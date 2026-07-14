<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ResponseStatus;
use App\Enums\SurveyMode;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    public function definition(): array
    {
        $survey = Survey::factory()->published();

        return [
            'tenant_id' => $this->tenantId(),
            'branch_id' => null,
            'survey_id' => $survey,
            'survey_version_id' => SurveyVersion::factory()->published()->for($survey),
            'campaign_id' => null,
            'invitation_id' => null,
            'mode' => SurveyMode::Anonymous,
            'status' => ResponseStatus::Started,
            'locale' => 'id',
            'started_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ResponseStatus::Completed,
            'submitted_at' => now(),
        ]);
    }

    public function invalidated(string $reason = 'test'): static
    {
        return $this->state(fn () => [
            'status' => ResponseStatus::Invalidated,
            'invalidated_at' => now(),
            'invalidated_reason' => $reason,
        ]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
