<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Authorization\Roles;
use App\Customers\CustomerIdentityResolver;
use App\Customers\CustomerInteractionsReadModel;
use App\Customers\CustomerMergeService;
use App\Customers\Exceptions\CustomerMergeException;
use App\Customers\Identity\IdentityCandidate;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsCustomer360Plan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Hostile cross-tenant and cross-branch matrix for Customer 360. Cross-tenant customer access or
 * identity correlation is a release blocker (rule 36; contract §15).
 */
final class Sf10CrossTenantMatrixTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsCustomer360Plan;
    use ProvisionsTenants;
    use RefreshDatabase;

    /**
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function workspace(string $role = Roles::BUSINESS_OWNER): array
    {
        $tenant = $this->provisionTenant();
        $this->provisionCustomer360Plan($tenant);
        [$user, $membership] = $this->memberWithRole($tenant, $role);
        $this->endRequestScope();

        return [$tenant, $user, $membership];
    }

    private function customerIn(Tenant $tenant, TenantMembership $membership): Customer
    {
        $this->establishTenantContext($tenant, $membership);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $this->endRequestScope();

        return $customer;
    }

    /** Tenant A must not be able to read tenant B's customer, even with a correct ULID. */
    public function test_a_tenant_cannot_view_another_tenants_customer(): void
    {
        [$tenantA, $ownerA] = $this->workspace();
        [$tenantB, , $membershipB] = $this->workspace();
        $customerB = $this->customerIn($tenantB, $membershipB);

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get(route('customers.show', $customerB))
            ->assertNotFound();
    }

    /** A guessed ULID must not reveal whether the record exists in another tenant. */
    public function test_a_cross_tenant_merge_target_is_not_found_rather_than_forbidden(): void
    {
        [$tenantA, $ownerA, $membershipA] = $this->workspace();
        [$tenantB, , $membershipB] = $this->workspace();

        $survivorA = $this->customerIn($tenantA, $membershipA);
        $customerB = $this->customerIn($tenantB, $membershipB);

        $response = $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post(route('customers.merge', $survivorA), [
                'merged_customer' => $customerB->ulid,
                'reason' => 'Attempting a cross-tenant merge.',
            ]);

        // Reported as "could not be found" — never as an authorization failure that would confirm
        // the record exists somewhere.
        $response->assertSessionHasErrors('merged_customer');

        $this->endRequestScope();
        $this->establishTenantContext($tenantB, $membershipB);
        $this->assertFalse(Customer::query()->find($customerB->id)?->isMerged());
    }

    public function test_a_cross_tenant_merge_is_refused_at_the_service_layer(): void
    {
        [$tenantA, , $membershipA] = $this->workspace();
        [$tenantB, , $membershipB] = $this->workspace();

        $survivorA = $this->customerIn($tenantA, $membershipA);
        $customerB = $this->customerIn($tenantB, $membershipB);

        $this->establishTenantContext($tenantA, $membershipA);

        // Defence in depth: even holding both model instances, the service cannot merge across
        // tenants because the locking read is tenant-scoped and fails closed.
        $this->expectException(CustomerMergeException::class);
        app(CustomerMergeService::class)->merge($survivorA, $customerB, 'Cross-tenant attempt.');
    }

    /**
     * The ADR 0071 non-correlation guarantee, stated as a security property: an attacker with read
     * access to one tenant's identity table must not be able to confirm that the same person exists
     * in another tenant by comparing hashes.
     */
    public function test_identity_hashes_do_not_correlate_across_tenants(): void
    {
        [$tenantA, , $membershipA] = $this->workspace();
        [$tenantB, , $membershipB] = $this->workspace();

        $shared = 'shared.person@example.com';

        $this->establishTenantContext($tenantA, $membershipA);
        // Resolved per scope, as it is in a real request/job — the resolver holds a scoped
        // TenantContext, so reusing one instance across two scopes is a test artifact only.
        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, $shared)],
        );
        $hashA = CustomerIdentity::query()->firstOrFail()->value_hash;
        $this->endRequestScope();

        $this->establishTenantContext($tenantB, $membershipB);
        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, $shared)],
        );
        $hashB = CustomerIdentity::query()->firstOrFail()->value_hash;

        $this->assertNotSame($hashA, $hashB);
    }

    /** Identity rows must never be a plaintext directory of customer contact details. */
    public function test_identity_rows_never_contain_plaintext_contact_values(): void
    {
        [$tenant, , $membership] = $this->workspace();

        $this->establishTenantContext($tenant, $membership);
        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [
                IdentityCandidate::verified(CustomerIdentityType::Email, 'private.person@example.com'),
                IdentityCandidate::verified(CustomerIdentityType::Phone, '+628112345678'),
            ],
        );

        $rows = CustomerIdentity::query()->get()->map(
            fn (CustomerIdentity $identity): string => json_encode($identity->toArray()) ?: ''
        )->implode(' ');

        $this->assertStringNotContainsString('private.person@example.com', $rows);
        $this->assertStringNotContainsString('628112345678', $rows);
    }

    /** A tenant-owned query without context must fail closed, never return everything. */
    public function test_querying_customers_without_tenant_context_fails_closed(): void
    {
        [$tenant, , $membership] = $this->workspace();
        $this->customerIn($tenant, $membership);

        $this->forgetTenantContext();

        $this->expectException(\Throwable::class);
        Customer::query()->get();
    }

    /**
     * A user with no membership must never reach customer data. The context middleware fails
     * closed by refusing to establish the tampered session tenant at all, so the request is
     * redirected away rather than rendering — and the customer is never disclosed.
     */
    public function test_a_user_without_membership_cannot_reach_the_customer_surface(): void
    {
        [$tenant, , $membership] = $this->workspace();
        $customer = $this->customerIn($tenant, $membership);

        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('customers.show', $customer));

        $this->assertContains($response->getStatusCode(), [302, 403]);
        $response->assertDontSee($customer->ulid);
    }

    /**
     * Regression for the Step 10 security review (F-1, HIGH).
     *
     * A customer with a NULL primary branch is deliberately tenant-wide visible, so without an
     * explicit branch predicate the 360 timeline would hand a branch-restricted viewer that
     * customer's feedback from branches they cannot reach — a Step 8 branch-scoping bypass.
     */
    public function test_the_interactions_timeline_is_branch_scoped(): void
    {
        [$tenant, , $ownerMembership] = $this->workspace();

        $this->establishTenantContext($tenant, $ownerMembership);
        $branchA = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $branchB = Branch::factory()->create(['tenant_id' => $tenant->id]);

        // Tenant-wide customer (null primary branch) with feedback in two different branches.
        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'primary_branch_id' => null,
        ]);

        foreach ([$branchA, $branchB] as $branch) {
            $item = FeedbackItem::factory()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
            ]);
            $item->forceFill(['customer_id' => $customer->id])->save();
        }
        $this->endRequestScope();

        [$manager, $managerMembership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER);
        $managerMembership->forceFill(['all_branches' => false])->save();
        BranchAccessGrant::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_membership_id' => $managerMembership->id,
            'branch_id' => $branchA->id,
        ]);

        $this->establishTenantContext($tenant, $managerMembership->fresh());

        $readModel = app(CustomerInteractionsReadModel::class);
        $interactions = $readModel->interactions($customer, $manager);
        $summary = $readModel->summary($customer, $manager);

        // Only the branch-A item is reachable; the branch-B item must not appear, and the counts
        // must not disclose it either.
        $this->assertCount(1, $interactions->items());
        $this->assertSame($branchA->id, $interactions->items()[0]->branch_id);
        $this->assertSame(1, $summary['feedback_count']);
    }

    /**
     * Regression for the Step 10 security review (F-2, HIGH).
     *
     * A split writes to BOTH customers, so reversing a merge whose merged customer sits in an
     * unreachable branch must be refused — otherwise a branch-restricted actor could rewrite
     * identity state outside their scope.
     */
    public function test_a_branch_restricted_member_cannot_reverse_a_merge_involving_an_unreachable_customer(): void
    {
        [$tenant, , $ownerMembership] = $this->workspace();

        $this->establishTenantContext($tenant, $ownerMembership);
        $branchA = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $branchB = Branch::factory()->create(['tenant_id' => $tenant->id]);

        $survivor = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'primary_branch_id' => $branchA->id,
        ]);
        $merged = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'primary_branch_id' => $branchB->id,
        ]);

        $event = app(CustomerMergeService::class)->merge($survivor, $merged, 'Merged by a tenant-wide operator.');
        $this->endRequestScope();

        // An operator who holds customer.merge but can only reach branch A.
        [, $restrictedMembership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $restrictedMembership->forceFill(['all_branches' => false])->save();
        BranchAccessGrant::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_membership_id' => $restrictedMembership->id,
            'branch_id' => $branchA->id,
        ]);

        $this->establishTenantContext($tenant, $restrictedMembership->fresh());

        $this->expectException(CustomerMergeException::class);
        app(CustomerMergeService::class)->split($event, 'Attempting an out-of-scope reversal.');
    }

    /**
     * A branch-restricted operator must not absorb a customer whose branch they cannot reach —
     * otherwise merge becomes a way to pull out-of-scope data into a reachable profile.
     */
    public function test_a_branch_restricted_member_cannot_merge_a_customer_outside_their_branch(): void
    {
        [$tenant, , $ownerMembership] = $this->workspace();

        $this->establishTenantContext($tenant, $ownerMembership);
        $branchA = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $branchB = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $reachable = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'primary_branch_id' => $branchA->id,
        ]);
        $outOfScope = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'primary_branch_id' => $branchB->id,
        ]);
        $this->endRequestScope();

        [$manager, $managerMembership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER);
        $managerMembership->forceFill(['all_branches' => false])->save();
        BranchAccessGrant::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_membership_id' => $managerMembership->id,
            'branch_id' => $branchA->id,
        ]);
        $this->endRequestScope();

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('customers.merge', $reachable), [
                'merged_customer' => $outOfScope->ulid,
                'reason' => 'Attempting an out-of-scope merge.',
            ])
            ->assertForbidden();
    }
}
