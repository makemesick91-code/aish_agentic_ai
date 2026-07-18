<?php

declare(strict_types=1);

namespace App\Customers\Exceptions;

use RuntimeException;

/**
 * Raised when a raw identity value cannot be normalized into an unambiguous canonical form.
 *
 * The message deliberately never echoes the offending value: an error surface must not become a
 * way to get customer PII into logs or a response body (rule 36; rule 04).
 */
final class InvalidIdentityValueException extends RuntimeException
{
    public static function forEmail(): self
    {
        return new self('The identity value is not a valid email address.');
    }

    public static function forPhone(): self
    {
        return new self('The identity value could not be resolved to an unambiguous E.164 phone number.');
    }

    public static function forEmpty(): self
    {
        return new self('The identity value is empty.');
    }

    public static function tooLong(): self
    {
        return new self('The identity value exceeds the maximum supported length.');
    }
}
