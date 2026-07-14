<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Structural + payload-size validation for a PUBLIC survey submission. Deep per-answer
 * validation (types, ranges, option ownership) is done by ResponseValidator against the exact
 * version. This request bounds the payload to prevent amplification; it authorizes openly
 * because token/link resolution is the access control (rule 32; Step 7 §18).
 */
final class SubmitSurveyResponseRequest extends FormRequest
{
    /** Hard cap on submitted questions to bound the payload. */
    private const MAX_ANSWERS = 200;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'max:'.self::MAX_ANSWERS],
        ];
    }

    /** @return array<string, mixed> */
    public function answers(): array
    {
        $answers = $this->input('answers', []);

        return is_array($answers) ? $answers : [];
    }
}
