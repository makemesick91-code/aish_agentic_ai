<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\Tenancy\Exceptions\LastOwnerException;
use App\Services\Tenancy\InvitationService;
use App\Services\Tenancy\MembershipService;
use App\Tenancy\TenantScope;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Membership lifecycle: suspend/reactivate/revoke transitions, role stripping on revoke,
 * the last-active-owner invariant across every removal path, the one-membership-per-user
 * constraint, and invitation supersession (rule 16, rule 30).
 */
final class MembershipLifecycleTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_suspend_reactivate_and_revoke_transitions(): void
    {
        $tenant = $this->provisionTenant();
        [, $membership] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);
        $service = app(MembershipService::class);

        $service->suspend($membership);
        $this->assertSame(MembershipStatus::Suspended, $membership->fresh()->status);
        $this->assertNotNull($membership->fresh()->suspended_at);

        $service->reactivate($membership);
        $this->assertSame(MembershipStatus::Active, $membership->fresh()->status);
        $this->assertNull($membership->fresh()->suspended_at);

        $service->revoke($membership);
        $this->assertSame(MembershipStatus::Revoked, $membership->fresh()->status);
        $this->assertNotNull($membership->fresh()->revoked_at);
    }

    public function test_revoke_strips_tenant_roles(): void
    {
        $tenant = $this->provisionTenant();
        [$user, $membership] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->id);
        $this->assertTrue($user->fresh()->hasRole(Roles::CORPORATE_ADMIN));

        app(MembershipService::class)->revoke($membership);

        $registrar->setPermissionsTeamId($tenant->id);
        $this->assertFalse($user->fresh()->hasRole(Roles::CORPORATE_ADMIN));
    }

    public function test_the_last_active_owner_cannot_be_suspended(): void
    {
        $tenant = $this->provisionTenant();
        [, $ownerMembership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->assertThrows(
            fn () => app(MembershipService::class)->suspend($ownerMembership),
            LastOwnerException::class,
        );
        $this->assertSame(MembershipStatus::Active, $ownerMembership->fresh()->status);
    }

    public function test_the_last_active_owner_cannot_be_revoked(): void
    {
        $tenant = $this->provisionTenant();
        [, $ownerMembership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->assertThrows(
            fn () => app(MembershipService::class)->revoke($ownerMembership),
            LastOwnerException::class,
        );
        $this->assertSame(MembershipStatus::Active, $ownerMembership->fresh()->status);
    }

    public function test_the_last_active_owner_cannot_be_demoted_via_set_role(): void
    {
        $tenant = $this->provisionTenant();
        [, $ownerMembership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->assertThrows(
            fn () => app(MembershipService::class)->setRole($ownerMembership, Roles::READ_ONLY),
            LastOwnerException::class,
        );
    }

    public function test_a_duplicate_membership_is_rejected_by_the_unique_constraint(): void
    {
        $tenant = $this->provisionTenant();
        $user = User::factory()->create();

        TenantMembership::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $user->id]);

        $this->assertThrows(
            fn () => TenantMembership::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $user->id]),
            QueryException::class,
        );
    }

    public function test_a_new_invitation_supersedes_a_pending_one_for_the_same_email(): void
    {
        Notification::fake();
        $tenant = $this->provisionTenant();
        $service = app(InvitationService::class);

        $first = $service->invite($tenant, 'invitee@example.com', Roles::READ_ONLY);
        $second = $service->invite($tenant, 'invitee@example.com', Roles::CORPORATE_ADMIN);

        $this->assertFalse($first['invitation']->fresh()->isPending());
        $this->assertTrue($second['invitation']->fresh()->isPending());

        $pending = TenantInvitation::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('email', 'invitee@example.com')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->count();

        $this->assertSame(1, $pending);
    }
}
