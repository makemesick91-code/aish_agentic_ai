<?php

declare(strict_types=1);

namespace App\Platform\Exceptions;

use RuntimeException;

/**
 * Thrown when a user tries to change their own platform roles, or when a non–Super Admin tries
 * to grant Super Admin. Self-escalation is prohibited (rule 31 §10.3).
 */
final class PlatformSelfEscalationException extends RuntimeException
{
    public static function selfModification(): self
    {
        return new self('A user cannot modify their own platform roles.');
    }

    public static function cannotGrantSuperAdmin(): self
    {
        return new self('Only a Platform Super Admin can grant the Super Admin role.');
    }
}
