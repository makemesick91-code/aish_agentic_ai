<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use App\Enums\FeedbackStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a feedback status transition request. Authorization is enforced by the controller via
 * FeedbackItemPolicy (rule 33; Step 8 §10, §21).
 */
final class UpdateFeedbackStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FeedbackStatus::class)],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
