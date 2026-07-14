<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->tenantId(),
            'branch_id' => null,
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'description' => fake()->optional()->sentence(),
            'status' => SurveyStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => SurveyStatus::Published]);
    }

    public function paused(): static
    {
        return $this->state(fn () => ['status' => SurveyStatus::Paused]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => SurveyStatus::Archived]);
    }

    /** Use the active tenant context when present so factory data stays tenant-consistent. */
    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
