<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Authorization\TenantRoleProvisioner;
use App\Enums\MembershipStatus;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Services\Tenancy\Exceptions\InvalidInvitationException;
use App\Services\Tenancy\Exceptions\LastOwnerException;
use App\Services\Tenancy\InvitationService;
use App\Services\Tenancy\MembershipService;
use App\Services\Tenancy\TenantProvisioningService;
use App\Tenancy\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class ProvisioningAndInvitationTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    public function test_provisioning_creates_tenant_owner_and_invitation(): void
    {
        Notification::fake();

        $result = app(TenantProvisioningService::class)->provision(
            tenantName: 'Klinik Gigi Daengtisia',
            ownerEmail: 'owner@example.com',
            ownerName: 'Owner',
            branchName: 'Daengtisia Pusat',
            branchCode: 'PUSAT',
        );

        $this->assertSame(TenantStatus::Active, $result->tenant->status);
        $this->assertNotNull($result->tenant->settings()->withoutGlobalScope(TenantScope::class)->first());
        $this->assertNotNull($result->branch);
        $this->assertSame(MembershipStatus::Invited, $result->ownerMembership->status);
        $this->assertTrue($result->ownerInvitation->isPending());

        // Owner holds the Business Owner role within this tenant's team.
        $this->establishTenantContext($result->tenant, $result->ownerMembership->fresh());
        $this->assertTrue($result->owner->fresh()->hasRole(Roles::BUSINESS_OWNER));

        Notification::assertSentOnDemand(TenantInvitationNotification::class);
    }

    public function test_invitation_accept_is_one_time_and_email_bound(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantRoleProvisioner::class)->provision($tenant);
        /** @var InvitationService $service */
        $service = app(InvitationService::class);

        $invite = $service->invite($tenant, 'member@example.com', Roles::CORPORATE_ADMIN);
        $token = $invite['plain_token'];

        $user = User::factory()->unverified()->create(['email' => 'member@example.com']);

        $membership = $service->accept($token, $user);
        $this->assertSame(MembershipStatus::Active, $membership->status);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // One-time: a replay fails.
        $this->expectException(InvalidInvitationException::class);
        $service->accept($token, $user);
    }

    public function test_invitation_cannot_be_accepted_by_a_different_email(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantRoleProvisioner::class)->provision($tenant);
        $invite = app(InvitationService::class)->invite($tenant, 'intended@example.com', Roles::READ_ONLY);

        $attacker = User::factory()->create(['email' => 'attacker@example.com']);

        $this->expectException(InvalidInvitationException::class);
        app(InvitationService::class)->accept($invite['plain_token'], $attacker);
    }

    public function test_last_active_owner_cannot_be_revoked(): void
    {
        $result = app(TenantProvisioningService::class)->provision(
            tenantName: 'Solo Owner Co',
            ownerEmail: 'solo@example.com',
            ownerName: 'Solo',
        );

        // Simulate the owner having accepted (membership active; role already assigned).
        $result->ownerMembership->forceFill([
            'status' => MembershipStatus::Active,
            'accepted_at' => now(),
        ])->save();

        $this->expectException(LastOwnerException::class);
        app(MembershipService::class)->revoke($result->ownerMembership->fresh());
    }

    public function test_second_owner_allows_revoking_the_first(): void
    {
        $result = app(TenantProvisioningService::class)->provision(
            tenantName: 'Two Owner Co',
            ownerEmail: 'first@example.com',
            ownerName: 'First',
        );
        $result->ownerMembership->forceFill(['status' => MembershipStatus::Active, 'accepted_at' => now()])->save();

        // Add a second active owner.
        $second = User::factory()->create();
        $secondMembership = TenantMembership::factory()->create([
            'tenant_id' => $result->tenant->id,
            'user_id' => $second->id,
            'status' => MembershipStatus::Active,
        ]);
        app(MembershipService::class)->setRole($secondMembership, Roles::BUSINESS_OWNER);

        // Now the first can be revoked; one active owner remains.
        app(MembershipService::class)->revoke($result->ownerMembership->fresh());
        $this->assertSame(MembershipStatus::Revoked, $result->ownerMembership->fresh()->status);
        $this->assertSame(1, app(MembershipService::class)->activeOwnerCount($result->tenant->fresh()));
    }
}
