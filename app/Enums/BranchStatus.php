<?php

declare(strict_types=1);

namespace App\Enums;

enum BranchStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /** Only an active branch may be selected as the working context. */
    public function isSelectable(): bool
    {
        return $this === self::Active;
    }
}
