<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Truthful lifecycle state of a unique survey invitation. `sent` means accepted by the
 * configured transport (never a proven end-user receipt); `opened` means a valid survey page
 * was accessed; `completed` means a valid response transaction committed. A `revoked` or
 * `expired` invitation can never be used (rule 32; Step 7 §17.1).
 */
enum InvitationStatus: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Opened = 'opened';
    case Completed = 'completed';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case DeliveryFailed = 'delivery_failed';

    /** Whether the invitation can still be opened / completed. */
    public function isUsable(): bool
    {
        return match ($this) {
            self::Created, self::Sent, self::Opened, self::DeliveryFailed => true,
            self::Completed, self::Expired, self::Revoked => false,
        };
    }

    /** Terminal states never transition further. */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Expired, self::Revoked => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Sent => 'Sent',
            self::Opened => 'Opened',
            self::Completed => 'Completed',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
            self::DeliveryFailed => 'Delivery failed',
        };
    }
}
