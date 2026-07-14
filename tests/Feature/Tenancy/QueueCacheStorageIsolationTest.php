<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Branch;
use App\Models\Tenant;
use App\Tenancy\Exceptions\CrossTenantAccessException;
use App\Tenancy\Exceptions\TenantContextMissingException;
use App\Tenancy\Queue\TenantContextSmokeJob;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

/**
 * Queue / cache / storage tenant isolation: fail-closed without context, per-tenant
 * namespacing, branch-scoped storage requirements, and a queued job that carries and then
 * clears its dispatch context so nothing leaks to the next unit of work (rule 03, rule 30).
 */
final class QueueCacheStorageIsolationTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    public function test_tenant_cache_fails_closed_without_a_context(): void
    {
        $this->forgetTenantContext();

        $this->assertThrows(
            fn () => app(TenantCache::class)->get('anything'),
            TenantContextMissingException::class,
        );
    }

    public function test_tenant_cache_namespaces_keys_per_tenant(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        $this->establishTenantContext($a);
        app(TenantCache::class)->put('dashboard', 'A');
        $keyA = app(TenantCache::class)->key('dashboard');

        $this->forgetTenantContext();
        $this->establishTenantContext($b);
        $keyB = app(TenantCache::class)->key('dashboard');

        $this->assertNotSame($keyA, $keyB);
        $this->assertNull(app(TenantCache::class)->get('dashboard'));
    }

    public function test_tenant_storage_prefixes_and_requires_a_branch_for_branch_paths(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $storage = app(TenantStorage::class);
        $this->assertStringStartsWith('tenants/'.$tenant->id.'/', $storage->path('a.txt'));
        $this->assertThrows(fn () => $storage->path('../escape'), CrossTenantAccessException::class);
        $this->assertThrows(fn () => $storage->branchPath('a.txt'), CrossTenantAccessException::class);
    }

    public function test_tenant_storage_branch_path_is_scoped_to_the_selected_branch(): void
    {
        $tenant = Tenant::factory()->create();
        $branch = Branch::factory()->for($tenant)->create();
        $this->establishTenantContext($tenant, null, $branch);

        $path = app(TenantStorage::class)->branchPath('report.csv');

        $this->assertStringStartsWith('tenants/'.$tenant->id.'/branches/'.$branch->id.'/', $path);
    }

    public function test_two_jobs_for_different_tenants_do_not_leak_context(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        $this->establishTenantContext($a);
        TenantContextSmokeJob::dispatch('job-a');
        // The worker cleared the context after handling; nothing lingers.
        $this->assertFalse(app(TenantContext::class)->hasTenant());

        $this->establishTenantContext($b);
        TenantContextSmokeJob::dispatch('job-b');
        $this->assertFalse(app(TenantContext::class)->hasTenant());

        // Each job observed exactly its own dispatch tenant; no cross-namespace bleed.
        $this->establishTenantContext($a);
        $this->assertSame($a->id, app(TenantCache::class)->get('job-a'));
        $this->assertNull(app(TenantCache::class)->get('job-b'));

        $this->forgetTenantContext();
        $this->establishTenantContext($b);
        $this->assertSame($b->id, app(TenantCache::class)->get('job-b'));
        $this->assertNull(app(TenantCache::class)->get('job-a'));
    }

    public function test_a_retried_job_still_runs_under_the_correct_tenant(): void
    {
        $a = Tenant::factory()->create();

        $this->establishTenantContext($a);
        TenantContextSmokeJob::dispatch('retry-key');

        // Simulate a retry: dispatch the same unit of work again under the same tenant.
        $this->forgetTenantContext();
        $this->establishTenantContext($a);
        TenantContextSmokeJob::dispatch('retry-key');

        $this->forgetTenantContext();
        $this->establishTenantContext($a);
        $this->assertSame($a->id, app(TenantCache::class)->get('retry-key'));
    }
}
