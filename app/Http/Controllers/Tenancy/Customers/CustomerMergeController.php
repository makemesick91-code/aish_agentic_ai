<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Customers;

use App\Customers\CustomerEntitlements;
use App\Customers\CustomerMergeService;
use App\Customers\Exceptions\CustomerEntitlementDeniedException;
use App\Customers\Exceptions\CustomerMergeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\MergeCustomerRequest;
use App\Http\Requests\Customers\SplitCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerMergeEvent;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Human-approved merge and split. There is deliberately NO bulk endpoint: bulk identity mutation
 * has no reviewable approval story, and a wrong bulk merge would be the single most damaging
 * operation in the product (rule 36; ADR 0072).
 */
final class CustomerMergeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CustomerMergeService $merges,
        private readonly CustomerEntitlements $entitlements,
        private readonly TenantContext $context,
    ) {}

    public function store(MergeCustomerRequest $request, Customer $customer): RedirectResponse
    {
        // Resolve through the tenant-scoped query, so a ULID from another tenant simply is not
        // found rather than revealing that it exists (rule 36).
        $merged = Customer::query()
            ->where('ulid', $request->validated('merged_customer'))
            ->first();

        if ($merged === null) {
            return back()->withErrors(['merged_customer' => 'That customer could not be found.']);
        }

        // Both customers must be reachable — checked against the resolved pair, not just the route
        // model, so a branch-restricted operator cannot absorb a customer they cannot see.
        $this->authorize('merge', [$customer, $merged]);

        try {
            $this->entitlements->assertMergeEnabled($this->context->tenant());

            $event = $this->merges->merge(
                $customer,
                $merged,
                $request->validated('reason'),
                $request->user()?->id,
            );
        } catch (CustomerEntitlementDeniedException $e) {
            abort(403, $e->getMessage());
        } catch (CustomerMergeException $e) {
            return back()->withErrors(['merged_customer' => $e->getMessage()]);
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', 'Customer merged. This can be reversed from the merge history.');
    }

    public function destroy(SplitCustomerRequest $request, CustomerMergeEvent $mergeEvent): RedirectResponse
    {
        $survivor = Customer::query()->findOrFail($mergeEvent->survivor_customer_id);

        $this->authorize('split', $survivor);

        try {
            $this->entitlements->assertMergeEnabled($this->context->tenant());

            $this->merges->split(
                $mergeEvent,
                $request->validated('reason'),
                $request->user()?->id,
            );
        } catch (CustomerEntitlementDeniedException $e) {
            abort(403, $e->getMessage());
        } catch (CustomerMergeException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()
            ->route('customers.show', $survivor)
            ->with('status', 'Merge reversed. Both customers have been restored.');
    }
}
