<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an export request's filters. Filter values are re-sanitized against an allowlist in the
 * export service; content inclusion additionally requires the content-view permission (enforced in
 * the controller) (rule 33; Step 8 §18).
 */
final class RequestFeedbackExportRequest extends FormRequest
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
            'include_content' => ['sometimes', 'boolean'],
            'statuses' => ['sometimes', 'array'],
            'statuses.*' => ['string'],
            'branch_id' => ['nullable', 'integer'],
            'survey_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'survey_version_id' => ['nullable', 'integer'],
            'assignee_id' => ['nullable', 'integer'],
            'tag_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}
