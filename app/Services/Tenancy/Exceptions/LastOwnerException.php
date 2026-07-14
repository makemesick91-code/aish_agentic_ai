<?php

declare(strict_types=1);

namespace App\Services\Tenancy\Exceptions;

use RuntimeException;

/** A tenant must always retain at least one active Business Owner. */
final class LastOwnerException extends RuntimeException
{
    public static function make(): self
    {
        return new self('A tenant must retain at least one active owner; this action would remove the last one.');
    }
}
