<?php

declare(strict_types=1);

namespace App\Tenancy\Concerns;

use App\Models\Tenant;
use App\Tenancy\Contracts\TenantOwned;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every tenant-owned model. Adds the fail-closed TenantScope, stamps
 * `tenant_id` from the current TenantContext on create, and guards against a create
 * that would cross tenants. Implements the TenantOwned contract's tenantKeyName().
 *
 * @see TenantOwned
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $context = app(TenantContext::class);
            $key = $model->tenantKeyName();

            if ($context->hasTenant()) {
                // Stamp from context; forbid an explicit mismatch (defence in depth).
                if (empty($model->{$key})) {
                    $model->{$key} = $context->tenantId();
                } elseif ((int) $model->{$key} !== $context->tenantId()) {
                    throw new \RuntimeException(
                        'Refusing to persist a tenant-owned record whose tenant_id differs from the active tenant context.'
                    );
                }
            }
            // With no context, tenant_id must have been set explicitly by allowlisted
            // infrastructure (e.g. provisioning); a null tenant_id will fail the DB
            // NOT NULL / FK constraint, which is the intended fail-closed behaviour.
        });
    }

    public function tenantKeyName(): string
    {
        return 'tenant_id';
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, $this->tenantKeyName());
    }
}
