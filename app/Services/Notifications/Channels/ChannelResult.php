<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

/**
 * The outcome of a single channel send attempt. `accepted` means the channel took
 * responsibility for the message (for email: the mail transport accepted it) — it is NOT a
 * proof of end-user receipt (rule 31 truthful states).
 */
final class ChannelResult
{
    private function __construct(
        public readonly bool $accepted,
        public readonly ?string $failureCode,
        public readonly bool $permanent,
    ) {}

    public static function accepted(): self
    {
        return new self(true, null, false);
    }

    public static function failed(string $failureCode, bool $permanent = false): self
    {
        return new self(false, $failureCode, $permanent);
    }
}
