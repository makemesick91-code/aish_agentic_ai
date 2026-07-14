<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Authorization\Roles;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantInvitation>
 */
class TenantInvitationFactory extends Factory
{
    protected $model = TenantInvitation::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'email' => fake()->unique()->safeEmail(),
            'role' => Roles::READ_ONLY,
            'branch_id' => null,
            'all_branches' => true,
            'token_hash' => hash('sha256', Str::random(64)),
            'invited_by' => null,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['accepted_at' => now()]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['revoked_at' => now()]);
    }
}
