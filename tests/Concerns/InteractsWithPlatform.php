<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Enums\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\User;

/**
 * Test-only helper to create a global user holding a platform (operator) role. Platform roles
 * are the separate operator plane and carry no tenant access (rule 31 §10.1).
 */
trait InteractsWithPlatform
{
    protected function platformUser(PlatformRole $role): User
    {
        $user = User::factory()->create();
        PlatformRoleAssignment::factory()->role($role)->create(['user_id' => $user->id]);

        return $user->fresh();
    }
}
