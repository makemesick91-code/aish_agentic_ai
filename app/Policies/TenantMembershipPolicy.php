<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\TenantMembership;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

class TenantMembershipPolicy
{
    use ChecksTenantScope;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::USERS_VIEW);
    }

    public function view(User $user, TenantMembership $membership): bool
    {
        return $user->can(Permissions::USERS_VIEW) && $this->inCurrentTenant($membership);
    }

    public function manage(User $user, TenantMembership $membership): bool
    {
        return $user->can(Permissions::USERS_MANAGE_MEMBERSHIPS) && $this->inCurrentTenant($membership);
    }

    public function assignRole(User $user, TenantMembership $membership): bool
    {
        return $user->can(Permissions::ROLES_ASSIGN) && $this->inCurrentTenant($membership);
    }
}
