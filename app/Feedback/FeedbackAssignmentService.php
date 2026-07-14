<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Authorization\Permissions;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Feedback\Exceptions\InvalidAssigneeException;
use App\Models\BranchAccessGrant;
use App\Models\FeedbackAssignment;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The single place a feedback item's current assignee changes. A proposed assignee is validated as an
 * active, in-branch tenant member with feedback access before assignment — cross-tenant and
 * cross-branch assignment fail closed. Assignment history is append-only; a change records an
 * immutable timeline event, a sanitized audit entry, and a deduplicated in-app notification to the
 * new assignee (rule 33; Step 8 §11, §23).
 */
final class FeedbackAssignmentService
{
    public function __construct(
        private readonly FeedbackTimeline $timeline,
        private readonly AuditRecorder $audit,
        private readonly NotificationDispatcher $dispatcher,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function assign(FeedbackItem $item, ?User $assignee, User $actor, ?string $reason = null, bool $notify = true): FeedbackItem
    {
        if ($assignee !== null) {
            $this->assertAssignable($item, $assignee);
        }

        $previousId = $item->current_assignee_id;
        $newId = $assignee?->id;

        $assignment = DB::transaction(function () use ($item, $assignee, $newId, $previousId, $actor, $reason): FeedbackAssignment {
            $assignment = FeedbackAssignment::create([
                'tenant_id' => $item->tenant_id,
                'feedback_item_id' => $item->id,
                'previous_assignee_id' => $previousId,
                'new_assignee_id' => $newId,
                'actor_id' => $actor->id,
                'reason' => $reason,
            ]);

            $item->current_assignee_id = $newId;
            if ($assignee !== null) {
                $item->assigned_at = now();
                if (in_array($item->status, [FeedbackStatus::New, FeedbackStatus::Triaged], true)) {
                    $item->status = FeedbackStatus::Assigned;
                }
            }
            $item->last_activity_at = now();
            $item->save();

            $this->timeline->record(
                $item,
                $assignee !== null ? FeedbackEventType::Assigned : FeedbackEventType::Unassigned,
                [
                    'previous_assignee_id' => $previousId,
                    'new_assignee_id' => $newId,
                    'has_reason' => $reason !== null && trim($reason) !== '',
                ],
                actorId: $actor->id,
            );

            return $assignment;
        });

        $this->audit->record($assignee !== null ? 'feedback.assigned' : 'feedback.unassigned', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $actor->id,
            'subject' => $item,
            'metadata' => [
                'previous_assignee_id' => $previousId,
                'new_assignee_id' => $newId,
            ],
        ]);

        if ($assignee !== null && $notify) {
            $this->notifyAssignee($item, $assignee, $assignment);
        }

        return $item;
    }

    private function notifyAssignee(FeedbackItem $item, User $assignee, FeedbackAssignment $assignment): void
    {
        try {
            $this->dispatcher->dispatch(
                NotificationType::FeedbackAssigned,
                $assignee,
                'feedback-assignment:'.$assignment->ulid,
                'A feedback item was assigned to you',
                tenant: $item->tenant,
                body: 'Feedback item '.$item->ulid.' was assigned to you.',
                data: ['feedback_ulid' => $item->ulid],
                branchId: $item->branch_id,
            );
        } catch (\Throwable) {
            // A notification failure must never fail the assignment.
        }
    }

    private function assertAssignable(FeedbackItem $item, User $assignee): void
    {
        if (! $assignee->isActive()) {
            throw InvalidAssigneeException::suspendedUser();
        }

        $membership = $assignee->activeMembershipFor($item->tenant_id);
        if ($membership === null) {
            throw InvalidAssigneeException::notActiveMember();
        }

        if ($item->branch_id !== null && ! $membership->all_branches) {
            $hasGrant = BranchAccessGrant::query()
                ->where('tenant_membership_id', $membership->id)
                ->where('branch_id', $item->branch_id)
                ->exists();
            if (! $hasGrant) {
                throw InvalidAssigneeException::branchOutOfScope();
            }
        }

        $this->registrar->setPermissionsTeamId($item->tenant_id);
        if (! $assignee->can(Permissions::FEEDBACK_VIEW)) {
            throw InvalidAssigneeException::missingPermission();
        }
    }
}
