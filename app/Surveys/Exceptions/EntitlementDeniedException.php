<?php

declare(strict_types=1);

namespace App\Surveys\Exceptions;

use RuntimeException;

/**
 * Raised when a survey action is refused by the authoritative entitlement resolver — either the
 * feature is not granted by the plan (fail-closed, incl. unknown keys) or a plan/usage limit is
 * reached. The reason is truthful and never claims a paid/collected state (rule 32; Step 7 §23).
 */
final class EntitlementDeniedException extends RuntimeException
{
    private function __construct(
        public readonly string $featureKey,
        public readonly string $reasonCode,
        public readonly bool $limitReached,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function notGranted(string $featureKey, string $reasonCode): self
    {
        return new self($featureKey, $reasonCode, false, 'This feature is not included in your current plan.');
    }

    public static function limitReached(string $featureKey): self
    {
        return new self($featureKey, 'limit_reached', true, 'You have reached your plan limit for this feature.');
    }
}
