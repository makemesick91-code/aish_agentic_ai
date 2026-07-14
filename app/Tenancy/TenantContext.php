<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Tenancy\Exceptions\TenantContextMissingException;
use RuntimeException;

/**
 * The immutable, request/job-scoped tenant context. Bound as a *scoped* singleton so
 * Laravel resets it between HTTP requests and between queue jobs (no leakage across a
 * long-running worker). It is established exactly once per request/job by middleware or
 * a job envelope; nothing else mutates the tenant afterwards (rule 03, rule 30).
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    private ?TenantMembership $membership = null;

    private ?Branch $branch = null;

    private bool $sealed = false;

    /**
     * Establish the tenant (and, for HTTP requests, the acting membership) for this
     * request/job. Immutable once set; a second attempt is a programming error, not a
     * silent overwrite. Queue jobs may establish a tenant without a membership.
     */
    public function establish(Tenant $tenant, ?TenantMembership $membership = null, ?Branch $branch = null): void
    {
        if ($this->sealed) {
            throw new RuntimeException('Tenant context is already established and is immutable for this request/job.');
        }

        $this->tenant = $tenant;
        $this->membership = $membership;
        $this->branch = $branch;
        $this->sealed = true;
    }

    /**
     * Select/replace the branch within the already-established tenant. The caller is
     * responsible for verifying the branch belongs to the tenant and is accessible.
     */
    public function setBranch(?Branch $branch): void
    {
        if (! $this->sealed) {
            throw new TenantContextMissingException('Cannot set a branch before a tenant context is established.');
        }

        if ($branch !== null && (int) $branch->tenant_id !== (int) $this->tenant->id) {
            throw new RuntimeException('Refusing to set a branch that belongs to a different tenant.');
        }

        $this->branch = $branch;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): Tenant
    {
        return $this->tenant ?? throw TenantContextMissingException::forAccessor();
    }

    public function tenantId(): int
    {
        return (int) $this->tenant()->id;
    }

    public function tenantIdOrNull(): ?int
    {
        return $this->tenant?->id !== null ? (int) $this->tenant->id : null;
    }

    public function membership(): TenantMembership
    {
        return $this->membership ?? throw TenantContextMissingException::forAccessor();
    }

    public function hasBranch(): bool
    {
        return $this->branch !== null;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function branchId(): ?int
    {
        return $this->branch?->id !== null ? (int) $this->branch->id : null;
    }

    /**
     * Clear the context. Used by the scoped-instance reset and by job envelopes that
     * explicitly tear down context after handling.
     */
    public function forget(): void
    {
        $this->tenant = null;
        $this->membership = null;
        $this->branch = null;
        $this->sealed = false;
    }
}
