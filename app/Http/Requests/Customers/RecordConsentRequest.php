<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Enums\CustomerConsentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Consent capture always records WHICH consent text version the customer saw, so the history stays
 * explainable when the wording later changes (rule 36, rule 32; ADR 0064).
 */
final class RecordConsentRequest extends FormRequest
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
            'consent_type' => ['required', Rule::enum(CustomerConsentType::class)],
            // Explicit boolean: there is no default and no pre-checked semantics (rule 32).
            'accepted' => ['required', 'boolean'],
            'consent_text_version' => ['required', 'string', 'max:40'],
            'channel' => ['nullable', 'string', 'max:20'],
        ];
    }
}
