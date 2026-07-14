<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Interpretation direction of a scored scale. For CSAT the satisfied threshold is applied in
 * this direction; for CES the average is interpreted against it. A metric is never labelled
 * "good" or "bad" without configured interpretation (rule 32; Step 7 §13).
 */
enum ScoreDirection: string
{
    case HigherIsBetter = 'higher_is_better';
    case LowerIsBetter = 'lower_is_better';

    public function label(): string
    {
        return match ($this) {
            self::HigherIsBetter => 'Higher is better',
            self::LowerIsBetter => 'Lower is better',
        };
    }
}
