<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\BranchAccessGrant;
use App\Tenancy\TenantContext;

/**
 * Branch-level authorization. A tenant-wide resource (branch_id null) is visible to any member
 * with the relevant permission; a branch-scoped resource is accessible only when the acting
 * membership has all-branches access or an explicit grant for that branch. A branch-restricted
 * user therefore never reaches another branch's data (rule 03, rule 30, rule 32; Step 7 §27).
 */
trait ScopesToBranch
{
    protected function canAccessBranch(?int $branchId): bool
    {
        // Tenant-wide resources carry no branch restriction.
        if ($branchId === null) {
            return true;
        }

        $context = app(TenantContext::class);
        if (! $context->hasTenant()) {
            return false;
        }

        try {
            $membership = $context->membership();
        } catch (\Throwable) {
            return false;
        }

        if ($membership->all_branches) {
            return true;
        }

        return BranchAccessGrant::where('tenant_membership_id', $membership->id)
            ->where('branch_id', $branchId)
            ->exists();
    }
}
