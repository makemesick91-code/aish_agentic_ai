<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use App\Enums\SurveyMode;
use App\Models\Survey;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Tenant-aware validation for creating a survey. `branch_id` must belong to the current tenant
 * (blocks cross-tenant FormRequest injection); `tenant_id` is never accepted from input — it is
 * stamped from context (rule 32; Step 7 §27, §28).
 */
final class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Survey::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->tenantIdOrNull();

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('tenant_id', $tenantId)],
            'title' => ['nullable', 'string', 'max:255'],
            'introduction' => ['nullable', 'string', 'max:2000'],
            'completion_message' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', Rule::enum(SurveyMode::class)],
            'locale' => ['nullable', 'string', 'max:10'],
        ];
    }
}
