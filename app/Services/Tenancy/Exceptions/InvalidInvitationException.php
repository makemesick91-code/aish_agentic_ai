<?php

declare(strict_types=1);

namespace App\Services\Tenancy\Exceptions;

use RuntimeException;

/** The invitation token is unknown, already used, revoked, or expired. */
final class InvalidInvitationException extends RuntimeException
{
    public static function make(): self
    {
        return new self('This invitation is invalid or has expired.');
    }
}
