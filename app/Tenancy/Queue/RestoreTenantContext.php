<?php

declare(strict_types=1);

namespace App\Tenancy\Queue;

use App\Models\Branch;
use App\Models\Tenant;
use App\Tenancy\Exceptions\CrossTenantAccessException;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantScope;
use Spatie\Permission\PermissionRegistrar;

/**
 * Job middleware that rehydrates the tenant context captured at dispatch and validates
 * it before the job runs: an unknown tenant fails the job, a suspended tenant is refused,
 * and the context is always cleared afterwards so it cannot leak to the next job on the
 * same worker (rule 03, rule 30).
 */
final class RestoreTenantContext
{
    public function handle(object $job, callable $next): mixed
    {
        $tenantId = $job->tenantId ?? null;
        $context = app(TenantContext::class);
        $registrar = app(PermissionRegistrar::class);

        // Defensive: never inherit a previous job's context.
        $context->forget();
        $registrar->setPermissionsTeamId(null);

        if ($tenantId !== null) {
            $tenant = Tenant::find($tenantId);

            if ($tenant === null) {
                throw new CrossTenantAccessException("Tenant [{$tenantId}] no longer exists; refusing to run job under an unknown tenant.");
            }

            if (! $tenant->isActive()) {
                throw new CrossTenantAccessException("Tenant [{$tenantId}] is not active; refusing to process its jobs.");
            }

            $branch = null;
            if (($job->branchId ?? null) !== null) {
                $branch = Branch::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', $tenant->id)
                    ->find($job->branchId);
            }

            $context->establish($tenant, null, $branch);
            $registrar->setPermissionsTeamId($tenant->id);
        }

        try {
            return $next($job);
        } finally {
            $context->forget();
            $registrar->setPermissionsTeamId(null);
        }
    }
}
