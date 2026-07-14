<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Enums\QuestionType;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Surveys\Exceptions\ResponseValidationException;

/**
 * Server-side validation and normalization of a submitted response against the exact answered
 * survey version. All input is untrusted: unknown questions/options are rejected, required
 * answers enforced, answer type matched to question type, numeric values range-checked, choice
 * options verified to belong to their question, select counts enforced, and text length-capped.
 * Free-text is never interpreted as an instruction and is length-limited (rule 32; Step 7 §12,
 * §18-§19).
 */
final class ResponseValidator
{
    private const DEFAULT_SHORT_TEXT_MAX = 255;

    private const DEFAULT_LONG_TEXT_MAX = 2000;

    /**
     * Validate and normalize. Returns a list of answer-row specs, or throws with per-question
     * errors.
     *
     * @param  array<string, mixed>  $answers  map of question_key => submitted value
     * @return list<array{question_id: int, option_id: int|null, numeric_value: int|null, boolean_value: bool|null, text_value: string|null}>
     */
    public function validate(SurveyVersion $version, array $answers): array
    {
        $questions = $version->questions()->with('options')->orderBy('display_order')->get()->keyBy('question_key');

        $errors = [];
        $specs = [];

        // Reject unknown/extra question keys (no silent acceptance).
        foreach (array_keys($answers) as $submittedKey) {
            if (! $questions->has($submittedKey)) {
                $errors[(string) $submittedKey] = 'unknown question';
            }
        }

        foreach ($questions as $key => $question) {
            $provided = array_key_exists($key, $answers);
            $value = $answers[$key] ?? null;
            $isEmpty = $value === null || $value === '' || (is_array($value) && $value === []);

            if ($question->required && $isEmpty) {
                $errors[$key] = 'this question is required';

                continue;
            }

            if (! $provided || $isEmpty) {
                continue; // optional and unanswered
            }

            try {
                foreach ($this->specFor($question, $value) as $spec) {
                    $specs[] = $spec;
                }
            } catch (\DomainException $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        if ($errors !== []) {
            throw new ResponseValidationException($errors);
        }

        return $specs;
    }

    /**
     * @return list<array{question_id: int, option_id: int|null, numeric_value: int|null, boolean_value: bool|null, text_value: string|null}>
     */
    private function specFor(SurveyQuestion $question, mixed $value): array
    {
        $type = $question->type;
        $base = ['question_id' => $question->id, 'option_id' => null, 'numeric_value' => null, 'boolean_value' => null, 'text_value' => null];

        if ($type->usesNumericScale()) {
            [$min, $max] = $this->scaleBounds($question);
            if (! is_numeric($value) || (string) (int) $value !== (string) $value) {
                throw new \DomainException('a numeric answer is required');
            }
            $int = (int) $value;
            if ($int < $min || $int > $max) {
                throw new \DomainException("value must be between {$min} and {$max}");
            }

            return [[...$base, 'numeric_value' => $int]];
        }

        if ($type->usesBoolean()) {
            $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($bool === null) {
                throw new \DomainException('a yes/no answer is required');
            }

            return [[...$base, 'boolean_value' => $bool]];
        }

        if ($type->usesText()) {
            if (! is_string($value)) {
                throw new \DomainException('a text answer is required');
            }
            $text = trim($value);
            $max = (int) ($question->validation_config['max_length']
                ?? ($type === QuestionType::LongText ? self::DEFAULT_LONG_TEXT_MAX : self::DEFAULT_SHORT_TEXT_MAX));
            if (mb_strlen($text) > $max) {
                throw new \DomainException("answer must be at most {$max} characters");
            }

            return [[...$base, 'text_value' => $text]];
        }

        // Choice types (single_choice, dropdown, multiple_choice).
        $optionsByKey = $question->options->keyBy('option_key');

        if ($type->allowsMultiple()) {
            if (! is_array($value)) {
                throw new \DomainException('one or more selections are required');
            }
            $selected = array_values(array_unique(array_map('strval', $value)));
            $specs = [];
            foreach ($selected as $optionKey) {
                $option = $optionsByKey->get($optionKey);
                if ($option === null) {
                    throw new \DomainException('an invalid option was selected');
                }
                $specs[] = [...$base, 'option_id' => $option->id];
            }

            $config = $question->validation_config ?? [];
            $count = count($specs);
            if (isset($config['min_select']) && $count < (int) $config['min_select']) {
                throw new \DomainException('too few options selected');
            }
            if (isset($config['max_select']) && $count > (int) $config['max_select']) {
                throw new \DomainException('too many options selected');
            }

            return $specs;
        }

        // Single choice / dropdown: exactly one option key.
        if (is_array($value)) {
            throw new \DomainException('only one option may be selected');
        }
        $option = $optionsByKey->get((string) $value);
        if ($option === null) {
            throw new \DomainException('an invalid option was selected');
        }

        return [[...$base, 'option_id' => $option->id]];
    }

    /** @return array{0: int, 1: int} */
    private function scaleBounds(SurveyQuestion $question): array
    {
        if ($question->type === QuestionType::Nps) {
            return [0, 10];
        }

        $config = $question->scoring_config ?? [];

        return [(int) ($config['scale_min'] ?? 1), (int) ($config['scale_max'] ?? 5)];
    }
}
