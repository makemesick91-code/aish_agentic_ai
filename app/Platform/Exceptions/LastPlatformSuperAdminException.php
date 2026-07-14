<?php

declare(strict_types=1);

namespace App\Platform\Exceptions;

use RuntimeException;

/**
 * Thrown when an operation would remove, revoke, or demote the last Platform Super Admin. The
 * platform must always retain at least one Super Admin (rule 31 §10.3).
 */
final class LastPlatformSuperAdminException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Refusing to remove the last Platform Super Admin.');
    }
}
