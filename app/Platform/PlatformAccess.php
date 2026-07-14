<?php

declare(strict_types=1);

namespace App\Platform;

use App\Enums\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\User;

/**
 * Resolves a user's effective platform permissions from their platform role assignments. This
 * is the sole source of platform authorization — there is NO global super-admin bypass and no
 * Gate::before that grants platform users tenant access (rule 31 §10.1).
 */
final class PlatformAccess
{
    /** @return list<PlatformRole> */
    public function roles(User $user): array
    {
        // `role` is cast to PlatformRole on the model.
        return $user->platformRoleAssignments
            ->map(fn (PlatformRoleAssignment $assignment): PlatformRole => $assignment->role)
            ->values()
            ->all();
    }

    public function hasAnyRole(User $user): bool
    {
        return $this->roles($user) !== [];
    }

    /** @return list<string> */
    public function permissionsFor(User $user): array
    {
        $permissions = [];

        foreach ($this->roles($user) as $role) {
            $permissions = array_merge($permissions, PlatformRoles::permissionsFor($role));
        }

        return array_values(array_unique($permissions));
    }

    public function has(User $user, string $permission): bool
    {
        return in_array($permission, $this->permissionsFor($user), true);
    }

    public function isSuperAdmin(User $user): bool
    {
        return in_array(PlatformRole::SuperAdmin, $this->roles($user), true);
    }
}
