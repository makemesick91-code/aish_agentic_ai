<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Enums\MetricType;
use App\Enums\ResponseStatus;
use App\Enums\ScoreDirection;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Surveys\Scoring\MetricCalculator;

/**
 * Basic operational survey summaries. Everything is tenant-scoped (fail-closed), version-aware,
 * and branch-filterable; metrics are computed only through the single MetricCalculator over
 * stored raw answers of completed responses. No cross-tenant aggregation, no answer content in
 * summaries, no AI insight or fabricated benchmark (rule 32; Step 7 §24).
 */
final class SurveySummaryService
{
    public function __construct(private readonly MetricCalculator $calculator) {}

    /**
     * Operational overview for a survey.
     *
     * @return array{total_completed: int, by_version: array<int, int>, by_campaign: array<int, int>, by_branch: array<int|string, int>, current_version_metrics: array<string, mixed>}
     */
    public function overview(Survey $survey): array
    {
        $completed = SurveyResponse::query()
            ->where('survey_id', $survey->id)
            ->where('status', ResponseStatus::Completed->value);

        $currentMetrics = [];
        if ($survey->current_version_id !== null) {
            $current = SurveyVersion::find($survey->current_version_id);
            if ($current !== null) {
                $currentMetrics = $this->metricsForVersion($current);
            }
        }

        return [
            'total_completed' => (clone $completed)->count(),
            'by_version' => (clone $completed)->selectRaw('survey_version_id, count(*) as c')
                ->groupBy('survey_version_id')->pluck('c', 'survey_version_id')->map(fn ($c) => (int) $c)->all(),
            'by_campaign' => (clone $completed)->whereNotNull('campaign_id')->selectRaw('campaign_id, count(*) as c')
                ->groupBy('campaign_id')->pluck('c', 'campaign_id')->map(fn ($c) => (int) $c)->all(),
            'by_branch' => (clone $completed)->selectRaw('branch_id, count(*) as c')
                ->groupBy('branch_id')->pluck('c', 'branch_id')->map(fn ($c) => (int) $c)->all(),
            'current_version_metrics' => $currentMetrics,
        ];
    }

    /**
     * Compute CSAT/NPS/CES per scored question of a version over completed responses, optionally
     * filtered to one branch.
     *
     * @return array<string, array<string, mixed>>
     */
    public function metricsForVersion(SurveyVersion $version, ?int $branchId = null): array
    {
        $results = [];

        $scored = $version->questions()->where('scored', true)->get();
        foreach ($scored as $question) {
            $metric = $question->type->metricType();
            if ($metric === null) {
                continue;
            }

            $values = $this->numericValues($question, $branchId);
            $config = $question->scoring_config ?? [];

            $result = match ($metric) {
                MetricType::Csat => $this->calculator->csat(
                    $values,
                    (int) ($config['scale_min'] ?? 1),
                    (int) ($config['scale_max'] ?? 5),
                    (int) ($config['satisfied_threshold'] ?? 4),
                    ScoreDirection::tryFrom((string) ($config['direction'] ?? 'higher_is_better')) ?? ScoreDirection::HigherIsBetter,
                )->toArray(),
                MetricType::Nps => $this->calculator->nps($values)->toArray(),
                MetricType::Ces => $this->calculator->ces(
                    $values,
                    (int) ($config['scale_min'] ?? 1),
                    (int) ($config['scale_max'] ?? 7),
                    ScoreDirection::tryFrom((string) ($config['direction'] ?? 'higher_is_better')) ?? ScoreDirection::HigherIsBetter,
                )->toArray(),
            };

            $results[$question->question_key] = ['metric' => $metric->value] + $result;
        }

        return $results;
    }

    /**
     * Raw numeric answers for a question among completed responses (tenant-scoped via the base
     * model's global scope).
     *
     * @return list<int>
     */
    private function numericValues(SurveyQuestion $question, ?int $branchId): array
    {
        return SurveyAnswer::query()
            ->join('survey_responses', 'survey_answers.response_id', '=', 'survey_responses.id')
            ->where('survey_answers.question_id', $question->id)
            ->where('survey_responses.status', ResponseStatus::Completed->value)
            ->when($branchId !== null, fn ($q) => $q->where('survey_responses.branch_id', $branchId))
            ->whereNotNull('survey_answers.numeric_value')
            ->pluck('survey_answers.numeric_value')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
