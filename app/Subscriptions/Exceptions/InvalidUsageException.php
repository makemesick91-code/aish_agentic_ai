<?php

declare(strict_types=1);

namespace App\Subscriptions\Exceptions;

use RuntimeException;

/**
 * Thrown when usage is recorded against an unknown meter or with a negative quantity outside
 * an explicit correction workflow (rule 31 §9.7).
 */
final class InvalidUsageException extends RuntimeException
{
    public static function unknownMeter(string $key): self
    {
        return new self("Unknown usage meter [{$key}]; it is not in the allowlist.");
    }

    public static function negativeQuantity(string $key): self
    {
        return new self("Refusing a negative quantity for meter [{$key}] without a correction workflow.");
    }
}
