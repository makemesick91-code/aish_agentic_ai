<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Commercial state of a tenant's subscription. This is DISTINCT from a tenant's security /
 * operational suspension and from user/membership status — security suspension always takes
 * precedence and is never overridden by a commercial state (rule 31 §9.5).
 */
enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case Grace = 'grace';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * Whether this commercial state grants plan entitlements. Grace deliberately still
     * grants (a short window after period end); suspended/cancelled/expired do not.
     */
    public function grantsEntitlements(): bool
    {
        return match ($this) {
            self::Trialing, self::Active, self::Grace => true,
            self::Suspended, self::Cancelled, self::Expired => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::Grace => 'Grace period',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }
}
