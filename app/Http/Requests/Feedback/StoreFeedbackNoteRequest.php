<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use App\Models\FeedbackNote;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an internal note. The body is untrusted free text: length-bounded here and escaped on
 * output; it is never logged or audited (rule 33; Step 8 §13).
 */
final class StoreFeedbackNoteRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:'.FeedbackNote::MAX_BODY_LENGTH],
        ];
    }
}
