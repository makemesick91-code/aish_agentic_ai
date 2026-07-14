<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Audit\AuditRecorder;
use App\Enums\BranchStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Branch selection within an already-established tenant context. Only active branches the
 * acting membership can reach are selectable; the choice is session-scoped (rule 03).
 */
final class BranchSelectionController extends Controller
{
    public function index(): View
    {
        $membership = app(TenantContext::class)->membership();

        if ($membership->all_branches) {
            // Already tenant-scoped by the Branch global scope.
            $branches = Branch::where('status', BranchStatus::Active->value)
                ->orderBy('name')
                ->get();
        } else {
            $grantedBranchIds = BranchAccessGrant::where('tenant_membership_id', $membership->id)
                ->pluck('branch_id');

            $branches = Branch::whereIn('id', $grantedBranchIds)
                ->where('status', BranchStatus::Active->value)
                ->orderBy('name')
                ->get();
        }

        return view('branch.select', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch' => ['required', 'string'],
        ]);

        $context = app(TenantContext::class);
        $membership = $context->membership();

        // Tenant-scoped lookup: a branch from another tenant simply does not resolve.
        $branch = Branch::where('ulid', $validated['branch'])->firstOrFail();

        if (! $branch->isActive()) {
            abort(403);
        }

        $hasAccess = $membership->all_branches
            || BranchAccessGrant::where('tenant_membership_id', $membership->id)
                ->where('branch_id', $branch->id)
                ->exists();

        if (! $hasAccess) {
            abort(403);
        }

        session(['current_branch_id' => $branch->id]);

        app(AuditRecorder::class)->record('context.branch.switched', [
            'tenant_id' => $context->tenantId(),
            'actor_id' => $request->user()->id,
            'subject' => $branch,
        ]);

        return redirect()->route('dashboard');
    }
}
