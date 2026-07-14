<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Enums\FeatureType;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Platform\PlatformPermissions;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\Exceptions\InvalidEntitlementException;
use App\Subscriptions\PlanService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Platform plan catalog management. Read requires plans.view; mutations require plans.manage.
 * Plan code/version are immutable; entitlements are typed and allowlisted (rule 31 §9.2, §9.3).
 */
final class PlanCatalogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::PLANS_VIEW);

        $plans = Plan::withCount('features')->orderBy('code')->orderBy('version')->paginate(20);
        $canManage = $request->user()->can(PlatformPermissions::PLANS_MANAGE);

        return view('platform.plans.index', compact('plans', 'canManage'));
    }

    public function show(Request $request, Plan $plan): View
    {
        $this->authorize(PlatformPermissions::PLANS_VIEW);

        $plan->load('features');
        $entitlementKeys = EntitlementKeys::map();
        $canManage = $request->user()->can(PlatformPermissions::PLANS_MANAGE);

        return view('platform.plans.show', compact('plan', 'entitlementKeys', 'canManage'));
    }

    public function store(Request $request, PlanService $plans): RedirectResponse
    {
        $this->authorize(PlatformPermissions::PLANS_MANAGE);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_.-]+$/'],
            'version' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $plan = $plans->create($validated, (int) $request->user()->id);

        return redirect()->route('platform.plans.show', $plan)->with('status', __('Plan created (draft).'));
    }

    public function activate(Request $request, Plan $plan, PlanService $plans): RedirectResponse
    {
        $this->authorize(PlatformPermissions::PLANS_MANAGE);

        $plans->activate($plan, (int) $request->user()->id);

        return redirect()->route('platform.plans.show', $plan)->with('status', __('Plan activated.'));
    }

    public function retire(Request $request, Plan $plan, PlanService $plans): RedirectResponse
    {
        $this->authorize(PlatformPermissions::PLANS_MANAGE);

        $plans->retire($plan, (int) $request->user()->id);

        return redirect()->route('platform.plans.show', $plan)->with('status', __('Plan retired.'));
    }

    public function storeFeature(Request $request, Plan $plan, PlanService $plans): RedirectResponse
    {
        $this->authorize(PlatformPermissions::PLANS_MANAGE);

        $validated = $request->validate([
            'key' => ['required', Rule::in(EntitlementKeys::all())],
            'value' => ['required', 'string', 'max:255'],
        ]);

        $type = EntitlementKeys::typeFor($validated['key']);
        $value = match ($type) {
            FeatureType::Boolean => filter_var($validated['value'], FILTER_VALIDATE_BOOLEAN),
            FeatureType::Integer => (int) $validated['value'],
            default => $validated['value'],
        };

        try {
            $plans->setFeature($plan, $validated['key'], $value, (int) $request->user()->id);
        } catch (InvalidEntitlementException $e) {
            return redirect()->route('platform.plans.show', $plan)->withErrors(['value' => $e->getMessage()]);
        }

        return redirect()->route('platform.plans.show', $plan)->with('status', __('Entitlement saved.'));
    }
}
