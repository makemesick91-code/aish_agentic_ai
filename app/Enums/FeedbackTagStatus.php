<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of a tenant-owned manual feedback tag. An archived tag cannot be newly attached, but
 * existing historical tag links remain visible (rule 33; Step 8 §12).
 */
enum FeedbackTagStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function isAttachable(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archived => 'Archived',
        };
    }
}
