<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Authorization\TenantRoleProvisioner;
use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Test-only helpers to stand up a fully role-provisioned tenant and onboarded members,
 * mirroring the real provisioning + invitation-acceptance outcome (rule 30). Roles are
 * assigned within the tenant's Spatie team so they can never leak across tenants.
 */
trait ProvisionsTenants
{
    /** Create a tenant with its foundation roles/permissions provisioned. */
    protected function provisionTenant(array $attributes = []): Tenant
    {
        $tenant = Tenant::factory()->create($attributes);
        app(TenantRoleProvisioner::class)->provision($tenant);

        return $tenant;
    }

    /**
     * Create an active member of $tenant holding $role within the tenant's team.
     *
     * @return array{0: User, 1: TenantMembership}
     */
    protected function memberWithRole(Tenant $tenant, string $role, array $membershipAttributes = []): array
    {
        $user = User::factory()->create();

        $membership = TenantMembership::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'status' => MembershipStatus::Active,
        ], $membershipAttributes));

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->id);
        $user->assignRole($role);
        $registrar->forgetCachedPermissions();

        return [$user->fresh(), $membership->fresh()];
    }

    /**
     * Reset request-scoped state between two HTTP calls in a single test. A real request
     * (or queue job) always starts with a fresh scoped TenantContext; the test harness
     * reuses one application instance, so we mirror that request boundary here. Without
     * this, the immutable (sealed) TenantContext from the first request would reject the
     * second request's establish().
     */
    protected function endRequestScope(): void
    {
        $this->app->forgetScopedInstances();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    /** Create an active member of $tenant with NO tenant role assigned. */
    protected function memberWithoutRole(Tenant $tenant, array $membershipAttributes = []): array
    {
        $user = User::factory()->create();

        $membership = TenantMembership::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'status' => MembershipStatus::Active,
        ], $membershipAttributes));

        return [$user->fresh(), $membership->fresh()];
    }
}
