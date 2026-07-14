<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use App\Enums\QuestionType;
use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for adding a question to a draft survey version. Authorizes against the survey's
 * update policy so a branch-restricted or read-only user is rejected server-side (rule 32).
 */
final class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $survey = $this->route('survey');

        return $survey instanceof Survey && ($this->user()?->can('update', $survey) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'question_key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'prompt' => ['required', 'string', 'max:1000'],
            'help_text' => ['nullable', 'string', 'max:1000'],
            'required' => ['boolean'],
            'display_order' => ['required', 'integer', 'min:1', 'max:1000'],
            'scored' => ['boolean'],
            'scoring_config' => ['nullable', 'array'],
            'scoring_config.scale_min' => ['nullable', 'integer'],
            'scoring_config.scale_max' => ['nullable', 'integer'],
            'scoring_config.satisfied_threshold' => ['nullable', 'integer'],
            'scoring_config.direction' => ['nullable', 'string', 'in:higher_is_better,lower_is_better'],
            'validation_config' => ['nullable', 'array'],
        ];
    }
}
