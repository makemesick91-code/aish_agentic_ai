<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Audit\AuditRecorder;
use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Services\Tenancy\Exceptions\LastOwnerException;
use App\Tenancy\TenantScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Membership lifecycle with the tenant's cardinal invariant: a tenant must always retain
 * at least one active Business Owner. Suspend/revoke/role-change that would remove the
 * last active owner is refused. All changes are audited and role assignment is
 * tenant-scoped (rule 30).
 */
final class MembershipService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function suspend(TenantMembership $membership, ?int $actorId = null): void
    {
        DB::transaction(function () use ($membership, $actorId): void {
            $this->lock($membership);
            $this->guardNotLastOwner($membership);

            $membership->forceFill([
                'status' => MembershipStatus::Suspended,
                'suspended_at' => now(),
            ])->save();

            $this->audit->record('tenant.membership.suspended', [
                'tenant_id' => $membership->tenant_id,
                'actor_id' => $actorId,
                'subject' => $membership,
            ]);
        });
    }

    public function reactivate(TenantMembership $membership, ?int $actorId = null): void
    {
        $membership->forceFill([
            'status' => MembershipStatus::Active,
            'suspended_at' => null,
        ])->save();

        $this->audit->record('tenant.membership.reactivated', [
            'tenant_id' => $membership->tenant_id,
            'actor_id' => $actorId,
            'subject' => $membership,
        ]);
    }

    public function revoke(TenantMembership $membership, ?int $actorId = null): void
    {
        DB::transaction(function () use ($membership, $actorId): void {
            $this->lock($membership);
            $this->guardNotLastOwner($membership);

            $membership->forceFill([
                'status' => MembershipStatus::Revoked,
                'revoked_at' => now(),
            ])->save();

            // Strip tenant-scoped roles so no stale permission survives revocation.
            $this->registrar->setPermissionsTeamId($membership->tenant_id);
            $membership->user?->syncRoles([]);
            $this->registrar->forgetCachedPermissions();

            $this->audit->record('tenant.membership.revoked', [
                'tenant_id' => $membership->tenant_id,
                'actor_id' => $actorId,
                'subject' => $membership,
            ]);
        });
    }

    /** Replace the membership's role, protecting the last active owner from demotion. */
    public function setRole(TenantMembership $membership, string $role, ?int $actorId = null): void
    {
        DB::transaction(function () use ($membership, $role, $actorId): void {
            $this->lock($membership);

            if ($role !== Roles::BUSINESS_OWNER) {
                $this->guardNotLastOwner($membership);
            }

            $this->registrar->setPermissionsTeamId($membership->tenant_id);
            $membership->user?->syncRoles([$role]);
            $this->registrar->forgetCachedPermissions();

            $this->audit->record('tenant.role.assigned', [
                'tenant_id' => $membership->tenant_id,
                'actor_id' => $actorId,
                'subject' => $membership,
                'metadata' => ['role' => $role],
            ]);
        });
    }

    public function activeOwnerCount(Tenant $tenant): int
    {
        return $this->activeOwners($tenant)->count();
    }

    /**
     * @return Collection<int, TenantMembership>
     */
    private function activeOwners(Tenant $tenant): Collection
    {
        $this->registrar->setPermissionsTeamId($tenant->id);

        return TenantMembership::withoutGlobalScope(TenantScope::class)
            ->with('user')
            ->where('tenant_id', $tenant->id)
            ->where('status', MembershipStatus::Active->value)
            ->get()
            ->filter(fn (TenantMembership $m): bool => $m->user?->hasRole(Roles::BUSINESS_OWNER) ?? false)
            ->values();
    }

    private function guardNotLastOwner(TenantMembership $membership): void
    {
        $tenant = Tenant::find($membership->tenant_id);

        if ($tenant === null) {
            return;
        }

        $owners = $this->activeOwners($tenant);

        if ($owners->count() <= 1 && $owners->contains(fn (TenantMembership $m): bool => $m->id === $membership->id)) {
            throw LastOwnerException::make();
        }
    }

    private function lock(TenantMembership $membership): void
    {
        // Serialise concurrent lifecycle changes on the same membership.
        TenantMembership::withoutGlobalScope(TenantScope::class)
            ->whereKey($membership->id)
            ->lockForUpdate()
            ->first();
    }
}
