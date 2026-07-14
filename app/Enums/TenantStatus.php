<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case DeletionPending = 'deletion_pending';

    /** A tenant can be used for application access only while active. */
    public function allowsApplicationAccess(): bool
    {
        return $this === self::Active;
    }
}
