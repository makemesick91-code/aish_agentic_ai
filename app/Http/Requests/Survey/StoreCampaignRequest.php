<?php

declare(strict_types=1);

namespace App\Http\Requests\Survey;

use App\Models\SurveyCampaign;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating a campaign. `survey_id`/`survey_version_id` are resolved and
 * ownership/published checks are enforced in the service; `branch_id` must belong to the tenant
 * (rule 32; Step 7 §16, §27).
 */
final class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SurveyCampaign::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->tenantIdOrNull();

        return [
            'survey_ulid' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('tenant_id', $tenantId)],
            'invitation_expiry_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }
}
