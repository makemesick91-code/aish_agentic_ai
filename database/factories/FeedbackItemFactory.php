<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackSourceType;
use App\Enums\FeedbackStatus;
use App\Models\FeedbackItem;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackItem>
 */
class FeedbackItemFactory extends Factory
{
    protected $model = FeedbackItem::class;

    public function definition(): array
    {
        $response = SurveyResponse::factory()->completed()->create();

        return [
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
            'current_assignee_id' => null,
            'metric_snapshot' => [],
            'search_meta' => 'survey feedback',
            'search_content' => null,
            'created_by' => null,
            'last_activity_at' => now(),
        ];
    }

    public function status(FeedbackStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
