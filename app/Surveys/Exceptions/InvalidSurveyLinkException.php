<?php

declare(strict_types=1);

namespace App\Surveys\Exceptions;

use RuntimeException;

/**
 * Raised for any failure to resolve a public survey link or invitation token — invalid,
 * expired, revoked, already completed, or a paused/ended campaign. The message is intentionally
 * generic so a guessed token can never reveal whether it maps to a real tenant/campaign
 * (no enumeration) (rule 32; Step 7 §18).
 */
final class InvalidSurveyLinkException extends RuntimeException
{
    public static function generic(): self
    {
        return new self('This survey link is not valid or is no longer available.');
    }

    public static function alreadyCompleted(): self
    {
        return new self('This survey has already been completed.');
    }
}
