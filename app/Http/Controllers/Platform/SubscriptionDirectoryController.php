<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Platform\PlatformPermissions;
use App\Subscriptions\Exceptions\InvalidSubscriptionTransitionException;
use App\Subscriptions\Exceptions\RetiredPlanException;
use App\Subscriptions\SubscriptionService;
use App\Tenancy\TenantScope;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Platform subscription directory. Tenant-owned subscriptions are resolved WITHOUT the tenant
 * scope (the platform plane carries no tenant context) and always filtered explicitly by the
 * specific tenant/subscription being acted on. Read requires subscriptions.view; assign/
 * transition require subscriptions.manage (rule 31 §10.1, §9.4).
 */
final class SubscriptionDirectoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::SUBSCRIPTIONS_VIEW);

        $subscriptions = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->with(['plan', 'tenant'])
            ->latest()
            ->paginate(20);

        $canManage = $request->user()->can(PlatformPermissions::SUBSCRIPTIONS_MANAGE);
        $plans = $canManage ? Plan::where('status', 'active')->orderBy('name')->get() : collect();
        $tenants = $canManage ? Tenant::orderBy('name')->get() : collect();
        $statuses = SubscriptionStatus::cases();

        return view('platform.subscriptions.index', compact('subscriptions', 'canManage', 'plans', 'tenants', 'statuses'));
    }

    public function assign(Request $request, SubscriptionService $subscriptions): RedirectResponse
    {
        $this->authorize(PlatformPermissions::SUBSCRIPTIONS_MANAGE);

        $validated = $request->validate([
            'tenant' => ['required', Rule::exists('tenants', 'ulid')],
            'plan' => ['required', Rule::exists('plans', 'ulid')],
        ]);

        $tenant = Tenant::where('ulid', $validated['tenant'])->firstOrFail();
        $plan = Plan::where('ulid', $validated['plan'])->firstOrFail();
        $actorId = (int) $request->user()->id;

        $existing = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->first();

        try {
            if ($existing !== null) {
                $subscriptions->changePlan($existing, $plan, $actorId);
            } else {
                $subscriptions->start($tenant, $plan, actorId: $actorId);
            }
        } catch (RetiredPlanException $e) {
            return redirect()->route('platform.subscriptions.index')->withErrors(['plan' => $e->getMessage()]);
        }

        return redirect()->route('platform.subscriptions.index')->with('status', __('Subscription assigned.'));
    }

    public function transition(Request $request, string $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        $this->authorize(PlatformPermissions::SUBSCRIPTIONS_MANAGE);

        $model = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->where('ulid', $subscription)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(fn (SubscriptionStatus $s): string => $s->value, SubscriptionStatus::cases()))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $subscriptions->transition(
                $model,
                SubscriptionStatus::from($validated['status']),
                $validated['reason'] ?? null,
                (int) $request->user()->id,
            );
        } catch (InvalidSubscriptionTransitionException $e) {
            return redirect()->route('platform.subscriptions.index')->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('platform.subscriptions.index')->with('status', __('Subscription updated.'));
    }
}
