<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

class TenantInvitationPolicy
{
    use ChecksTenantScope;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::USERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::USERS_INVITE);
    }

    public function revoke(User $user, TenantInvitation $invitation): bool
    {
        return $user->can(Permissions::USERS_INVITE) && $this->inCurrentTenant($invitation);
    }
}
