<?php

declare(strict_types=1);

namespace App\Services\Notifications\Exceptions;

use RuntimeException;

/**
 * Thrown when a tenant notification targets a user who is not a member of that tenant.
 * A tenant MUST NOT be able to notify another tenant's members (rule 03, rule 31 §8.3).
 */
final class CrossTenantRecipientException extends RuntimeException
{
    public static function make(int $tenantId, int $userId): self
    {
        return new self("User [{$userId}] is not a member of tenant [{$tenantId}]; refusing to dispatch a cross-tenant notification.");
    }
}
