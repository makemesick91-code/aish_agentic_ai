<?php

declare(strict_types=1);

namespace App\Platform;

use App\Audit\AuditRecorder;
use App\Enums\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\User;
use App\Platform\Exceptions\LastPlatformSuperAdminException;
use App\Platform\Exceptions\PlatformSelfEscalationException;
use Illuminate\Support\Facades\DB;

/**
 * Assigns and removes platform roles with the platform invariants enforced regardless of
 * caller: no self-modification, only a Super Admin may grant Super Admin, and the last Super
 * Admin can never be removed (rule 31 §10.3).
 */
final class PlatformRoleService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly PlatformAccess $access,
    ) {}

    public function assign(User $target, PlatformRole $role, ?User $actor = null): PlatformRoleAssignment
    {
        if ($actor !== null && (int) $actor->id === (int) $target->id) {
            throw PlatformSelfEscalationException::selfModification();
        }

        if ($role === PlatformRole::SuperAdmin && $actor !== null && ! $this->access->isSuperAdmin($actor)) {
            throw PlatformSelfEscalationException::cannotGrantSuperAdmin();
        }

        return DB::transaction(function () use ($target, $role, $actor): PlatformRoleAssignment {
            $assignment = PlatformRoleAssignment::firstOrCreate(
                ['user_id' => $target->id, 'role' => $role->value],
                ['granted_by' => $actor?->id],
            );

            $this->audit->record('platform.role.assigned', [
                'tenant_id' => null,
                'actor_id' => $actor?->id,
                'subject' => $assignment,
                'metadata' => ['target_user_id' => (int) $target->id, 'role' => $role->value],
            ]);

            return $assignment;
        });
    }

    public function remove(User $target, PlatformRole $role, ?User $actor = null): void
    {
        if ($actor !== null && (int) $actor->id === (int) $target->id) {
            throw PlatformSelfEscalationException::selfModification();
        }

        DB::transaction(function () use ($target, $role, $actor): void {
            if ($role === PlatformRole::SuperAdmin) {
                $this->guardNotLastSuperAdmin($target);
            }

            $assignment = PlatformRoleAssignment::where('user_id', $target->id)
                ->where('role', $role->value)
                ->lockForUpdate()
                ->first();

            if ($assignment === null) {
                return;
            }

            $assignment->delete();

            $this->audit->record('platform.role.removed', [
                'tenant_id' => null,
                'actor_id' => $actor?->id,
                'metadata' => ['target_user_id' => (int) $target->id, 'role' => $role->value],
            ]);
        });
    }

    private function guardNotLastSuperAdmin(User $target): void
    {
        // Lock the actual super-admin rows (FOR UPDATE on a row set — never on an aggregate,
        // which PostgreSQL rejects) to serialise concurrent removals, then count in PHP.
        $superAdmins = PlatformRoleAssignment::where('role', PlatformRole::SuperAdmin->value)
            ->lockForUpdate()
            ->get();

        $targetIsSuperAdmin = $superAdmins->contains(
            fn (PlatformRoleAssignment $assignment): bool => (int) $assignment->user_id === (int) $target->id,
        );

        if ($superAdmins->count() <= 1 && $targetIsSuperAdmin) {
            throw LastPlatformSuperAdminException::make();
        }
    }
}
