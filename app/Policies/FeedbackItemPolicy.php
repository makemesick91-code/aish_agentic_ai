<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

/**
 * Server-side authorization for feedback items. Every ability requires the specific permission AND
 * that the item belongs to the current tenant AND that the actor may reach the item's branch. UI
 * hiding is never sufficient; a platform role grants no tenant feedback access (rule 33; Step 8 §21).
 */
class FeedbackItemPolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::FEEDBACK_VIEW);
    }

    public function view(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_VIEW) && $this->reachable($item);
    }

    public function viewContent(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_VIEW_CONTENT) && $this->reachable($item);
    }

    public function manageStatus(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_MANAGE_STATUS) && $this->reachable($item);
    }

    public function assign(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_ASSIGN) && $this->reachable($item);
    }

    public function tag(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_TAGS_MANAGE) && $this->reachable($item);
    }

    public function addNote(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_NOTES_CREATE) && $this->reachable($item);
    }

    public function manageAttachments(User $user, FeedbackItem $item): bool
    {
        return $user->can(Permissions::FEEDBACK_ATTACHMENTS_MANAGE) && $this->reachable($item);
    }

    public function bulkManage(User $user): bool
    {
        return $user->can(Permissions::FEEDBACK_BULK_MANAGE);
    }

    /** Tenant-level tag catalog management (create/archive), distinct from attaching to an item. */
    public function manageTags(User $user): bool
    {
        return $user->can(Permissions::FEEDBACK_TAGS_MANAGE);
    }

    public function export(User $user): bool
    {
        return $user->can(Permissions::FEEDBACK_EXPORT);
    }

    public function viewSummary(User $user): bool
    {
        return $user->can(Permissions::FEEDBACK_SUMMARY_VIEW);
    }

    private function reachable(FeedbackItem $item): bool
    {
        return $this->inCurrentTenant($item) && $this->canAccessBranch($item->branch_id);
    }
}
