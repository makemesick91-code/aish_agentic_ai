<?php

declare(strict_types=1);

namespace App\Surveys\Scoring;

/**
 * Deterministic NPS result. Categories are fixed (detractors 0-6, passives 7-8, promoters
 * 9-10); the score is promoter% minus detractor% in the range -100..+100 (rule 32; Step 7
 * §13, ADR 0059).
 */
final readonly class NpsResult
{
    public function __construct(
        public int $validCount,
        public int $detractors,
        public int $passives,
        public int $promoters,
        public ?float $promoterPercentage,
        public ?float $detractorPercentage,
        public ?float $npsScore,
    ) {}

    /** @return array<string, int|float|null> */
    public function toArray(): array
    {
        return [
            'valid_count' => $this->validCount,
            'detractors' => $this->detractors,
            'passives' => $this->passives,
            'promoters' => $this->promoters,
            'promoter_percentage' => $this->promoterPercentage,
            'detractor_percentage' => $this->detractorPercentage,
            'nps_score' => $this->npsScore,
        ];
    }
}
