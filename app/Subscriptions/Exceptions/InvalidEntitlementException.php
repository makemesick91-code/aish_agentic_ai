<?php

declare(strict_types=1);

namespace App\Subscriptions\Exceptions;

use RuntimeException;

/**
 * Thrown when a plan feature is defined with an unknown key, a value that does not match the
 * key's declared type, or an invalid (negative, non-sentinel) limit (rule 31 §9.3).
 */
final class InvalidEntitlementException extends RuntimeException
{
    public static function unknownKey(string $key): self
    {
        return new self("Unknown entitlement key [{$key}]; it is not in the allowlist.");
    }

    public static function typeMismatch(string $key, string $expected): self
    {
        return new self("Entitlement [{$key}] expects a {$expected} value.");
    }

    public static function negativeLimit(string $key): self
    {
        return new self("Entitlement [{$key}] cannot be a negative limit; use the explicit unlimited sentinel.");
    }
}
