<?php

declare(strict_types=1);

namespace App\Surveys\Scoring;

use App\Enums\ScoreDirection;

/**
 * The single, deterministic, presentation-independent calculator for CSAT, NPS, and CES.
 * All survey metric computation MUST go through this service — no controller, view, or query
 * may re-implement a formula (rule 32; Step 7 §13, ADR 0059).
 *
 * Rounding policy: raw counts are exact; percentages and averages are rounded to
 * self::PRECISION decimals at the boundary. Callers that need integer display values round
 * again themselves — the raw values here are the source of truth.
 */
final class MetricCalculator
{
    /** Decimal places for derived percentages/averages. */
    public const PRECISION = 2;

    /**
     * CSAT = satisfied valid responses / all valid responses * 100. A value is valid when it
     * is an integer within [$min, $max]; "satisfied" applies $threshold in $direction.
     *
     * @param  iterable<int|null>  $values
     */
    public function csat(
        iterable $values,
        int $min,
        int $max,
        int $threshold,
        ScoreDirection $direction = ScoreDirection::HigherIsBetter,
    ): CsatResult {
        $valid = $this->validIntegers($values, $min, $max);
        $count = count($valid);

        if ($count === 0) {
            return new CsatResult(0, 0, null, null);
        }

        $satisfied = 0;
        foreach ($valid as $v) {
            $isSatisfied = $direction === ScoreDirection::HigherIsBetter
                ? $v >= $threshold
                : $v <= $threshold;
            if ($isSatisfied) {
                $satisfied++;
            }
        }

        $average = round(array_sum($valid) / $count, self::PRECISION);
        $percentage = round($satisfied / $count * 100, self::PRECISION);

        return new CsatResult($count, $satisfied, $average, $percentage);
    }

    /**
     * NPS with fixed categories (detractors 0-6, passives 7-8, promoters 9-10). Only values in
     * [0, 10] are valid.
     *
     * @param  iterable<int|null>  $values
     */
    public function nps(iterable $values): NpsResult
    {
        $valid = $this->validIntegers($values, 0, 10);
        $count = count($valid);

        if ($count === 0) {
            return new NpsResult(0, 0, 0, 0, null, null, null);
        }

        $detractors = $passives = $promoters = 0;
        foreach ($valid as $v) {
            if ($v <= 6) {
                $detractors++;
            } elseif ($v <= 8) {
                $passives++;
            } else {
                $promoters++;
            }
        }

        $promoterPct = round($promoters / $count * 100, self::PRECISION);
        $detractorPct = round($detractors / $count * 100, self::PRECISION);
        // Derive the score from raw counts (not the rounded percentages) to avoid double rounding.
        $score = round(($promoters - $detractors) / $count * 100, self::PRECISION);

        return new NpsResult($count, $detractors, $passives, $promoters, $promoterPct, $detractorPct, $score);
    }

    /**
     * CES average over valid values in [$min, $max]. Direction is interpretation metadata only.
     *
     * @param  iterable<int|null>  $values
     */
    public function ces(
        iterable $values,
        int $min,
        int $max,
        ScoreDirection $direction = ScoreDirection::HigherIsBetter,
    ): CesResult {
        $valid = $this->validIntegers($values, $min, $max);
        $count = count($valid);

        if ($count === 0) {
            return new CesResult(0, null, $direction);
        }

        $average = round(array_sum($valid) / $count, self::PRECISION);

        return new CesResult($count, $average, $direction);
    }

    /**
     * Filter to integer values within [$min, $max]; null and out-of-range are excluded.
     *
     * @param  iterable<int|null>  $values
     * @return list<int>
     */
    private function validIntegers(iterable $values, int $min, int $max): array
    {
        $valid = [];
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }
            $int = (int) $value;
            if ($int >= $min && $int <= $max) {
                $valid[] = $int;
            }
        }

        return $valid;
    }
}
