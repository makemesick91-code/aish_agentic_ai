<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A merge always requires a stated reason — the ledger must explain WHY identity was reshaped, not
 * only that it was (rule 36; ADR 0072). Authorization is asserted in the controller against the
 * resolved models, because it depends on reaching BOTH customers.
 */
final class MergeCustomerRequest extends FormRequest
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
            // A ULID, never an internal id — a sequential id would be guessable (rule 36).
            'merged_customer' => ['required', 'string', 'size:26'],
            'reason' => ['required', 'string', 'min:8', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.min' => 'Please record a meaningful reason for this merge.',
        ];
    }
}
