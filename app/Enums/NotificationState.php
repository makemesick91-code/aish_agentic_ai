<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Truthful delivery state machine (rule 10, rule 31). `sent` means the channel adapter
 * accepted the message for delivery (for email: accepted by the mail transport) — never a
 * proof of end-user receipt, which no provider receipt currently substantiates. `queued`
 * is NOT `sent`. Invalid transitions are rejected by the dispatcher/job.
 */
enum NotificationState: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Suppressed = 'suppressed';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Sent, self::Failed, self::Cancelled, self::Suppressed => true,
            default => false,
        };
    }

    /** @return list<self> */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Pending => [self::Queued, self::Suppressed, self::Cancelled],
            self::Queued => [self::Sending, self::Cancelled],
            // Sending may resolve to sent, be re-queued for a bounded retry, or fail.
            self::Sending => [self::Sent, self::Queued, self::Failed],
            self::Sent, self::Failed, self::Cancelled, self::Suppressed => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Queued => 'Queued',
            self::Sending => 'Sending',
            self::Sent => 'Sent',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Suppressed => 'Suppressed',
        };
    }
}
