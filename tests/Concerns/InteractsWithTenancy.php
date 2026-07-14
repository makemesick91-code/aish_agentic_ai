<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Tenancy\TenantContext;
use Spatie\Permission\PermissionRegistrar;

/**
 * Test-only helpers to establish and tear down a TenantContext, mirroring what the
 * ResolveTenantContext middleware does in a real request.
 */
trait InteractsWithTenancy
{
    protected function establishTenantContext(
        Tenant $tenant,
        ?TenantMembership $membership = null,
        ?Branch $branch = null,
    ): TenantContext {
        $membership ??= TenantMembership::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $context = app(TenantContext::class);
        $context->forget();
        $context->establish($tenant, $membership, $branch);

        // Mirror the RBAC team binding performed by the real middleware.
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        return $context;
    }

    protected function forgetTenantContext(): void
    {
        app(TenantContext::class)->forget();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
