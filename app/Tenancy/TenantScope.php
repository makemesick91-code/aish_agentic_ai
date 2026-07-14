<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Tenancy\Contracts\TenantOwned;
use App\Tenancy\Exceptions\TenantContextMissingException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope applied to every tenant-owned model. It constrains all reads to the
 * current tenant, and — critically — fails closed: querying a tenant-owned model with
 * no tenant context throws rather than returning cross-tenant rows. Legitimate
 * cross-context infrastructure (provisioning, system maintenance) must opt out
 * explicitly with withoutGlobalScope(TenantScope::class), which is allowlisted and
 * asserted by an architecture test (rule 03, rule 20, rule 30).
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // The scope is only added to tenant-owned models; this guard makes that
        // contract explicit so tenantKeyName() is resolved on a TenantOwned type.
        if (! $model instanceof TenantOwned) {
            return;
        }

        $context = app(TenantContext::class);

        if (! $context->hasTenant()) {
            throw TenantContextMissingException::forQuery($model::class);
        }

        $builder->where(
            $model->getTable().'.'.$model->tenantKeyName(),
            $context->tenantId(),
        );
    }
}
