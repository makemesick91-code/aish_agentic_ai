<?php

declare(strict_types=1);

namespace App\Http\Requests\Feedback;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an attachment upload's shape and size. The authoritative content-based MIME allowlist is
 * enforced in the attachment service, not here (rule 33; Step 8 §14).
 */
final class StoreFeedbackAttachmentRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240'],
        ];
    }
}
