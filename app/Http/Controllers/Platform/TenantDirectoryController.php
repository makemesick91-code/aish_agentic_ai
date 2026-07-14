<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Enums\NotificationState;
use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Models\PlatformSupportNote;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Platform\PlatformPermissions;
use App\Platform\TenantAdministrationService;
use App\Tenancy\TenantScope;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Platform tenant directory. Exposes platform metadata only (name, code, status, subscription,
 * notification health) — never tenant business/customer/medical data (rule 31 §10.7). Status
 * mutations require a reason and are audited via TenantAdministrationService.
 */
final class TenantDirectoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::TENANTS_VIEW);

        $tenants = Tenant::orderBy('name')->paginate(20);
        $ids = $tenants->getCollection()->pluck('id')->all();

        $subscriptions = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->with('plan')
            ->whereIn('tenant_id', $ids)
            ->get()
            ->keyBy('tenant_id');

        return view('platform.tenants.index', compact('tenants', 'subscriptions'));
    }

    public function show(Request $request, Tenant $tenant): View
    {
        $this->authorize(PlatformPermissions::TENANTS_VIEW);

        $subscription = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->with('plan')
            ->where('tenant_id', $tenant->id)
            ->first();

        $notificationCounts = [
            'queued' => NotificationDelivery::query()->forTenant($tenant->id)->where('state', NotificationState::Queued->value)->count(),
            'sent' => NotificationDelivery::query()->forTenant($tenant->id)->where('state', NotificationState::Sent->value)->count(),
            'failed' => NotificationDelivery::query()->forTenant($tenant->id)->where('state', NotificationState::Failed->value)->count(),
        ];

        $supportNotes = Gate::allows(PlatformPermissions::SUPPORT_NOTES_VIEW)
            ? PlatformSupportNote::where('tenant_id', $tenant->id)->with('author')->latest()->limit(20)->get()
            : collect();

        $canManageStatus = Gate::allows(PlatformPermissions::TENANTS_MANAGE_STATUS);
        $canManageNotes = Gate::allows(PlatformPermissions::SUPPORT_NOTES_MANAGE);

        return view('platform.tenants.show', compact('tenant', 'subscription', 'notificationCounts', 'supportNotes', 'canManageStatus', 'canManageNotes'));
    }

    public function suspend(Request $request, Tenant $tenant, TenantAdministrationService $admin): RedirectResponse
    {
        $this->authorize(PlatformPermissions::TENANTS_MANAGE_STATUS);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $admin->suspend($tenant, $validated['reason'], $request->user());

        return redirect()->route('platform.tenants.show', $tenant)->with('status', __('Tenant suspended.'));
    }

    public function reactivate(Request $request, Tenant $tenant, TenantAdministrationService $admin): RedirectResponse
    {
        $this->authorize(PlatformPermissions::TENANTS_MANAGE_STATUS);

        $admin->reactivate($tenant, $request->user());

        return redirect()->route('platform.tenants.show', $tenant)->with('status', __('Tenant reactivated.'));
    }

    public function markDeletionPending(Request $request, Tenant $tenant, TenantAdministrationService $admin): RedirectResponse
    {
        $this->authorize(PlatformPermissions::TENANTS_MANAGE_STATUS);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $admin->markDeletionPending($tenant, $validated['reason'], $request->user());

        return redirect()->route('platform.tenants.show', $tenant)->with('status', __('Tenant marked for deletion.'));
    }
}
