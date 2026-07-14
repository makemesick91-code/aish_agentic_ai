<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Tenancy\TenantScope;
use Spatie\Permission\PermissionRegistrar;

/**
 * Resolves a tenant's active owner users, so platform/subscription operations can notify the
 * right people. Roles are tenant-scoped (Spatie team = tenant_id), so the team is bound
 * before evaluating (rule 30, rule 31).
 */
final class TenantOwnerLocator
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /** @return list<User> */
    public function activeOwners(Tenant $tenant): array
    {
        $this->registrar->setPermissionsTeamId($tenant->id);

        return TenantMembership::withoutGlobalScope(TenantScope::class)
            ->with('user')
            ->where('tenant_id', $tenant->id)
            ->where('status', MembershipStatus::Active->value)
            ->get()
            ->filter(fn (TenantMembership $membership): bool => $membership->user?->hasRole(Roles::BUSINESS_OWNER) ?? false)
            ->map(fn (TenantMembership $membership): ?User => $membership->user)
            ->filter()
            ->values()
            ->all();
    }
}
