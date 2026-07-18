<?php

declare(strict_types=1);

namespace App\Customers;

use App\Authorization\Permissions;
use App\Feedback\Support\FeedbackBranchScope;
use App\Models\Customer;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The Customer 360 interactions timeline.
 *
 * This is a DERIVED, read-only projection — not a stored timeline. Nothing is materialized, so it
 * cannot go stale, needs no reprojection job, and stays correct the instant a merge is reversed
 * (ADR 0070). It reads sources the customer domain does not own and writes to none of them, which
 * is what keeps the Step 8 feedback timeline authoritative (ADR 0065, rule 34).
 *
 * When the Experience Event Ledger lands it becomes an ADDITIONAL source behind this same
 * interface, with no change to the customer aggregate (ADR 0068).
 */
final class CustomerInteractionsReadModel
{
    /** Hard ceiling so a customer with a long history can never produce an unbounded query. */
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly CustomerConsentService $consents,
        private readonly TenantContext $context,
    ) {}

    /**
     * Interactions for a customer, newest first, filtered by what the viewer may see.
     *
     * @return LengthAwarePaginator<int, FeedbackItem>
     */
    public function interactions(Customer $customer, User $viewer, int $perPage = 20): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $query = FeedbackItem::query()
            // Include the whole merge chain: after a merge the survivor must show the history it
            // absorbed, and after a reversal it must stop showing it.
            ->whereIn('customer_id', $this->customerScopeIds($customer))
            ->with(['branch:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        // Customer 360 must not become a way around Step 8 branch scoping. A customer with a null
        // primary branch is deliberately tenant-wide visible, so without this the timeline would
        // hand a branch-restricted viewer that customer's feedback from branches they cannot reach
        // (rule 03, rule 33, rule 36).
        FeedbackBranchScope::apply($query, $this->context);

        // Free-text feedback content stays gated by the Step 8 permission. Customer 360 must not
        // become a way to read content a viewer could not read in the Feedback Inbox (rule 33/36).
        if (! $viewer->can(Permissions::FEEDBACK_VIEW_CONTENT)) {
            $query->select($this->metadataColumns());
        }

        return $query->paginate($perPage);
    }

    /**
     * A compact, permission-aware summary for the customer detail header.
     *
     * @return array<string, mixed>
     */
    public function summary(Customer $customer, User $viewer): array
    {
        $scopeIds = $this->customerScopeIds($customer);

        $base = FeedbackItem::query()->whereIn('customer_id', $scopeIds);

        // Counts and first/last timestamps disclose out-of-branch activity just as surely as the
        // rows themselves, so the same branch predicate applies here.
        FeedbackBranchScope::apply($base, $this->context);

        return [
            'feedback_count' => (clone $base)->count(),
            'first_interaction_at' => (clone $base)->min('created_at'),
            'last_interaction_at' => (clone $base)->max('created_at'),
            'identity_count' => $customer->identities()->count(),
            'merged_customer_count' => max(0, count($scopeIds) - 1),
            // Contact details are PII: expose the real value only to a holder of view-contact,
            // and a mask otherwise, so a template can never leak by forgetting to check.
            'contact_email' => $viewer->can(Permissions::CUSTOMER_VIEW_CONTACT)
                ? $customer->contact_email
                : $customer->maskedContactEmail(),
            'contact_phone' => $viewer->can(Permissions::CUSTOMER_VIEW_CONTACT)
                ? $customer->contact_phone
                : $customer->maskedContactPhone(),
            'contact_visible' => $viewer->can(Permissions::CUSTOMER_VIEW_CONTACT),
        ];
    }

    /**
     * The customer plus everything merged into it.
     *
     * @return list<int>
     */
    public function customerScopeIds(Customer $customer): array
    {
        return $this->consents->consentScopeIds($customer);
    }

    /**
     * Metadata-only projection used when the viewer lacks feedback content permission. Listing the
     * safe columns explicitly (rather than excluding the unsafe ones) means a future content column
     * is private by default instead of leaking until someone remembers to add it here.
     *
     * @return list<string>
     */
    private function metadataColumns(): array
    {
        return [
            'id',
            'ulid',
            'tenant_id',
            'branch_id',
            'customer_id',
            'source_type',
            'status',
            'current_assignee_id',
            'created_at',
            'last_activity_at',
        ];
    }
}
