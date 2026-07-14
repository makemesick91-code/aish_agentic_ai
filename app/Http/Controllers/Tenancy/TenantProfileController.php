<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Audit\AuditRecorder;
use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * View and update the current tenant's profile and settings. Authorization is enforced
 * before any mutation (rule 20).
 */
final class TenantProfileController extends Controller
{
    use AuthorizesRequests;

    public function edit(): View
    {
        $tenant = app(TenantContext::class)->tenant();

        $this->authorize('view', $tenant);

        $tenant->loadMissing('settings');

        return view('tenant.settings', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = app(TenantContext::class)->tenant();

        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'timezone' => $validated['timezone'],
            'locale' => $validated['locale'],
        ]);

        $settings = TenantSetting::firstOrNew(['tenant_id' => $tenant->id]);
        $settings->timezone = $validated['timezone'];
        $settings->locale = $validated['locale'];
        $settings->save();

        app(AuditRecorder::class)->record('tenant.updated', [
            'tenant_id' => (int) $tenant->id,
            'actor_id' => $request->user()->id,
            'subject' => $tenant,
        ]);

        return redirect()->route('tenant.edit')->with('status', __('Workspace settings updated.'));
    }
}
