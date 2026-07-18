<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle state of a canonical customer. `Merged` is a real domain state with a survivor
 * pointer — NOT a deletion: a merged customer row is retained in full so the merge stays
 * reversible (rule 36; ADR 0072).
 */
enum CustomerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Merged = 'merged';
    case Erased = 'erased';

    /** A merged or erased customer is never a merge participant or a new-link target. */
    public function isLinkable(): bool
    {
        return in_array($this, [self::Active, self::Inactive], true);
    }

    public function isMerged(): bool
    {
        return $this === self::Merged;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Merged => 'Merged',
            self::Erased => 'Erased',
        };
    }
}
