<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Branch;
use App\Models\Tenant;
use App\Tenancy\Exceptions\TenantContextMissingException;
use App\Tenancy\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class TenantScopeIsolationTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    public function test_querying_a_tenant_owned_model_without_context_fails_closed(): void
    {
        $this->forgetTenantContext();

        $this->expectException(TenantContextMissingException::class);

        Branch::query()->get();
    }

    public function test_scope_returns_only_the_current_tenants_rows(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        Branch::factory()->for($a)->count(2)->create();
        Branch::factory()->for($b)->count(1)->create();

        $this->establishTenantContext($a);
        $this->assertSame(2, Branch::query()->count());

        $this->forgetTenantContext();
        $this->establishTenantContext($b);
        $this->assertSame(1, Branch::query()->count());
    }

    public function test_create_stamps_tenant_id_from_context(): void
    {
        $a = Tenant::factory()->create();
        $this->establishTenantContext($a);

        $branch = Branch::create(['name' => 'Pusat', 'code' => 'PUSAT']);

        $this->assertSame($a->id, $branch->tenant_id);
    }

    public function test_create_with_mismatched_tenant_id_is_rejected(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        $this->establishTenantContext($a);

        $this->expectException(\RuntimeException::class);

        Branch::create(['tenant_id' => $b->id, 'name' => 'Evil', 'code' => 'EVIL']);
    }

    public function test_without_global_scope_allows_allowlisted_cross_context_reads(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();
        Branch::factory()->for($a)->create();
        Branch::factory()->for($b)->create();

        // Simulates allowlisted infrastructure (provisioning/system maintenance).
        $count = Branch::withoutGlobalScope(TenantScope::class)->count();

        $this->assertSame(2, $count);
    }
}
