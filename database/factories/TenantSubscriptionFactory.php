<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSubscription>
 */
class TenantSubscriptionFactory extends Factory
{
    protected $model = TenantSubscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Trialing,
            'started_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'source' => 'platform',
        ];
    }

    public function active(?int $periodDays = 30): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => $periodDays === null ? null : now()->addDays($periodDays),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Expired,
            'trial_ends_at' => now()->subDay(),
            'ended_at' => now(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::Suspended]);
    }

    public function trialExpiredButUnreconciled(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Trialing,
            'trial_ends_at' => now()->subDay(),
        ]);
    }
}
