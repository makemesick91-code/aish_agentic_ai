<?php

declare(strict_types=1);

namespace Tests\Feature\Customer360;

use App\Authorization\Roles;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Subscriptions\EntitlementKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsCustomer360Plan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * HTTP surface for Customer 360: entitlement gating, permission gating, contact-PII gating, and
 * merge authorization (rule 36; contract §11).
 */
final class CustomerHttpTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsCustomer360Plan;
    use ProvisionsTenants;
    use RefreshDatabase;

    /**
     * @param  array<string, bool>  $flags
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function tenantWithRole(string $role, array $flags = []): array
    {
        $tenant = $this->provisionTenant();
        $this->provisionCustomer360Plan($tenant, $flags);
        [$user, $membership] = $this->memberWithRole($tenant, $role);
        $this->endRequestScope();

        return [$tenant, $user, $membership];
    }

    private function customerFor(Tenant $tenant, TenantMembership $membership, array $attributes = []): Customer
    {
        $this->establishTenantContext($tenant, $membership);
        $customer = Customer::factory()->create($attributes + ['tenant_id' => $tenant->id]);
        $this->endRequestScope();

        return $customer;
    }

    public function test_owner_can_view_the_directory_and_a_profile(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $customer = $this->customerFor($tenant, $membership, ['display_name' => 'Ana Lopez']);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.index'))->assertOk()->assertSee('Ana Lopez');
        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.show', $customer))->assertOk()->assertSee('Ana Lopez');
    }

    /** The surface fails closed when the plan does not grant Customer 360. */
    public function test_the_surface_requires_the_entitlement(): void
    {
        [$tenant, $owner] = $this->tenantWithRole(
            Roles::BUSINESS_OWNER,
            [EntitlementKeys::CUSTOMER_360_ENABLED => false],
        );

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.index'))->assertForbidden();
    }

    public function test_a_read_only_member_cannot_see_contact_pii(): void
    {
        [$tenant, $owner, $ownerMembership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $customer = $this->customerFor($tenant, $ownerMembership, [
            'display_name' => 'Ana Lopez',
            'contact_email' => 'ana.private@example.com',
        ]);

        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $this->endRequestScope();

        $response = $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.show', $customer));

        $response->assertOk()
            ->assertSee('Ana Lopez')
            // The address itself must never reach the page for a viewer without view-contact.
            ->assertDontSee('ana.private@example.com');
    }

    public function test_an_owner_can_see_contact_pii(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $customer = $this->customerFor($tenant, $membership, [
            'contact_email' => 'ana.private@example.com',
        ]);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('ana.private@example.com');
    }

    /** A read-only member holds customer.view but never customer.merge. */
    public function test_a_read_only_member_cannot_merge(): void
    {
        [$tenant, $owner, $ownerMembership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $survivor = $this->customerFor($tenant, $ownerMembership);
        $duplicate = $this->customerFor($tenant, $ownerMembership);

        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $this->endRequestScope();

        $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.merge', $survivor), [
                'merged_customer' => $duplicate->ulid,
                'reason' => 'Looks like a duplicate profile.',
            ])
            ->assertForbidden();
    }

    public function test_an_owner_can_merge_and_the_duplicate_is_retained(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $survivor = $this->customerFor($tenant, $membership);
        $duplicate = $this->customerFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.merge', $survivor), [
                'merged_customer' => $duplicate->ulid,
                'reason' => 'Same person, duplicate profile.',
            ])
            ->assertRedirect(route('customers.show', $survivor));

        $this->endRequestScope();
        $this->establishTenantContext($tenant, $membership);

        $this->assertTrue(Customer::query()->find($duplicate->id)?->isMerged());
    }

    public function test_merge_requires_a_reason(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $survivor = $this->customerFor($tenant, $membership);
        $duplicate = $this->customerFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.merge', $survivor), [
                'merged_customer' => $duplicate->ulid,
                'reason' => 'dup',
            ])
            ->assertSessionHasErrors('reason');
    }

    /** Merge can be withheld commercially without removing the 360 view. */
    public function test_merge_requires_the_merge_entitlement(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(
            Roles::BUSINESS_OWNER,
            [EntitlementKeys::CUSTOMER_360_MERGE_ENABLED => false],
        );
        $survivor = $this->customerFor($tenant, $membership);
        $duplicate = $this->customerFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.merge', $survivor), [
                'merged_customer' => $duplicate->ulid,
                'reason' => 'Same person, duplicate profile.',
            ])
            ->assertForbidden();
    }

    public function test_recording_consent_appends_a_decision(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $customer = $this->customerFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.consents.store', $customer), [
                'consent_type' => 'follow_up',
                'accepted' => '1',
                'consent_text_version' => 'v1',
            ])
            ->assertRedirect(route('customers.show', $customer));

        $this->endRequestScope();
        $this->establishTenantContext($tenant, $membership);

        $this->assertSame(1, $customer->consents()->count());
    }
}
