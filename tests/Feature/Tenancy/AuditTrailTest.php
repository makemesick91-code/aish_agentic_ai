<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Audit\AuditRecorder;
use App\Authorization\Roles;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Tenancy\InvitationService;
use App\Services\Tenancy\MembershipService;
use App\Services\Tenancy\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Audit trail: important actions write a tenant-attributed record, records are append-only,
 * metadata is sanitised (secrets redacted), and a tenant only ever sees its own trail
 * (rule 07, rule 30).
 */
final class AuditTrailTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_provisioning_writes_an_audit_record(): void
    {
        Notification::fake();

        $result = app(TenantProvisioningService::class)->provision(
            tenantName: 'Audited Co',
            ownerEmail: 'owner@example.com',
            ownerName: 'Owner',
        );

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $result->tenant->id,
            'event' => 'tenant.provisioned',
        ]);
    }

    public function test_a_successful_login_writes_an_audit_record(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.login.succeeded',
            'actor_id' => $user->id,
        ]);
    }

    public function test_a_membership_change_writes_an_audit_record(): void
    {
        $tenant = $this->provisionTenant();
        [, $membership] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN);

        app(MembershipService::class)->suspend($membership);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'event' => 'tenant.membership.suspended',
        ]);
    }

    public function test_an_invitation_writes_an_audit_record(): void
    {
        Notification::fake();
        $tenant = $this->provisionTenant();

        app(InvitationService::class)->invite($tenant, 'invitee@example.com', Roles::READ_ONLY);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'event' => 'tenant.invitation.created',
        ]);
    }

    public function test_audit_records_are_append_only(): void
    {
        $log = AuditLog::factory()->create();
        $originalEvent = $log->event;

        $this->assertThrows(fn () => $log->update(['event' => 'tampered']), RuntimeException::class);
        $this->assertThrows(fn () => $log->delete(), RuntimeException::class);

        // The original record is intact and still present (the blocked write never landed).
        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'event' => $originalEvent]);
        $this->assertDatabaseMissing('audit_logs', ['id' => $log->id, 'event' => 'tampered']);
    }

    public function test_metadata_is_sanitised_and_never_stores_secrets(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $log = app(AuditRecorder::class)->record('test.sensitive', [
            'metadata' => [
                'password' => 'hunter2',
                'token' => 'plain-token-value',
                'role' => 'Business Owner',
                'nested' => ['api_key' => 'sk-live-123', 'safe' => 'ok'],
            ],
        ]);

        $metadata = $log->fresh()->metadata;

        $this->assertSame('[redacted]', $metadata['password']);
        $this->assertSame('[redacted]', $metadata['token']);
        $this->assertSame('[redacted]', $metadata['nested']['api_key']);
        $this->assertSame('Business Owner', $metadata['role']);
        $this->assertSame('ok', $metadata['nested']['safe']);

        // The raw secret never reaches storage.
        $this->assertStringNotContainsString('hunter2', (string) json_encode($metadata));
        $this->assertStringNotContainsString('plain-token-value', (string) json_encode($metadata));
    }

    public function test_a_tenant_can_only_view_its_own_audit_records(): void
    {
        $tenantA = $this->provisionTenant();
        [$owner, $membership] = $this->memberWithRole($tenantA, Roles::BUSINESS_OWNER);
        $tenantB = $this->provisionTenant();

        $logA = AuditLog::factory()->create(['tenant_id' => $tenantA->id]);
        $logB = AuditLog::factory()->create(['tenant_id' => $tenantB->id]);

        $this->establishTenantContext($tenantA, $membership);

        $this->assertTrue($owner->can('view', $logA));
        $this->assertFalse($owner->can('view', $logB));
    }
}
