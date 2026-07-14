<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyAnswer>
 */
class SurveyAnswerFactory extends Factory
{
    protected $model = SurveyAnswer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->tenantId(),
            'response_id' => SurveyResponse::factory(),
            'question_id' => SurveyQuestion::factory(),
            'option_id' => null,
            'numeric_value' => 5,
            'boolean_value' => null,
            'text_value' => null,
        ];
    }

    public function numeric(int $value): static
    {
        return $this->state(fn () => ['numeric_value' => $value, 'boolean_value' => null, 'text_value' => null, 'option_id' => null]);
    }

    public function boolean(bool $value): static
    {
        return $this->state(fn () => ['boolean_value' => $value, 'numeric_value' => null, 'text_value' => null, 'option_id' => null]);
    }

    public function text(string $value): static
    {
        return $this->state(fn () => ['text_value' => $value, 'numeric_value' => null, 'boolean_value' => null, 'option_id' => null]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
