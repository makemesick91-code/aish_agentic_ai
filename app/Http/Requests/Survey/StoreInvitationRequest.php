<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use App\Models\SurveyInvitation;
use Illuminate\Foundation\Http\FormRequest;

final class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SurveyInvitation::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'campaign_ulid' => ['required', 'string', 'max:40'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
            'deliver' => ['nullable', 'boolean'],
        ];
    }
}
