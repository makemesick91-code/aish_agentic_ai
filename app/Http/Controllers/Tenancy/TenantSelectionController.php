<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Audit\AuditRecorder;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Authenticated but pre-tenant-context: the user chooses which workspace to act in. The
 * selection is stored in the session and consumed by the tenant-context middleware. Only
 * workspaces the user actually has an active membership in are ever selectable (rule 03).
 */
final class TenantSelectionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $memberships = $request->user()->accessibleMemberships();

        if ($memberships->isEmpty()) {
            return view('tenant.none');
        }

        if ($memberships->count() === 1) {
            $tenantId = (int) $memberships->first()->tenant_id;

            session(['current_tenant_id' => $tenantId]);

            app(AuditRecorder::class)->record('context.tenant.selected', [
                'tenant_id' => $tenantId,
                'actor_id' => $request->user()->id,
            ]);

            return redirect()->route('dashboard');
        }

        return view('tenant.select', compact('memberships'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant' => ['required', 'string'],
        ]);

        // Never trust an arbitrary id: resolve the ULID against the user's own
        // memberships only, so another tenant's ULID can never be selected.
        $accessibleTenantIds = $request->user()->accessibleMemberships()
            ->pluck('tenant_id')
            ->all();

        $tenant = Tenant::where('ulid', $validated['tenant'])
            ->whereIn('id', $accessibleTenantIds)
            ->first();

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant' => __('You do not have access to the selected workspace.'),
            ]);
        }

        $tenantId = (int) $tenant->id;

        session(['current_tenant_id' => $tenantId]);
        session()->forget('current_branch_id');

        app(AuditRecorder::class)->record('context.tenant.switched', [
            'tenant_id' => $tenantId,
            'actor_id' => $request->user()->id,
        ]);

        return redirect()->route('dashboard');
    }
}
