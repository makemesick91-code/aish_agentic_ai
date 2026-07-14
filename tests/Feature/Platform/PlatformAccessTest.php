<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Authorization\Roles;
use App\Enums\PlatformRole;
use App\Platform\PlatformPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\InteractsWithPlatform;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The platform plane is isolated: it requires a platform role, enforces least privilege per
 * permission, and never crosses with tenant access in either direction (rule 31 §10.1, §10.3).
 */
final class PlatformAccessTest extends TestCase
{
    use InteractsWithPlatform;
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_a_guest_cannot_reach_the_platform_area(): void
    {
        $this->get('/platform-admin')->assertRedirect();
    }

    public function test_a_tenant_only_user_is_denied_platform_access(): void
    {
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->actingAs($owner)->get('/platform-admin')->assertForbidden();
    }

    public function test_a_platform_operator_can_open_the_dashboard(): void
    {
        $admin = $this->platformUser(PlatformRole::Admin);

        $this->actingAs($admin)->get('/platform-admin')->assertOk();
    }

    public function test_a_platform_role_does_not_grant_tenant_data_access(): void
    {
        $super = $this->platformUser(PlatformRole::SuperAdmin);
        $tenant = $this->provisionTenant();

        // Even forcing a tenant into the session must not let a non-member act in it.
        $this->actingAs($super)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/dashboard')
            ->assertRedirect(route('tenant.select'));
    }

    public function test_read_only_and_auditor_cannot_mutate(): void
    {
        foreach ([PlatformRole::ReadOnly, PlatformRole::Auditor] as $role) {
            $user = $this->platformUser($role);

            $this->assertFalse(Gate::forUser($user)->allows(PlatformPermissions::PLANS_MANAGE), "{$role->value} must not manage plans");
            $this->assertFalse(Gate::forUser($user)->allows(PlatformPermissions::SUBSCRIPTIONS_MANAGE), "{$role->value} must not manage subscriptions");
            $this->assertFalse(Gate::forUser($user)->allows(PlatformPermissions::TENANTS_MANAGE_STATUS), "{$role->value} must not manage tenant status");
        }
    }

    public function test_support_cannot_manage_plans_or_subscriptions_but_can_view_tenants(): void
    {
        $support = $this->platformUser(PlatformRole::Support);

        $this->assertTrue(Gate::forUser($support)->allows(PlatformPermissions::TENANTS_VIEW));
        $this->assertFalse(Gate::forUser($support)->allows(PlatformPermissions::PLANS_MANAGE));
        $this->assertFalse(Gate::forUser($support)->allows(PlatformPermissions::SUBSCRIPTIONS_MANAGE));
    }

    public function test_finance_can_manage_subscriptions_but_not_users(): void
    {
        $finance = $this->platformUser(PlatformRole::Finance);

        $this->assertTrue(Gate::forUser($finance)->allows(PlatformPermissions::SUBSCRIPTIONS_MANAGE));
        $this->assertFalse(Gate::forUser($finance)->allows(PlatformPermissions::USERS_MANAGE));
        $this->assertFalse(Gate::forUser($finance)->allows(PlatformPermissions::SUPPORT_NOTES_MANAGE));
    }

    public function test_only_super_admin_manages_platform_users(): void
    {
        $this->assertTrue(Gate::forUser($this->platformUser(PlatformRole::SuperAdmin))->allows(PlatformPermissions::USERS_MANAGE));
        $this->assertFalse(Gate::forUser($this->platformUser(PlatformRole::Admin))->allows(PlatformPermissions::USERS_MANAGE));
    }

    public function test_a_read_only_operator_is_forbidden_from_a_manage_route(): void
    {
        $reader = $this->platformUser(PlatformRole::ReadOnly);

        $this->actingAs($reader)
            ->post('/platform-admin/plans', ['code' => 'x', 'version' => 1, 'name' => 'X'])
            ->assertForbidden();
    }
}
