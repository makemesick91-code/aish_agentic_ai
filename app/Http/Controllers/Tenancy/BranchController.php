<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Audit\AuditRecorder;
use App\Enums\BranchStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Branch management for the current tenant. Every action authorizes before mutating and
 * branches are tenant-stamped automatically by the BelongsToTenant concern (rule 03, 20).
 */
final class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Branch::class);

        $branches = Branch::orderBy('name')->paginate(20);

        return view('branches.index', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32'],
            'timezone' => ['nullable', 'string', 'max:64', 'timezone'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        $branch = Branch::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => BranchStatus::Active->value,
            'timezone' => $validated['timezone'] ?? null,
            'locale' => $validated['locale'] ?? null,
        ]);

        app(AuditRecorder::class)->record('branch.created', [
            'tenant_id' => app(TenantContext::class)->tenantId(),
            'actor_id' => $request->user()->id,
            'subject' => $branch,
        ]);

        return redirect()->route('branches.index')->with('status', __('Branch created.'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64', 'timezone'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        // The tenant and the immutable code are intentionally NOT updatable here.
        $branch->update([
            'name' => $validated['name'],
            'timezone' => $validated['timezone'] ?? $branch->timezone,
            'locale' => $validated['locale'] ?? $branch->locale,
        ]);

        app(AuditRecorder::class)->record('branch.updated', [
            'tenant_id' => app(TenantContext::class)->tenantId(),
            'actor_id' => $request->user()->id,
            'subject' => $branch,
        ]);

        return redirect()->route('branches.index')->with('status', __('Branch updated.'));
    }

    public function deactivate(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('deactivate', $branch);

        $branch->update(['status' => BranchStatus::Inactive->value]);

        app(AuditRecorder::class)->record('branch.deactivated', [
            'tenant_id' => app(TenantContext::class)->tenantId(),
            'actor_id' => $request->user()->id,
            'subject' => $branch,
        ]);

        return redirect()->route('branches.index')->with('status', __('Branch deactivated.'));
    }
}
