<?php

declare(strict_types=1);

namespace App\Tenancy\Contracts;

/**
 * Marks an Eloquent model as tenant-owned: it carries a `tenant_id`, is filtered by the
 * TenantScope on every read, and has its `tenant_id` stamped from the immutable request
 * TenantContext on create. Every tenant-owned table in Step 6 implements this contract
 * so a fitness test (rule 30) can assert coverage rather than relying on review.
 */
interface TenantOwned
{
    /** The foreign-key column that binds this record to its owning tenant. */
    public function tenantKeyName(): string;
}
