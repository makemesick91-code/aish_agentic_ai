<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use App\Enums\FeedbackStatus;
use RuntimeException;

/**
 * Raised when a feedback lifecycle transition is not permitted by the state machine, or a reopen is
 * attempted without a reason. Invalid transitions fail closed (rule 33; Step 8 §10).
 */
final class InvalidStatusTransitionException extends RuntimeException
{
    public static function notAllowed(FeedbackStatus $from, FeedbackStatus $to): self
    {
        return new self("A feedback item cannot move from {$from->value} to {$to->value}.");
    }

    public static function reasonRequired(FeedbackStatus $from, FeedbackStatus $to): self
    {
        return new self("Reopening a feedback item ({$from->value} → {$to->value}) requires a reason.");
    }
}
