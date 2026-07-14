<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SurveyMode;
use App\Enums\SurveyVersionStatus;
use App\Models\Survey;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyVersion>
 */
class SurveyVersionFactory extends Factory
{
    protected $model = SurveyVersion::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->tenantId(),
            'survey_id' => Survey::factory(),
            'version_number' => 1,
            'status' => SurveyVersionStatus::Draft,
            'title' => ucfirst(fake()->words(2, true)),
            'introduction' => fake()->optional()->sentence(),
            'completion_message' => 'Thank you.',
            'locale' => 'id',
            'mode' => SurveyMode::Anonymous,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => SurveyVersionStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function superseded(): static
    {
        return $this->state(fn () => [
            'status' => SurveyVersionStatus::Superseded,
            'published_at' => now()->subDay(),
        ]);
    }

    public function identified(): static
    {
        return $this->state(fn () => ['mode' => SurveyMode::Identified]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
