<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Models\User;
use App\Tenancy\Exceptions\CrossTenantAccessException;
use App\Tenancy\Queue\TenantContextSmokeJob;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * CRITICAL cross-tenant attack matrix (Section 16.7). A user acting inside tenant A must
 * never reach tenant B on ANY surface: route-model binding (IDOR), mass assignment,
 * indexes, audit, cache, queue, or storage. A cross-tenant read or write here is a hard
 * failure (rule 03, rule 20, rule 30).
 */
final class CrossTenantAttackMatrixTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_idor_on_branch_routes_via_cross_tenant_ulid_returns_404(): void
    {
        [$owner, $tenantA] = $this->ownerActingInFreshTenant();
        $tenantB = $this->provisionTenant();
        $branchB = Branch::factory()->for($tenantB)->create();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenantA->id])
            ->put('/branches/'.$branchB->ulid, ['name' => 'Hijacked'])
            ->assertNotFound();

        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenantA->id])
            ->patch('/branches/'.$branchB->ulid.'/deactivate')
            ->assertNotFound();

        // B's branch is untouched by either attempt.
        $this->assertTrue($branchB->fresh()->isActive());
        $this->assertNotSame('Hijacked', $branchB->fresh()->name);
    }

    public function test_idor_on_membership_routes_via_cross_tenant_ulid_returns_404(): void
    {
        [$owner, $tenantA] = $this->ownerActingInFreshTenant();
        $tenantB = $this->provisionTenant();
        [, $membershipB] = $this->memberWithRole($tenantB, Roles::CORPORATE_ADMIN);

        foreach ([
            fn () => $this->patch('/users/'.$membershipB->ulid.'/suspend'),
            fn () => $this->patch('/users/'.$membershipB->ulid.'/reactivate'),
            fn () => $this->delete('/users/'.$membershipB->ulid),
            fn () => $this->patch('/users/'.$membershipB->ulid.'/role', ['role' => Roles::READ_ONLY]),
        ] as $call) {
            $this->endRequestScope();
            $this->actingAs($owner)->withSession(['current_tenant_id' => $tenantA->id]);
            $call()->assertNotFound();
        }

        // The membership in B is never mutated by A's attempts.
        $this->assertSame(MembershipStatus::Active, $membershipB->fresh()->status);
    }

    public function test_idor_on_invitation_destroy_via_cross_tenant_ulid_returns_404(): void
    {
        [$owner, $tenantA] = $this->ownerActingInFreshTenant();
        $tenantB = $this->provisionTenant();
        $invitationB = TenantInvitation::factory()->for($tenantB)->create();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenantA->id])
            ->delete('/invitations/'.$invitationB->ulid)
            ->assertNotFound();

        $this->assertTrue($invitationB->fresh()->isPending());
    }

    public function test_mass_assignment_of_tenant_id_on_branch_create_is_stamped_to_the_acting_tenant(): void
    {
        [$owner, $tenantA] = $this->ownerActingInFreshTenant();
        $tenantB = $this->provisionTenant();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenantA->id])
            ->post('/branches', [
                'tenant_id' => $tenantB->id, // attacker-controlled: must be ignored
                'name' => 'Injected Branch',
                'code' => 'INJ001',
            ])
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', ['code' => 'INJ001', 'tenant_id' => $tenantA->id]);
        $this->assertDatabaseMissing('branches', ['code' => 'INJ001', 'tenant_id' => $tenantB->id]);
    }

    public function test_records_created_in_one_tenant_never_appear_in_another(): void
    {
        $tenantA = $this->provisionTenant();
        $tenantB = $this->provisionTenant();

        $branchA = Branch::factory()->for($tenantA)->create();
        [, $membershipA] = $this->memberWithRole($tenantA, Roles::CORPORATE_ADMIN);
        $invitationA = TenantInvitation::factory()->for($tenantA)->create();

        // Now act entirely inside tenant B.
        $this->establishTenantContext($tenantB);

        $this->assertNull(Branch::where('ulid', $branchA->ulid)->first());
        $this->assertNull(TenantMembership::where('ulid', $membershipA->ulid)->first());
        $this->assertNull(TenantInvitation::where('ulid', $invitationA->ulid)->first());
        $this->assertSame(0, Branch::query()->count());
    }

    public function test_audit_of_one_tenant_is_not_visible_to_another(): void
    {
        $tenantA = $this->provisionTenant();
        $tenantB = $this->provisionTenant();

        AuditLog::factory()->count(3)->create(['tenant_id' => $tenantA->id, 'event' => 'a.event']);
        AuditLog::factory()->count(2)->create(['tenant_id' => $tenantB->id, 'event' => 'b.event']);

        $this->assertSame(3, AuditLog::forTenant($tenantA->id)->count());
        $this->assertSame(2, AuditLog::forTenant($tenantB->id)->count());
        $this->assertSame(0, AuditLog::forTenant($tenantA->id)->where('event', 'b.event')->count());
    }

    public function test_tenant_cache_keys_do_not_collide_across_tenants(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->establishTenantContext($tenantA);
        $keyA = app(TenantCache::class)->key('report');
        app(TenantCache::class)->put('report', 'A-value');

        $this->forgetTenantContext();
        $this->establishTenantContext($tenantB);
        $keyB = app(TenantCache::class)->key('report');
        app(TenantCache::class)->put('report', 'B-value');

        $this->assertNotSame($keyA, $keyB);
        $this->assertSame('B-value', app(TenantCache::class)->get('report'));

        $this->forgetTenantContext();
        $this->establishTenantContext($tenantA);
        $this->assertSame('A-value', app(TenantCache::class)->get('report'));
    }

    public function test_a_queued_job_runs_under_its_dispatch_tenant_and_never_leaks(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->establishTenantContext($tenantA);
        TenantContextSmokeJob::dispatch('poison-key');

        // Sync middleware clears context after the job — no leakage to the next unit of work.
        $this->assertFalse(app(TenantContext::class)->hasTenant());

        // The tenant observed *inside* the worker was A (written to A's namespaced key).
        $this->establishTenantContext($tenantA);
        $this->assertSame($tenantA->id, app(TenantCache::class)->get('poison-key'));

        // B never observed the job and its namespace is empty.
        $this->forgetTenantContext();
        $this->establishTenantContext($tenantB);
        $this->assertNull(app(TenantCache::class)->get('poison-key'));
    }

    public function test_tenant_storage_rejects_traversal_and_roots_under_the_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);
        $storage = app(TenantStorage::class);

        $this->assertStringStartsWith('tenants/'.$tenant->id.'/', $storage->path('reports/q1.csv'));

        $this->assertThrows(fn () => $storage->path('../secret.txt'), CrossTenantAccessException::class);
        $this->assertThrows(fn () => $storage->path('/etc/passwd'), CrossTenantAccessException::class);
        // A branch-scoped path with no branch context fails closed.
        $this->assertThrows(fn () => $storage->branchPath('x.txt'), CrossTenantAccessException::class);
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function ownerActingInFreshTenant(): array
    {
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        return [$owner, $tenant];
    }
}
