<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Customers;

use App\Authorization\Permissions;
use App\Customers\CustomerConsentService;
use App\Customers\CustomerInteractionsReadModel;
use App\Customers\Support\CustomerBranchScope;
use App\Enums\CustomerConsentType;
use App\Enums\CustomerStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Tenant Customer 360 directory + detail workspace. Every action authorizes server-side via
 * CustomerPolicy (never UI hiding); contact PII is gated separately from customer visibility
 * (rule 36; contract §11).
 */
final class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CustomerInteractionsReadModel $readModel,
        private readonly CustomerConsentService $consents,
        private readonly TenantContext $context,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        /** @var User $user */
        $user = $request->user();

        $status = $this->resolveStatusFilter($request->query('status'));
        $search = trim((string) $request->query('q', ''));

        $query = Customer::query()->with('primaryBranch:id,name');

        CustomerBranchScope::apply($query, $this->context);

        if ($status !== null) {
            $query->where('status', $status);
        } else {
            // Merged rows are retained for reversibility but are not the working set — showing them
            // by default would present one person twice (ADR 0072).
            $query->where('status', '!=', CustomerStatus::Merged);
        }

        if ($search !== '') {
            $query->where(function ($scoped) use ($search, $user): void {
                $scoped->where('display_name', 'like', '%'.$search.'%');

                // Contact search is available only to holders of view-contact; without it the
                // columns are excluded from the query entirely, so they can never be a match
                // source and cannot be probed by trial and error (rule 36).
                if ($user->can(Permissions::CUSTOMER_VIEW_CONTACT)) {
                    $scoped->orWhere('contact_email', 'like', '%'.$search.'%')
                        ->orWhere('contact_phone', 'like', '%'.$search.'%');
                }
            });
        }

        $customers = $query
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'status' => $status,
            'search' => $search,
            'statuses' => CustomerStatus::cases(),
            'canViewContact' => $user->can(Permissions::CUSTOMER_VIEW_CONTACT),
        ]);
    }

    public function show(Request $request, Customer $customer): View
    {
        $this->authorize('view', $customer);

        /** @var User $user */
        $user = $request->user();

        $consentStates = [];

        foreach (CustomerConsentType::cases() as $type) {
            $latest = $this->consents->latest($customer, $type);

            $consentStates[$type->value] = [
                'type' => $type,
                // null is rendered as "not recorded" — never as a decline (rule 36).
                'decision' => $latest?->accepted,
                'recorded_at' => $latest?->created_at,
                'text_version' => $latest?->consent_text_version,
            ];
        }

        return view('customers.show', [
            'customer' => $customer,
            'summary' => $this->readModel->summary($customer, $user),
            'interactions' => $this->readModel->interactions($customer, $user),
            'consentStates' => $consentStates,
            'identities' => $customer->identities()->orderByDesc('last_seen_at')->get(),
            'canMerge' => $user->can(Permissions::CUSTOMER_MERGE),
            'canManage' => $user->can(Permissions::CUSTOMER_MANAGE),
        ]);
    }

    private function resolveStatusFilter(mixed $value): ?CustomerStatus
    {
        return is_string($value) ? CustomerStatus::tryFrom($value) : null;
    }
}
