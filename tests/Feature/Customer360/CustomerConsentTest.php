<?php

declare(strict_types=1);

namespace Tests\Feature\Customer360;

use App\Customers\CustomerConsentService;
use App\Customers\CustomerMergeService;
use App\Enums\CustomerConsentType;
use App\Models\Customer;
use App\Models\CustomerConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Consent is append-only and fails closed. Two properties matter most: an absent decision is never
 * treated as permission, and a merge never discards a recorded objection (rule 36, rule 32;
 * ADR 0064, ADR 0072).
 */
final class CustomerConsentTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private CustomerConsentService $consents;

    protected function setUp(): void
    {
        parent::setUp();
        $this->consents = app(CustomerConsentService::class);
    }

    public function test_withdrawing_consent_appends_rather_than_edits(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->consents->record($customer, CustomerConsentType::Marketing, true, 'v1', 'survey');
        $this->consents->record($customer, CustomerConsentType::Marketing, false, 'v1', 'operator');

        // Both decisions survive; the history of what was agreed is never overwritten.
        $this->assertSame(2, CustomerConsent::query()->where('customer_id', $customer->id)->count());
        $this->assertFalse($this->consents->latest($customer, CustomerConsentType::Marketing)?->accepted);
    }

    /** "Never asked" is not "agreed" — the difference is the whole point of consent. */
    public function test_an_absent_decision_is_not_permission(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertNull($this->consents->latest($customer, CustomerConsentType::Marketing));
        $this->assertFalse($this->consents->mayContact($customer, CustomerConsentType::Marketing));
    }

    public function test_an_accepted_purpose_permits_contact(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->consents->record($customer, CustomerConsentType::FollowUp, true, 'v1', 'survey');

        $this->assertTrue($this->consents->mayContact($customer, CustomerConsentType::FollowUp));
    }

    /** An explicit do-not-contact overrides every other permission. */
    public function test_do_not_contact_suppresses_every_purpose(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->consents->record($customer, CustomerConsentType::FollowUp, true, 'v1', 'survey');
        $this->consents->record($customer, CustomerConsentType::DoNotContact, true, 'v1', 'operator');

        $this->assertFalse($this->consents->mayContact($customer, CustomerConsentType::FollowUp));
        $this->assertFalse($this->consents->mayContact($customer, CustomerConsentType::Marketing));
    }

    /**
     * The merge-safety property: absorbing a duplicate must not lose that person's objection,
     * or a merge would become a way to launder a do-not-contact request.
     */
    public function test_a_merge_preserves_the_merged_customers_objection(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $duplicate = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->consents->record($survivor, CustomerConsentType::Marketing, true, 'v1', 'survey');
        $this->consents->record($duplicate, CustomerConsentType::DoNotContact, true, 'v1', 'operator');

        app(CustomerMergeService::class)->merge($survivor, $duplicate, 'Same person, duplicate profile.');

        $this->assertFalse($this->consents->mayContact($survivor->fresh(), CustomerConsentType::Marketing));
    }

    public function test_consent_records_cannot_be_edited_or_deleted(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $consent = $this->consents->record($customer, CustomerConsentType::Marketing, true, 'v1', 'survey');

        $this->expectException(\RuntimeException::class);
        $consent->update(['accepted' => false]);
    }

    public function test_the_consent_text_version_is_retained(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->consents->record($customer, CustomerConsentType::Marketing, true, 'v7', 'survey');

        $this->assertSame('v7', $this->consents->latest($customer, CustomerConsentType::Marketing)?->consent_text_version);
    }
}
