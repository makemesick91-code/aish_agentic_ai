<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\Branch;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

class BranchPolicy
{
    use ChecksTenantScope;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::BRANCHES_VIEW);
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can(Permissions::BRANCHES_VIEW) && $this->inCurrentTenant($branch);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::BRANCHES_CREATE);
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can(Permissions::BRANCHES_UPDATE) && $this->inCurrentTenant($branch);
    }

    public function deactivate(User $user, Branch $branch): bool
    {
        return $user->can(Permissions::BRANCHES_DEACTIVATE) && $this->inCurrentTenant($branch);
    }
}
