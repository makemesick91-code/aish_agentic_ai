<?php

declare(strict_types=1);

namespace Tests\Unit\Surveys;

use App\Enums\ScoreDirection;
use App\Surveys\Scoring\MetricCalculator;
use PHPUnit\Framework\TestCase;

final class MetricCalculatorTest extends TestCase
{
    private MetricCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new MetricCalculator;
    }

    // ---- CSAT -------------------------------------------------------------

    public function test_csat_with_no_responses_is_null(): void
    {
        $r = $this->calc->csat([], 1, 5, 4);
        $this->assertSame(0, $r->validCount);
        $this->assertNull($r->csatPercentage);
        $this->assertNull($r->averageScore);
    }

    public function test_csat_single_satisfied_response(): void
    {
        $r = $this->calc->csat([5], 1, 5, 4);
        $this->assertSame(1, $r->validCount);
        $this->assertSame(1, $r->satisfiedCount);
        $this->assertSame(100.0, $r->csatPercentage);
        $this->assertSame(5.0, $r->averageScore);
    }

    public function test_csat_boundary_below_threshold_is_not_satisfied(): void
    {
        $r = $this->calc->csat([3], 1, 5, 4);
        $this->assertSame(0, $r->satisfiedCount);
        $this->assertSame(0.0, $r->csatPercentage);
    }

    public function test_csat_boundary_at_threshold_is_satisfied(): void
    {
        $r = $this->calc->csat([4], 1, 5, 4);
        $this->assertSame(1, $r->satisfiedCount);
        $this->assertSame(100.0, $r->csatPercentage);
    }

    public function test_csat_excludes_null_and_out_of_range_values(): void
    {
        // null, 0 (below min), 6 (above max) all excluded; only 4 and 5 count.
        $r = $this->calc->csat([null, 0, 6, 4, 5], 1, 5, 4);
        $this->assertSame(2, $r->validCount);
        $this->assertSame(2, $r->satisfiedCount);
        $this->assertSame(100.0, $r->csatPercentage);
    }

    public function test_csat_rounding_policy_two_decimals(): void
    {
        // 2 satisfied of 3 valid -> 66.666... -> 66.67
        $r = $this->calc->csat([5, 4, 2], 1, 5, 4);
        $this->assertSame(3, $r->validCount);
        $this->assertSame(2, $r->satisfiedCount);
        $this->assertSame(66.67, $r->csatPercentage);
    }

    public function test_csat_lower_is_better_direction(): void
    {
        // With lower_is_better, satisfied means value <= threshold.
        $r = $this->calc->csat([1, 2, 5], 1, 5, 2, ScoreDirection::LowerIsBetter);
        $this->assertSame(2, $r->satisfiedCount);
        $this->assertSame(66.67, $r->csatPercentage);
    }

    // ---- NPS --------------------------------------------------------------

    public function test_nps_category_boundaries(): void
    {
        $r = $this->calc->nps([0, 6, 7, 8, 9, 10]);
        $this->assertSame(2, $r->detractors); // 0, 6
        $this->assertSame(2, $r->passives);   // 7, 8
        $this->assertSame(2, $r->promoters);  // 9, 10
    }

    public function test_nps_all_promoters_is_100(): void
    {
        $r = $this->calc->nps([9, 10, 9, 10]);
        $this->assertSame(100.0, $r->npsScore);
    }

    public function test_nps_all_detractors_is_negative_100(): void
    {
        $r = $this->calc->nps([0, 3, 6]);
        $this->assertSame(-100.0, $r->npsScore);
    }

    public function test_nps_mixed_population(): void
    {
        // 5 promoters, 2 passives, 3 detractors of 10 -> 50% - 30% = 20
        $r = $this->calc->nps([9, 9, 10, 10, 9, 7, 8, 0, 3, 6]);
        $this->assertSame(10, $r->validCount);
        $this->assertSame(50.0, $r->promoterPercentage);
        $this->assertSame(30.0, $r->detractorPercentage);
        $this->assertSame(20.0, $r->npsScore);
    }

    public function test_nps_with_no_valid_responses_is_null(): void
    {
        $r = $this->calc->nps([null, 11, -1]);
        $this->assertSame(0, $r->validCount);
        $this->assertNull($r->npsScore);
    }

    // ---- CES --------------------------------------------------------------

    public function test_ces_higher_is_better_average(): void
    {
        $r = $this->calc->ces([5, 6, 7], 1, 7, ScoreDirection::HigherIsBetter);
        $this->assertSame(3, $r->validCount);
        $this->assertSame(6.0, $r->average);
        $this->assertSame(ScoreDirection::HigherIsBetter, $r->direction);
    }

    public function test_ces_lower_is_better_average_and_precision(): void
    {
        // (1+2+2)/3 = 1.666... -> 1.67
        $r = $this->calc->ces([1, 2, 2], 1, 7, ScoreDirection::LowerIsBetter);
        $this->assertSame(1.67, $r->average);
        $this->assertSame(ScoreDirection::LowerIsBetter, $r->direction);
    }

    public function test_ces_boundaries_and_exclusion(): void
    {
        // 0 below min, 8 above max excluded; 1 and 7 remain.
        $r = $this->calc->ces([0, 8, 1, 7], 1, 7);
        $this->assertSame(2, $r->validCount);
        $this->assertSame(4.0, $r->average);
    }

    public function test_ces_with_no_valid_responses_is_null(): void
    {
        $r = $this->calc->ces([null], 1, 7);
        $this->assertSame(0, $r->validCount);
        $this->assertNull($r->average);
    }
}
