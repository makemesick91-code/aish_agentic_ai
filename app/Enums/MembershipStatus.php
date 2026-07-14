<?php

declare(strict_types=1);

namespace App\Enums;

enum MembershipStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';

    /** Only an active membership grants access to a tenant. */
    public function grantsAccess(): bool
    {
        return $this === self::Active;
    }
}
