<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\AuditLog;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

class AuditLogPolicy
{
    use ChecksTenantScope;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::AUDIT_VIEW);
    }

    public function view(User $user, AuditLog $log): bool
    {
        // A tenant user can only view their own tenant's audit records.
        return $user->can(Permissions::AUDIT_VIEW) && $this->inCurrentTenant($log);
    }
}
