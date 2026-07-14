<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of a plan (a specific code+version). A retired plan MUST NOT be newly assigned,
 * but existing subscriptions that reference it remain valid (rule 31 §9.2).
 */
enum PlanStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Retired = 'retired';

    /** Only an active plan may be assigned to a new or changed subscription. */
    public function isAssignable(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Retired => 'Retired',
        };
    }
}
