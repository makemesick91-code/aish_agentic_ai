<?php

declare(strict_types=1);

namespace App\Feedback\Support;

use App\Models\BranchAccessGrant;
use App\Models\FeedbackItem;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of the branch-visibility predicate for feedback list/summary queries. A member with
 * all-branches access sees every tenant item; a branch-restricted member sees only tenant-wide
 * (null-branch) items plus items in the branches they are explicitly granted. Tenant scoping itself
 * is already enforced by the global TenantScope (rule 33; Step 8 §16, §21).
 */
final class FeedbackBranchScope
{
    /**
     * @param  Builder<FeedbackItem>  $query
     */
    public static function apply(Builder $query, TenantContext $context): void
    {
        $membership = $context->membership();
        if ($membership->all_branches) {
            return;
        }

        $accessible = BranchAccessGrant::query()
            ->where('tenant_membership_id', $membership->id)
            ->pluck('branch_id')
            ->all();

        $query->where(function (Builder $scoped) use ($accessible): void {
            $scoped->whereNull('branch_id');
            if ($accessible !== []) {
                $scoped->orWhereIn('branch_id', $accessible);
            }
        });
    }

    public static function canReach(FeedbackItem $item, TenantContext $context): bool
    {
        if ($item->branch_id === null) {
            return true;
        }

        $membership = $context->membership();
        if ($membership->all_branches) {
            return true;
        }

        return BranchAccessGrant::query()
            ->where('tenant_membership_id', $membership->id)
            ->where('branch_id', $item->branch_id)
            ->exists();
    }
}
