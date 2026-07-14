<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformRoleAssignment>
 */
class PlatformRoleAssignmentFactory extends Factory
{
    protected $model = PlatformRoleAssignment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'role' => PlatformRole::Admin,
        ];
    }

    public function role(PlatformRole $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }
}
