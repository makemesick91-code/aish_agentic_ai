<?php

declare(strict_types=1);

namespace App\Surveys\Exceptions;

use RuntimeException;

/**
 * Raised when a survey version fails publish-time validation. Carries the list of specific
 * validation failures so the builder UI can present them (Step 7 §11.3).
 */
final class SurveyValidationException extends RuntimeException
{
    /** @param list<string> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Survey version cannot be published: '.implode('; ', $errors));
    }
}
