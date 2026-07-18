<?php

declare(strict_types=1);

namespace App\Customers\Support;

use App\Models\BranchAccessGrant;
use App\Models\Customer;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of the branch-visibility predicate for customer queries. A member with all-branches
 * access sees every tenant customer; a branch-restricted member sees only tenant-wide (null-branch)
 * customers plus those whose primary branch they are explicitly granted. Tenant scoping itself is
 * already enforced by the global TenantScope (rule 36; contract §10).
 *
 * Branch on a customer is PROVENANCE, not ownership (ADR 0064) — which is why a null primary branch
 * is visible tenant-wide rather than hidden.
 */
final class CustomerBranchScope
{
    /**
     * @param  Builder<Customer>  $query
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
            $scoped->whereNull('primary_branch_id');

            if ($accessible !== []) {
                $scoped->orWhereIn('primary_branch_id', $accessible);
            }
        });
    }

    public static function canReach(Customer $customer, TenantContext $context): bool
    {
        if ($customer->primary_branch_id === null) {
            return true;
        }

        $membership = $context->membership();

        if ($membership->all_branches) {
            return true;
        }

        return BranchAccessGrant::query()
            ->where('tenant_membership_id', $membership->id)
            ->where('branch_id', $customer->primary_branch_id)
            ->exists();
    }
}
