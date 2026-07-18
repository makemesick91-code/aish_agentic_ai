<?php

declare(strict_types=1);

namespace App\Customers;

use App\Audit\AuditRecorder;
use App\Customers\Exceptions\InvalidIdentityValueException;
use App\Customers\Identity\IdentityCandidate;
use App\Customers\Identity\IdentityHasher;
use App\Customers\Identity\IdentityNormalizer;
use App\Customers\Identity\IdentityResolution;
use App\Customers\Identity\NormalizedIdentity;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantContext;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The single writer of customer identity. Given the identity values a source observed, it either
 * finds the existing canonical customer, creates one, or truthfully reports that the source was
 * anonymous.
 *
 * Three invariants drive the whole design (rule 36; ADR 0064, ADR 0071):
 *
 * 1. **Only verified identities link.** An unverified value becomes a suggestion, never an
 *    automatic link — otherwise typing someone else's email would attach you to their history.
 * 2. **Anonymous never creates.** A source with no usable identity yields a null customer; it must
 *    not manufacture an empty profile per response.
 * 3. **Creation is idempotent.** Concurrent callers race on the unique
 *    `(tenant_id, identity_type, value_hash)` index; the loser re-reads the winner's row instead of
 *    creating a duplicate.
 */
final class CustomerIdentityResolver
{
    public function __construct(
        private readonly IdentityNormalizer $normalizer,
        private readonly IdentityHasher $hasher,
        private readonly TenantContext $context,
        private readonly UsageMeter $usage,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  list<IdentityCandidate>  $candidates
     */
    public function resolve(
        CustomerIdentitySource $source,
        array $candidates,
        ?int $branchId = null,
        ?string $displayName = null,
        ?string $defaultRegionCallingCode = null,
    ): IdentityResolution {
        $tenant = $this->context->tenant();

        /** @var list<array{candidate: IdentityCandidate, normalized: NormalizedIdentity, hash: string}> $linkable */
        $linkable = [];
        $suggestedReasons = [];

        foreach ($candidates as $candidate) {
            // An unverified value is not proof of ownership — record why, and do not link.
            if (! $candidate->isVerified) {
                $suggestedReasons[] = $candidate->type->value.':unverified';

                continue;
            }

            try {
                $normalized = $this->normalizer->normalize(
                    $candidate->type,
                    $candidate->rawValue,
                    $defaultRegionCallingCode,
                );
            } catch (InvalidIdentityValueException) {
                // Never echo the offending value — an audit trail must not become a PII sink.
                $suggestedReasons[] = $candidate->type->value.':unnormalizable';

                continue;
            }

            $linkable[] = [
                'candidate' => $candidate,
                'normalized' => $normalized,
                'hash' => $this->hasher->hash($tenant->id, $normalized),
            ];
        }

        if ($linkable === []) {
            return IdentityResolution::anonymous($suggestedReasons);
        }

        $result = $this->linkAll($source, $linkable, $branchId, $displayName);

        // Metering and audit happen AFTER the transaction so a metering hiccup can never roll back
        // an identity write, and so the audit reflects committed state.
        if ($result->customerWasCreated && $result->customer !== null) {
            $this->usage->record(
                $tenant,
                MeterKeys::CUSTOMERS_CREATED,
                1,
                'customer-created:'.$result->customer->id,
                sourceReference: 'customer:'.$result->customer->id,
            );

            $this->audit->record('customer.created', [
                'subject' => $result->customer,
                'metadata' => [
                    'source_type' => $source->value,
                    'branch_id' => $branchId,
                ],
            ]);
        }

        if ($result->identitiesLinked > 0 && $result->customer !== null) {
            $this->usage->record(
                $tenant,
                MeterKeys::CUSTOMER_IDENTITIES_LINKED,
                $result->identitiesLinked,
                'customer-identities-linked:'.$result->customer->id.':'.$source->value.':'.$result->identitiesLinked,
                sourceReference: 'customer:'.$result->customer->id,
            );

            $this->audit->record('customer.identity.linked', [
                'subject' => $result->customer,
                'metadata' => [
                    // Counts and provenance only — never the identity value or its hash (rule 36).
                    'source_type' => $source->value,
                    'identities_linked' => $result->identitiesLinked,
                ],
            ]);
        }

        return new IdentityResolution(
            $result->customer,
            $result->customerWasCreated,
            $result->identitiesLinked,
            $suggestedReasons,
        );
    }

    /**
     * @param  list<array{candidate: IdentityCandidate, normalized: NormalizedIdentity, hash: string}>  $linkable
     */
    private function linkAll(
        CustomerIdentitySource $source,
        array $linkable,
        ?int $branchId,
        ?string $displayName,
    ): IdentityResolution {
        return DB::transaction(function () use ($source, $linkable, $branchId, $displayName): IdentityResolution {
            $customer = null;

            // Reuse an existing customer if ANY candidate already resolves to one. Two candidates
            // pointing at different customers is a real-world merge suggestion, not something to
            // auto-merge here (ADR 0072 requires human approval), so the first match wins and the
            // remaining identity is left for the merge queue.
            foreach ($linkable as $entry) {
                $existing = CustomerIdentity::query()
                    ->where('identity_type', $entry['normalized']->type)
                    ->where('value_hash', $entry['hash'])
                    ->first();

                if ($existing !== null) {
                    $customer = Customer::query()->find($existing->customer_id);

                    if ($customer !== null) {
                        break;
                    }
                }
            }

            $created = false;

            if ($customer === null) {
                $customer = Customer::create([
                    'primary_branch_id' => $branchId,
                    'display_name' => $displayName,
                    'status' => CustomerStatus::Active,
                    'last_seen_at' => now(),
                ]);
                $created = true;
            } else {
                // A merged customer must never receive new links — follow the survivor pointer so
                // late-arriving data lands on the customer that is actually in use (ADR 0072).
                $customer = $this->survivorOf($customer);

                // An erased customer must not be resurrected by a late-arriving identity: linking
                // to it would rebuild a profile that was deliberately purged (rule 36).
                if (! $customer->isLinkable()) {
                    return IdentityResolution::anonymous(['customer:not-linkable']);
                }

                $customer->forceFill(['last_seen_at' => now()])->save();
            }

            $linked = 0;

            foreach ($linkable as $entry) {
                if ($this->attachIdentity($customer, $source, $entry)) {
                    $linked++;
                }
            }

            return new IdentityResolution($customer, $created, $linked);
        });
    }

    /**
     * Follow the merge chain to the surviving customer. Bounded so a malformed chain cannot spin.
     */
    private function survivorOf(Customer $customer): Customer
    {
        $seen = 0;

        while ($customer->isMerged() && $customer->merged_into_customer_id !== null && $seen < 16) {
            $next = Customer::query()->find($customer->merged_into_customer_id);

            if ($next === null) {
                break;
            }

            $customer = $next;
            $seen++;
        }

        return $customer;
    }

    /**
     * @param  array{candidate: IdentityCandidate, normalized: NormalizedIdentity, hash: string}  $entry
     * @return bool True when a new identity row was created.
     */
    private function attachIdentity(Customer $customer, CustomerIdentitySource $source, array $entry): bool
    {
        $existing = CustomerIdentity::query()
            ->where('identity_type', $entry['normalized']->type)
            ->where('value_hash', $entry['hash'])
            ->first();

        if ($existing !== null) {
            // Already known: refresh recency only. The value, its hash, and its version are
            // immutable at the model layer, so this cannot silently rewrite history.
            $existing->forceFill(['last_seen_at' => now()])->save();

            return false;
        }

        try {
            $identity = new CustomerIdentity;

            $identity->fill([
                'customer_id' => $customer->id,
                'source_type' => $source,
                'identity_type' => $entry['normalized']->type,
                'value_normalized' => $entry['normalized']->persistableValue(),
                'value_hash' => $entry['hash'],
                'normalizer_version' => $entry['normalized']->normalizerVersion,
                'provenance' => $entry['candidate']->provenance,
                'confidence' => $entry['candidate']->confidence,
                'last_seen_at' => now(),
            ]);

            // Set explicitly, never by mass assignment: reaching this line already proves the
            // candidate was verified (unverified candidates are filtered out in resolve()).
            $identity->is_deterministic = true;
            $identity->is_verified = true;

            $identity->save();

            return true;
        } catch (UniqueConstraintViolationException) {
            // Lost a concurrent race — the winner's row is authoritative. Not an error.
            return false;
        }
    }
}
