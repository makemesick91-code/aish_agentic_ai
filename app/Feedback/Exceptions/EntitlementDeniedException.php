<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use RuntimeException;

/**
 * Raised when a feedback action is refused by the authoritative entitlement resolver — the feature
 * is not granted by the plan (fail-closed, including unknown keys). The reason is truthful and never
 * claims a paid/collected state; a security suspension always takes precedence (rule 33; Step 8 §22).
 */
final class EntitlementDeniedException extends RuntimeException
{
    private function __construct(
        public readonly string $featureKey,
        public readonly string $reasonCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function notGranted(string $featureKey, string $reasonCode): self
    {
        return new self($featureKey, $reasonCode, 'This feature is not included in your current plan.');
    }
}
