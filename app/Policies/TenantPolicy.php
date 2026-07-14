<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

class TenantPolicy
{
    use ChecksTenantScope;

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->can(Permissions::TENANT_VIEW) && $this->inCurrentTenant($tenant);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->can(Permissions::TENANT_UPDATE) && $this->inCurrentTenant($tenant);
    }
}
