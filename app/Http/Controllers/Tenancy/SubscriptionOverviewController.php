<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Authorization\Permissions;
use App\Http\Controllers\Controller;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Tenant-facing subscription overview: current plan, status, dates, effective entitlements,
 * and the usage summary for the one implemented meter. Every entitlement is resolved through
 * the authoritative resolver, and unsupported features are labelled truthfully — no paid or
 * billing state is shown (rule 27, rule 31 §9.9).
 */
final class SubscriptionOverviewController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, TenantContext $context, EntitlementResolver $resolver, UsageMeter $meter): View
    {
        abort_unless($request->user()->can(Permissions::TENANT_VIEW), 403);

        $tenant = $context->tenant();

        $subscription = TenantSubscription::query()->with('plan')->first();

        $entitlements = [];
        foreach (EntitlementKeys::all() as $key) {
            $entitlements[] = $resolver->resolve($tenant, $key)->toArray();
        }

        $usagePeriod = Carbon::now()->setTimezone('Asia/Makassar')->format('Y-m');
        $usage = [
            'meter' => MeterKeys::FOUNDATION_VERIFICATION,
            'period' => $usagePeriod,
            'total' => $meter->total($tenant, MeterKeys::FOUNDATION_VERIFICATION, $usagePeriod),
        ];

        return view('subscription.overview', compact('subscription', 'entitlements', 'usage'));
    }
}
