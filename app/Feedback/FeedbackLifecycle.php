<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Feedback\Exceptions\InvalidStatusTransitionException;
use App\Models\FeedbackItem;
use App\Models\User;
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
    ) {}

    public function transition(FeedbackItem $item, FeedbackStatus $to, User $actor, ?string $reason = null): FeedbackItem
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

        return $item;
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
