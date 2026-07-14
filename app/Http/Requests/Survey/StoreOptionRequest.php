<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $survey = $this->route('survey');

        return $survey instanceof Survey && ($this->user()?->can('update', $survey) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'option_key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'display_order' => ['required', 'integer', 'min:1', 'max:1000'],
            'score' => ['nullable', 'integer'],
        ];
    }
}
