<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use RuntimeException;

/**
 * Thrown when a tenant-owned model is queried, or the current tenant is requested,
 * without an established TenantContext. This is the fail-closed guarantee: the system
 * never silently falls back to "all tenants" or "the first tenant" (rule 03, rule 30).
 */
final class TenantContextMissingException extends RuntimeException
{
    public static function forQuery(string $model): self
    {
        return new self(
            "No tenant context is established; refusing to query tenant-owned model [{$model}]. ".
            'Establish a TenantContext or use an allowlisted withoutGlobalScope(TenantScope) bypass.'
        );
    }

    public static function forAccessor(): self
    {
        return new self('No tenant context is established for the current request or job.');
    }
}
