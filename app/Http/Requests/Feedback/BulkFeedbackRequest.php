<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use App\Enums\FeedbackStatus;
use App\Feedback\Bulk\FeedbackBulkService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a bounded bulk operation. The batch is capped and the per-item authorization and
 * all-or-nothing validation happen in the bulk service (rule 33; Step 8 §17).
 */
final class BulkFeedbackRequest extends FormRequest
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
            'action' => ['required', Rule::in(['status', 'assign', 'attach-tag', 'remove-tag'])],
            'ids' => ['required', 'array', 'min:1', 'max:'.FeedbackBulkService::MAX_BATCH],
            'ids.*' => ['integer'],
            'status' => ['nullable', 'required_if:action,status', Rule::enum(FeedbackStatus::class)],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'tag_id' => ['nullable', 'required_if:action,attach-tag', 'required_if:action,remove-tag', 'integer', 'exists:feedback_tags,id'],
        ];
    }
}
