<?php

declare(strict_types=1);

namespace App\Surveys\Exceptions;

use RuntimeException;

/**
 * Raised when a submitted response fails server-side validation (unknown/extra question,
 * missing required answer, type mismatch, out-of-range value, option not belonging to the
 * question, select-count violation, text too long). Client-side validation is supplemental
 * only (rule 32; Step 7 §18).
 */
final class ResponseValidationException extends RuntimeException
{
    /** @param array<string, string> $errors keyed by question key (or '_' for global) */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The survey response is invalid.');
    }
}
