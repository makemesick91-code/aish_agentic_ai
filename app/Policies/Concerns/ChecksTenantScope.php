<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

trait ChecksTenantScope
{
    /**
     * Defence in depth: even though tenant-owned models are already filtered by the
     * TenantScope on fetch, policies re-assert that the subject belongs to the active
     * tenant before authorizing a mutation (rule 30).
     */
    protected function inCurrentTenant(Model $model, string $key = 'tenant_id'): bool
    {
        $context = app(TenantContext::class);

        if (! $context->hasTenant()) {
            return false;
        }

        $tenantId = $model instanceof Tenant ? $model->id : $model->{$key};

        return (int) $tenantId === $context->tenantId();
    }
}
