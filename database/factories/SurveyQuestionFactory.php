<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    protected $model = SurveyQuestion::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->tenantId(),
            'survey_version_id' => SurveyVersion::factory(),
            'question_key' => 'q_'.fake()->unique()->numerify('######'),
            'type' => QuestionType::Csat,
            'prompt' => fake()->sentence().'?',
            'help_text' => null,
            'required' => true,
            'display_order' => fake()->unique()->numberBetween(1, 1_000_000),
            'scored' => true,
            'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better'],
            'validation_config' => null,
        ];
    }

    public function csat(int $min = 1, int $max = 5, int $threshold = 4): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Csat,
            'scored' => true,
            'scoring_config' => ['scale_min' => $min, 'scale_max' => $max, 'satisfied_threshold' => $threshold, 'direction' => 'higher_is_better'],
        ]);
    }

    public function nps(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Nps,
            'scored' => true,
            'scoring_config' => ['scale_min' => 0, 'scale_max' => 10, 'direction' => 'higher_is_better'],
        ]);
    }

    public function ces(string $direction = 'higher_is_better', int $min = 1, int $max = 7): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Ces,
            'scored' => true,
            'scoring_config' => ['scale_min' => $min, 'scale_max' => $max, 'direction' => $direction],
        ]);
    }

    public function singleChoice(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::SingleChoice,
            'scored' => false,
            'scoring_config' => null,
        ]);
    }

    public function multipleChoice(?int $min = null, ?int $max = null): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::MultipleChoice,
            'scored' => false,
            'scoring_config' => null,
            'validation_config' => array_filter(['min_select' => $min, 'max_select' => $max], fn ($v) => $v !== null),
        ]);
    }

    public function yesNo(): static
    {
        return $this->state(fn () => ['type' => QuestionType::YesNo, 'scored' => false, 'scoring_config' => null]);
    }

    public function consent(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Consent,
            'required' => true,
            'scored' => false,
            'scoring_config' => null,
        ]);
    }

    public function shortText(int $maxLength = 255): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::ShortText,
            'scored' => false,
            'scoring_config' => null,
            'required' => false,
            'validation_config' => ['max_length' => $maxLength],
        ]);
    }

    public function longText(int $maxLength = 2000): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::LongText,
            'scored' => false,
            'scoring_config' => null,
            'required' => false,
            'validation_config' => ['max_length' => $maxLength],
        ]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
