<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantMembership>
 */
class TenantMembershipFactory extends Factory
{
    protected $model = TenantMembership::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'status' => MembershipStatus::Active,
            'all_branches' => true,
            'accepted_at' => now(),
        ];
    }

    public function invited(): static
    {
        return $this->state(fn () => ['status' => MembershipStatus::Invited, 'accepted_at' => null]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => MembershipStatus::Suspended, 'suspended_at' => now()]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['status' => MembershipStatus::Revoked, 'revoked_at' => now()]);
    }

    public function branchScoped(): static
    {
        return $this->state(fn () => ['all_branches' => false]);
    }
}
