<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Feedback\Exceptions\InvalidStatusTransitionException;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;

/**
 * The single place feedback status transitions happen. Every transition is validated against the
 * explicit state machine; an invalid transition fails closed. Reopens require a reason. Resolved and
 * closed are OPERATIONAL states — they never imply customer recovery. Each transition records an
 * immutable timeline event and a sanitized audit entry (rule 33; Step 8 §10).
 */
final class FeedbackLifecycle
{
    public function __construct(
        private readonly FeedbackTimeline $timeline,
        private readonly AuditRecorder $audit,
        private readonly NotificationDispatcher $dispatcher,
    ) {}

    public function transition(FeedbackItem $item, FeedbackStatus $to, User $actor, ?string $reason = null, bool $notify = true): FeedbackItem
    {
        $from = $item->status;

        if (! $from->canTransitionTo($to)) {
            throw InvalidStatusTransitionException::notAllowed($from, $to);
        }

        $isReopen = $from->isReopenInto($to);
        if ($isReopen && ($reason === null || trim($reason) === '')) {
            throw InvalidStatusTransitionException::reasonRequired($from, $to);
        }

        DB::transaction(function () use ($item, $from, $to, $actor, $reason, $isReopen): void {
            $item->status = $to;
            $item->last_activity_at = now();
            $this->stampTransitionTime($item, $to, $isReopen);
            $item->save();

            $this->timeline->record(
                $item,
                $isReopen ? FeedbackEventType::Reopened : FeedbackEventType::StatusChanged,
                [
                    'from' => $from->value,
                    'to' => $to->value,
                    'has_reason' => $reason !== null && trim($reason) !== '',
                ],
                actorId: $actor->id,
            );
        });

        $this->audit->record($isReopen ? 'feedback.reopened' : 'feedback.status.changed', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $actor->id,
            'subject' => $item,
            'metadata' => [
                'from' => $from->value,
                'to' => $to->value,
                'has_reason' => $reason !== null && trim($reason) !== '',
            ],
        ]);

        if ($notify) {
            $this->notifyAssignee($item, $from, $to, $actor);
        }

        return $item;
    }

    /**
     * Notify the current assignee (if any, and not the actor) of a status change. In-app only;
     * failure never breaks the transition.
     */
    private function notifyAssignee(FeedbackItem $item, FeedbackStatus $from, FeedbackStatus $to, User $actor): void
    {
        $assigneeId = $item->current_assignee_id;
        if ($assigneeId === null || $assigneeId === $actor->id) {
            return;
        }

        $assignee = User::find($assigneeId);
        if ($assignee === null) {
            return;
        }

        try {
            $this->dispatcher->dispatch(
                NotificationType::FeedbackStatusChanged,
                $assignee,
                'feedback-status:'.$item->id.':'.$from->value.':'.$to->value.':'.now()->timestamp,
                'A feedback item you are assigned to changed status',
                tenant: $item->tenant,
                body: 'Feedback item '.$item->ulid.' moved to '.$to->label().'.',
                data: ['feedback_ulid' => $item->ulid, 'status' => $to->value],
                branchId: $item->branch_id,
            );
        } catch (\Throwable) {
            // A notification failure must never fail the transition.
        }
    }

    private function stampTransitionTime(FeedbackItem $item, FeedbackStatus $to, bool $isReopen): void
    {
        if ($isReopen) {
            $item->reopened_at = now();
        }

        match ($to) {
            FeedbackStatus::Triaged => $item->triaged_at ??= now(),
            FeedbackStatus::Resolved => $item->resolved_at = now(),
            FeedbackStatus::Closed => $item->closed_at = now(),
            FeedbackStatus::Archived => $item->archived_at = now(),
            default => null,
        };
    }
}
