<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Enums\SurveyMode;
use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyCampaign>
 */
class SurveyCampaignFactory extends Factory
{
    protected $model = SurveyCampaign::class;

    public function definition(): array
    {
        $survey = Survey::factory()->published();

        return [
            'tenant_id' => $this->tenantId(),
            'branch_id' => null,
            'survey_id' => $survey,
            'survey_version_id' => SurveyVersion::factory()->published()->for($survey),
            'name' => ucfirst(fake()->unique()->words(2, true)).' Campaign',
            'status' => CampaignStatus::Draft,
            'mode' => SurveyMode::Anonymous,
            'invitation_expiry_days' => 7,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Active]);
    }

    public function paused(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Paused]);
    }

    public function ended(): static
    {
        return $this->state(fn () => ['status' => CampaignStatus::Ended]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
