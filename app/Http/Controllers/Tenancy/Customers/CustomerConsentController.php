<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Customers;

use App\Customers\CustomerConsentService;
use App\Enums\CustomerConsentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\RecordConsentRequest;
use App\Models\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Records an operator-captured consent decision. Appends only — a change of mind is a new row, so
 * the history of what the customer agreed to is never overwritten (rule 36; ADR 0064).
 */
final class CustomerConsentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CustomerConsentService $consents) {}

    public function store(RecordConsentRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('recordConsent', $customer);

        $this->consents->record(
            $customer,
            CustomerConsentType::from($request->validated('consent_type')),
            $request->boolean('accepted'),
            $request->validated('consent_text_version'),
            source: 'operator',
            channel: $request->validated('channel'),
            recordedBy: $request->user()?->id,
        );

        return redirect()
            ->route('customers.show', $customer)
            ->with('status', 'Consent decision recorded.');
    }
}
