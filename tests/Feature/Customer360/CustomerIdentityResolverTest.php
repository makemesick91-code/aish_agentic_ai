<?php

declare(strict_types=1);

namespace Tests\Feature\Customer360;

use App\Customers\CustomerIdentityResolver;
use App\Customers\Identity\IdentityCandidate;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The identity resolver is the single writer of customer identity. These tests pin the three
 * invariants that keep it safe: verified-only linking, anonymous-never-creates, and idempotent
 * creation (rule 36; ADR 0064, ADR 0071).
 */
final class CustomerIdentityResolverTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private CustomerIdentityResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CustomerIdentityResolver::class);
    }

    public function test_a_verified_identity_creates_a_customer(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $result = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
            displayName: 'Ana',
        );

        $this->assertNotNull($result->customer);
        $this->assertTrue($result->customerWasCreated);
        $this->assertSame(1, $result->identitiesLinked);
        $this->assertSame(CustomerStatus::Active, $result->customer->status);
        $this->assertSame(1, CustomerIdentity::query()->count());
    }

    public function test_resolving_the_same_verified_identity_twice_reuses_the_customer(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $first = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
        );

        // Different casing and spacing must still resolve to the same canonical identity.
        $second = $this->resolver->resolve(
            CustomerIdentitySource::Feedback,
            [IdentityCandidate::verified(CustomerIdentityType::Email, '  ANA@Example.com ')],
        );

        $this->assertSame($first->customer?->id, $second->customer?->id);
        $this->assertFalse($second->customerWasCreated);
        $this->assertSame(0, $second->identitiesLinked);
        $this->assertSame(1, Customer::query()->count());
        $this->assertSame(1, CustomerIdentity::query()->count());
    }

    /**
     * The anonymity guarantee: a survey response with nothing verifiable must not manufacture an
     * empty customer profile (rule 32, rule 36).
     */
    public function test_a_source_with_no_candidates_creates_nothing(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $result = $this->resolver->resolve(CustomerIdentitySource::Survey, []);

        $this->assertTrue($result->isAnonymous());
        $this->assertSame(0, Customer::query()->count());
    }

    /**
     * An unverified value is just something someone typed — linking on it would let one person
     * attach themselves to another person's history.
     */
    public function test_an_unverified_candidate_does_not_link_and_is_reported_as_a_suggestion(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $result = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::unverified(CustomerIdentityType::Email, 'ana@example.com')],
        );

        $this->assertTrue($result->isAnonymous());
        $this->assertSame(0, Customer::query()->count());
        $this->assertContains('email:unverified', $result->suggestedReasons);
    }

    public function test_an_unnormalizable_value_is_refused_without_echoing_it(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $result = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'not-an-email')],
        );

        $this->assertTrue($result->isAnonymous());
        $this->assertContains('email:unnormalizable', $result->suggestedReasons);

        foreach ($result->suggestedReasons as $reason) {
            $this->assertStringNotContainsString('not-an-email', $reason);
        }
    }

    public function test_a_pii_identity_never_persists_a_plaintext_value(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [
                IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com'),
                IdentityCandidate::verified(CustomerIdentityType::Phone, '+628112345678'),
            ],
        );

        foreach (CustomerIdentity::query()->get() as $identity) {
            $this->assertNull($identity->value_normalized);
            $this->assertStringNotContainsString('ana@example.com', (string) $identity->value_hash);
        }
    }

    /**
     * The cross-tenant non-correlation guarantee from ADR 0071: the same email in two tenants must
     * hash differently, so the identity table cannot confirm a person exists elsewhere.
     */
    public function test_the_same_email_in_two_tenants_yields_different_hashes_and_separate_customers(): void
    {
        $tenantA = $this->provisionTenant();
        $this->establishTenantContext($tenantA);
        $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'shared@example.com')],
        );
        $hashA = CustomerIdentity::query()->firstOrFail()->value_hash;
        $this->forgetTenantContext();

        $tenantB = $this->provisionTenant();
        $this->establishTenantContext($tenantB);
        $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'shared@example.com')],
        );
        $hashB = CustomerIdentity::query()->firstOrFail()->value_hash;

        $this->assertNotSame($hashA, $hashB);

        $this->forgetTenantContext();
        $this->establishTenantContext($tenantA);
        $this->assertSame(1, Customer::query()->count());
    }

    public function test_multiple_verified_identities_attach_to_one_customer(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $result = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [
                IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com'),
                IdentityCandidate::verified(CustomerIdentityType::Phone, '+628112345678'),
            ],
        );

        $this->assertSame(2, $result->identitiesLinked);
        $this->assertSame(1, Customer::query()->count());
        $this->assertSame(2, CustomerIdentity::query()->where('customer_id', $result->customer?->id)->count());
    }

    /**
     * Late-arriving data must land on the customer actually in use, not on a retired merged row.
     */
    public function test_resolving_an_identity_on_a_merged_customer_follows_the_survivor(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $first = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
        );
        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $first->customer?->forceFill([
            'status' => CustomerStatus::Merged,
            'merged_into_customer_id' => $survivor->id,
        ])->save();

        $again = $this->resolver->resolve(
            CustomerIdentitySource::Feedback,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
        );

        $this->assertSame($survivor->id, $again->customer?->id);
    }
}
