<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of a survey response. A `completed` response is immutable through the normal public
 * workflow; `invalidated` requires an authorized internal process with a reason and never
 * deletes the response (rule 32; Step 7 §18, §19).
 */
enum ResponseStatus: string
{
    case Started = 'started';
    case Completed = 'completed';
    case Invalidated = 'invalidated';

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    /** Whether this response counts toward metric calculation. */
    public function countsTowardMetrics(): bool
    {
        return $this === self::Completed;
    }

    public function label(): string
    {
        return match ($this) {
            self::Started => 'Started',
            self::Completed => 'Completed',
            self::Invalidated => 'Invalidated',
        };
    }
}
