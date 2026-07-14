<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SurveyInvitation>
 */
class SurveyInvitationFactory extends Factory
{
    protected $model = SurveyInvitation::class;

    public function definition(): array
    {
        $campaign = SurveyCampaign::factory()->active();

        return [
            'tenant_id' => $this->tenantId(),
            'branch_id' => null,
            'campaign_id' => $campaign,
            'survey_version_id' => fn (array $attributes) => SurveyCampaign::withoutGlobalScopes()
                ->findOrFail($attributes['campaign_id'])->survey_version_id,
            // Random plaintext is discarded; only its SHA-256 hash is stored (never logged).
            'token_hash' => hash('sha256', Str::random(64)),
            'recipient_email' => fake()->optional()->safeEmail(),
            'status' => InvitationStatus::Created,
            'idempotency_key' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Sent]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Completed, 'completed_at' => now()]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Revoked, 'revoked_at' => now()]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Sent, 'expires_at' => now()->subDay()]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
