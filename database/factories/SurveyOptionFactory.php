<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyOption>
 */
class SurveyOptionFactory extends Factory
{
    protected $model = SurveyOption::class;

    public function definition(): array
    {
        $label = ucfirst(fake()->unique()->word());

        return [
            'tenant_id' => $this->tenantId(),
            'question_id' => SurveyQuestion::factory()->singleChoice(),
            'option_key' => 'opt_'.fake()->unique()->numerify('######'),
            'label' => $label,
            'value' => strtolower($label),
            'display_order' => fake()->unique()->numberBetween(1, 1_000_000),
            'score' => null,
        ];
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
