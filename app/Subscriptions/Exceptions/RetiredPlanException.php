<?php

declare(strict_types=1);

namespace App\Subscriptions\Exceptions;

use App\Models\Plan;
use RuntimeException;

/**
 * Thrown when a retired (or draft) plan is assigned to a new or changed subscription. Only an
 * active plan is assignable (rule 31 §9.2).
 */
final class RetiredPlanException extends RuntimeException
{
    public static function make(Plan $plan): self
    {
        return new self("Plan [{$plan->code} v{$plan->version}] is not assignable (status: {$plan->status->value}).");
    }
}
