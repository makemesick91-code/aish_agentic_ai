<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates attaching an existing tag to a feedback item. Tenant/cross-tenant safety is enforced by
 * the tag service and the pivot's composite FK (rule 33; Step 8 §12).
 */
final class AttachFeedbackTagRequest extends FormRequest
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
            'tag_id' => ['required', 'integer', 'exists:feedback_tags,id'],
        ];
    }
}
