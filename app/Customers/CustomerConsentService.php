<?php

declare(strict_types=1);

namespace App\Customers;

use App\Audit\AuditRecorder;
use App\Enums\CustomerConsentType;
use App\Models\Customer;
use App\Models\CustomerConsent;
use Illuminate\Support\Facades\DB;

/**
 * Append-only consent capture and resolution.
 *
 * Consent is never edited: withdrawing appends a new row, so what the customer agreed to — and
 * exactly which consent text version they saw — stays provable (rule 36, rule 32; ADR 0064).
 *
 * Because a merge retains the non-surviving customer's consent history rather than rewriting it
 * (ADR 0072), resolving effective consent for a survivor must consider the whole merge chain.
 * Otherwise merging would silently discard a customer's recorded objection.
 */
final class CustomerConsentService
{
    /** How many merge generations to fold in — bounded so a malformed chain cannot spin. */
    private const MAX_MERGE_DEPTH = 16;

    public function __construct(private readonly AuditRecorder $audit) {}

    public function record(
        Customer $customer,
        CustomerConsentType $type,
        bool $accepted,
        string $consentTextVersion,
        string $source,
        ?string $channel = null,
        ?int $recordedBy = null,
    ): CustomerConsent {
        $consent = DB::transaction(fn (): CustomerConsent => CustomerConsent::create([
            'customer_id' => $customer->id,
            'consent_type' => $type,
            'accepted' => $accepted,
            'consent_text_version' => $consentTextVersion,
            'source' => $source,
            'channel' => $channel,
            'recorded_by' => $recordedBy,
            'created_at' => now(),
        ]));

        $this->audit->record('customer.consent.recorded', [
            'subject' => $customer,
            'actor_id' => $recordedBy,
            'metadata' => [
                // Decision metadata only — never the consent prose or any contact value (rule 36).
                'consent_type' => $type->value,
                'accepted' => $accepted,
                'consent_text_version' => $consentTextVersion,
                'source' => $source,
            ],
        ]);

        return $consent;
    }

    /**
     * The latest recorded decision for a consent type across the customer's merge chain, or null
     * when the customer never made one.
     *
     * Null is meaningfully different from false: "never asked" is not "declined", and treating them
     * the same would either fabricate consent or fabricate a refusal.
     */
    public function latest(Customer $customer, CustomerConsentType $type): ?CustomerConsent
    {
        return CustomerConsent::query()
            ->whereIn('customer_id', $this->consentScopeIds($customer))
            ->where('consent_type', $type)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Whether the customer may be contacted for a given purpose.
     *
     * Fails closed in both directions that matter: an explicit do-not-contact always wins, and an
     * absent decision is NOT treated as permission.
     */
    public function mayContact(Customer $customer, CustomerConsentType $purpose): bool
    {
        $suppression = $this->latest($customer, CustomerConsentType::DoNotContact);

        if ($suppression !== null && $suppression->accepted) {
            return false;
        }

        if ($purpose->isSuppression()) {
            return false;
        }

        $decision = $this->latest($customer, $purpose);

        return $decision !== null && $decision->accepted;
    }

    /**
     * The customer plus every customer merged into it, so a merge never loses consent history.
     *
     * @return list<int>
     */
    public function consentScopeIds(Customer $customer): array
    {
        $ids = [$customer->id];
        $frontier = [$customer->id];

        for ($depth = 0; $depth < self::MAX_MERGE_DEPTH && $frontier !== []; $depth++) {
            $frontier = Customer::query()
                ->whereIn('merged_into_customer_id', $frontier)
                ->whereNotIn('id', $ids)
                ->pluck('id')
                ->all();

            foreach ($frontier as $id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
