<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Regression tests for the vertical privilege-escalation fix: assigning/inviting the
 * foundation owner role requires roles.manage-foundation (not roles.assign), and nobody
 * may change their own role (rule 30).
 */
final class RolePrivilegeEscalationTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_corporate_admin_cannot_promote_a_member_to_business_owner(): void
    {
        $tenant = $this->provisionTenant();
        [$admin] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);
        [, $targetMembership] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $response = $this->actingAs($admin)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->patch(route('users.role', $targetMembership), ['role' => Roles::BUSINESS_OWNER]);

        $response->assertForbidden();
    }

    public function test_corporate_admin_cannot_invite_a_business_owner(): void
    {
        $tenant = $this->provisionTenant();
        [$admin] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);

        $response = $this->actingAs($admin)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('invitations.store'), [
                'email' => 'newowner@example.com',
                'role' => Roles::BUSINESS_OWNER,
            ]);

        $response->assertForbidden();
    }

    public function test_business_owner_can_promote_a_member_to_business_owner(): void
    {
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        [$target, $targetMembership] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $response = $this->actingAs($owner)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->patch(route('users.role', $targetMembership), ['role' => Roles::BUSINESS_OWNER]);

        $response->assertRedirect();
        $this->endRequestScope();

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $this->assertTrue($target->fresh()->hasRole(Roles::BUSINESS_OWNER));
    }

    public function test_a_member_cannot_change_their_own_role(): void
    {
        $tenant = $this->provisionTenant();
        [$owner, $ownerMembership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $response = $this->actingAs($owner)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->patch(route('users.role', $ownerMembership), ['role' => Roles::READ_ONLY]);

        $response->assertForbidden();
    }

    public function test_corporate_admin_can_still_assign_a_non_owner_role(): void
    {
        $tenant = $this->provisionTenant();
        [$admin] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);
        [, $targetMembership] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $response = $this->actingAs($admin)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->patch(route('users.role', $targetMembership), ['role' => Roles::BRANCH_MANAGER]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
