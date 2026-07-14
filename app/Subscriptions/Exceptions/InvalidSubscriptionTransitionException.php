<?php

declare(strict_types=1);

namespace App\Subscriptions\Exceptions;

use App\Enums\SubscriptionStatus;
use RuntimeException;

/**
 * Thrown when a subscription status transition is not permitted by the state machine
 * (rule 31 §9.4).
 */
final class InvalidSubscriptionTransitionException extends RuntimeException
{
    public static function make(SubscriptionStatus $from, SubscriptionStatus $to): self
    {
        return new self("Cannot transition subscription from [{$from->value}] to [{$to->value}].");
    }
}
