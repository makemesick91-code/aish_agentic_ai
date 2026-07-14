<?php

declare(strict_types=1);

namespace App\Surveys\Scoring;

/**
 * Deterministic CSAT result. Raw counts are authoritative; the percentage is derived with an
 * explicit rounding policy (2 decimals) and never inferred from UI labels (rule 32; Step 7
 * §13, ADR 0059).
 */
final readonly class CsatResult
{
    public function __construct(
        public int $validCount,
        public int $satisfiedCount,
        public ?float $averageScore,
        public ?float $csatPercentage,
    ) {}

    /** @return array{valid_count: int, satisfied_count: int, average_score: float|null, csat_percentage: float|null} */
    public function toArray(): array
    {
        return [
            'valid_count' => $this->validCount,
            'satisfied_count' => $this->satisfiedCount,
            'average_score' => $this->averageScore,
            'csat_percentage' => $this->csatPercentage,
        ];
    }
}
