<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optionally establishes the branch context after the tenant context. The branch must
 * belong to the current tenant (enforced by the tenant scope), be active, and be
 * accessible to the member (tenant-wide or explicitly granted). An invalid selection is
 * silently dropped, never coerced (rule 03, rule 30). Runs after ResolveTenantContext.
 */
final class ResolveBranchContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $branchId = $request->session()->get('current_branch_id');

        if ($branchId !== null && $context->hasTenant()) {
            // Branch is tenant-scoped now: a cross-tenant id simply resolves to null.
            $branch = Branch::find($branchId);

            if ($branch !== null && $branch->isActive() && $this->memberCanAccess($branch)) {
                $context->setBranch($branch);
            } else {
                $request->session()->forget('current_branch_id');
            }
        }

        return $next($request);
    }

    private function memberCanAccess(Branch $branch): bool
    {
        $membership = app(TenantContext::class)->membership();

        if ($membership->all_branches) {
            return true;
        }

        return BranchAccessGrant::query()
            ->where('tenant_membership_id', $membership->id)
            ->where('branch_id', $branch->id)
            ->exists();
    }
}
