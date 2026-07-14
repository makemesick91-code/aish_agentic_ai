<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackSourceType;
use App\Enums\FeedbackStatus;
use App\Models\FeedbackItem;
use App\Models\SurveyResponse;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Projects a completed survey response into exactly one operational feedback item. The projection is
 * idempotent: a duplicate source (same tenant + source_type + source_id) returns the existing item
 * rather than creating a second, protected both by an up-front lookup and by the unique index (race
 * losers resolve to the winner). It stores an operational snapshot and allowlisted search text only
 * — never a free-text copy of the response beyond the permission-gated `search_content` projection
 * (rule 33; Step 8 §9).
 */
final class FeedbackProjector
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly UsageMeter $usage,
        private readonly FeedbackTimeline $timeline,
    ) {}

    public function projectFromSurveyResponse(SurveyResponse $response): FeedbackItem
    {
        $existing = $this->find($response);
        if ($existing !== null) {
            return $existing;
        }

        $response->loadMissing(['answers.question', 'survey', 'campaign']);
        $snapshot = $this->buildMetricSnapshot($response);
        [$searchMeta, $searchContent] = $this->buildSearchText($response);

        try {
            $item = DB::transaction(function () use ($response, $snapshot, $searchMeta, $searchContent): FeedbackItem {
                $item = FeedbackItem::create([
                    'tenant_id' => $response->tenant_id,
                    'branch_id' => $response->branch_id,
                    'source_type' => FeedbackSourceType::SurveyResponse,
                    'source_id' => $response->id,
                    'survey_response_id' => $response->id,
                    'survey_id' => $response->survey_id,
                    'survey_version_id' => $response->survey_version_id,
                    'campaign_id' => $response->campaign_id,
                    'invitation_id' => $response->invitation_id,
                    'status' => FeedbackStatus::New,
                    'metric_snapshot' => $snapshot,
                    'search_meta' => $searchMeta,
                    'search_content' => $searchContent,
                    'created_by' => null,
                    'last_activity_at' => now(),
                ]);

                $this->timeline->record($item, FeedbackEventType::Projected, [
                    'source_type' => FeedbackSourceType::SurveyResponse->value,
                    'survey_response_ulid' => $response->ulid,
                ], actorId: null);

                return $item;
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent projection won the race; return its item — never a duplicate.
            return $this->find($response) ?? throw new \RuntimeException('Feedback projection race could not resolve.');
        }

        $this->usage->record(
            $response->tenant,
            MeterKeys::FEEDBACK_ITEMS_PROJECTED,
            1,
            'feedback-projection:'.$response->id,
            sourceReference: 'survey_response:'.$response->id,
            actorId: null,
        );

        $this->audit->record('feedback.projected', [
            'tenant_id' => $response->tenant_id,
            'branch_id' => $response->branch_id,
            'subject' => $item,
            'metadata' => [
                'source_type' => FeedbackSourceType::SurveyResponse->value,
                'survey_response_id' => $response->id,
                'feedback_item_ulid' => $item->ulid,
            ],
        ]);

        return $item;
    }

    private function find(SurveyResponse $response): ?FeedbackItem
    {
        return FeedbackItem::query()
            ->where('source_type', FeedbackSourceType::SurveyResponse)
            ->where('source_id', $response->id)
            ->first();
    }

    /**
     * Raw scored-answer snapshot for inbox display/filtering. Values are read from stored answers —
     * never recomputed from a second scoring formula. Aggregate metrics reuse the canonical
     * MetricCalculator elsewhere.
     *
     * @return array<string, mixed>
     */
    private function buildMetricSnapshot(SurveyResponse $response): array
    {
        $primary = ['csat' => null, 'nps' => null, 'ces' => null];
        $metrics = [];

        foreach ($response->answers as $answer) {
            $question = $answer->question;
            if ($question === null) {
                continue;
            }
            $metric = $question->type->metricType();
            if ($metric === null) {
                continue;
            }
            $metrics[] = ['metric' => $metric->value, 'value' => $answer->numeric_value];
            if ($primary[$metric->value] === null) {
                $primary[$metric->value] = $answer->numeric_value;
            }
        }

        return $primary + ['metrics' => $metrics];
    }

    /**
     * @return array{0: string|null, 1: string|null} [search_meta, search_content]
     */
    private function buildSearchText(SurveyResponse $response): array
    {
        $meta = collect([
            $response->survey?->name,
            $response->campaign?->name,
        ])->filter()->implode(' ');

        $content = $response->answers
            ->filter(fn ($answer) => $answer->question?->type->usesText() === true)
            ->map(fn ($answer) => $answer->text_value)
            ->filter()
            ->implode(' ');

        return [
            $meta !== '' ? $meta : null,
            $content !== '' ? Str::limit($content, 4000, '') : null,
        ];
    }
}
