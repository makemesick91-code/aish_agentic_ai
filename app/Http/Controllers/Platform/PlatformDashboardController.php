<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Enums\NotificationState;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NotificationDelivery;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Platform\PlatformPermissions;
use App\Tenancy\TenantScope;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Platform operations dashboard. Shows only truthful foundation metadata — tenant/subscription/
 * notification counts and recent platform audit — never fabricated revenue, MRR, or business
 * KPIs (rule 31 §10.6).
 */
final class PlatformDashboardController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): View
    {
        $this->authorize(PlatformPermissions::DASHBOARD_VIEW);

        $tenantCounts = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', TenantStatus::Active->value)->count(),
            'suspended' => Tenant::where('status', TenantStatus::Suspended->value)->count(),
            'deletion_pending' => Tenant::where('status', TenantStatus::DeletionPending->value)->count(),
        ];

        $subscriptionCounts = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $notificationCounts = [
            'queued' => NotificationDelivery::where('state', NotificationState::Queued->value)->count(),
            'sent' => NotificationDelivery::where('state', NotificationState::Sent->value)->count(),
            'failed' => NotificationDelivery::where('state', NotificationState::Failed->value)->count(),
        ];

        $recentAudit = AuditLog::query()
            ->where(fn ($query) => $query->where('event', 'like', 'platform.%')->orWhere('event', 'like', 'plan.%'))
            ->latest()
            ->limit(10)
            ->get();

        return view('platform.dashboard', compact('tenantCounts', 'subscriptionCounts', 'notificationCounts', 'recentAudit'));
    }
}
