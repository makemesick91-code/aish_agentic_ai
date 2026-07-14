<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\TenantMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Tenant-scoped RBAC: roles are provisioned per tenant team, permissions gate every
 * mutation, branch-scoped members are confined to granted branches, and a role in one
 * tenant grants nothing in another (rule 16, rule 20, rule 30).
 */
final class RbacAuthorizationTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_business_owner_can_update_the_tenant_and_create_branches(): void
    {
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->put('/settings', ['name' => 'Renamed Workspace', 'timezone' => 'Asia/Makassar', 'locale' => 'en'])
            ->assertRedirect(route('tenant.edit'));

        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branches', ['name' => 'New Branch', 'code' => 'NB001'])
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'name' => 'Renamed Workspace']);
        $this->assertDatabaseHas('branches', ['tenant_id' => $tenant->id, 'code' => 'NB001']);
    }

    public function test_read_only_user_is_denied_updates_and_creates(): void
    {
        $tenant = $this->provisionTenant();
        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->put('/settings', ['name' => 'Nope', 'timezone' => 'Asia/Makassar', 'locale' => 'en'])
            ->assertForbidden();

        $this->endRequestScope();

        $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branches', ['name' => 'Nope', 'code' => 'NOPE1'])
            ->assertForbidden();

        $this->assertDatabaseMissing('branches', ['code' => 'NOPE1']);
    }

    public function test_branch_manager_can_select_only_a_granted_branch(): void
    {
        $tenant = $this->provisionTenant();
        [$manager, $membership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);

        $granted = Branch::factory()->for($tenant)->create();
        $ungranted = Branch::factory()->for($tenant)->create();
        BranchAccessGrant::factory()->create([
            'tenant_id' => $tenant->id,
            'tenant_membership_id' => $membership->id,
            'branch_id' => $granted->id,
        ]);

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branch/select', ['branch' => $granted->ulid])
            ->assertRedirect(route('dashboard'));

        $this->endRequestScope();

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branch/select', ['branch' => $ungranted->ulid])
            ->assertForbidden();
    }

    public function test_role_assignment_requires_the_roles_assign_permission(): void
    {
        $tenant = $this->provisionTenant();
        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        [, $target] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);

        $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->patch('/users/'.$target->ulid.'/role', ['role' => Roles::BUSINESS_OWNER])
            ->assertForbidden();
    }

    public function test_a_member_cannot_escalate_their_own_role(): void
    {
        $tenant = $this->provisionTenant();
        [$manager, $membership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER);

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->patch('/users/'.$membership->ulid.'/role', ['role' => Roles::BUSINESS_OWNER])
            ->assertForbidden();

        // The manager did not gain the owner role.
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $this->assertFalse($manager->fresh()->hasRole(Roles::BUSINESS_OWNER));
    }

    public function test_a_member_without_a_tenant_role_is_denied(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/branches')
            ->assertForbidden();
    }

    public function test_a_role_in_one_tenant_grants_nothing_in_another(): void
    {
        $tenantA = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenantA, Roles::BUSINESS_OWNER);

        // Same user is a plain member of tenant B with no role there.
        $tenantB = $this->provisionTenant();
        TenantMembership::factory()->create([
            'tenant_id' => $tenantB->id,
            'user_id' => $user->id,
            'status' => MembershipStatus::Active,
        ]);

        // Owner in A, but acting inside B: the A role must not carry over.
        $this->actingAs($user)->withSession(['current_tenant_id' => $tenantB->id])
            ->put('/settings', ['name' => 'Cross Tenant', 'timezone' => 'Asia/Makassar', 'locale' => 'en'])
            ->assertForbidden();
    }
}
