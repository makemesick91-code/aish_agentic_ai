<?php

declare(strict_types=1);

namespace App\Surveys\Scoring;

use App\Enums\ScoreDirection;

/**
 * Deterministic CES result. The average is derived from valid values only; the direction is
 * carried for interpretation but the metric is never labelled "good"/"bad" without configured
 * interpretation (rule 32; Step 7 §13, ADR 0059).
 */
final readonly class CesResult
{
    public function __construct(
        public int $validCount,
        public ?float $average,
        public ScoreDirection $direction,
    ) {}

    /** @return array{valid_count: int, average: float|null, direction: string} */
    public function toArray(): array
    {
        return [
            'valid_count' => $this->validCount,
            'average' => $this->average,
            'direction' => $this->direction->value,
        ];
    }
}
