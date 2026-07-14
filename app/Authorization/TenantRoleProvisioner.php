<?php

declare(strict_types=1);

namespace App\Authorization;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the foundation roles/permissions for a tenant. Permissions are the same
 * (global) vocabulary across tenants; roles are tenant-scoped (Spatie "team" = tenant_id)
 * so a role in tenant A can never grant access in tenant B. Idempotent and deterministic
 * (rule 30; ADR 0013).
 */
final class TenantRoleProvisioner
{
    private const GUARD = 'web';

    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant): void {
            // Global permission vocabulary (team-agnostic).
            $this->registrar->setPermissionsTeamId(null);
            foreach (Permissions::all() as $name) {
                Permission::findOrCreate($name, self::GUARD);
            }

            // Tenant-scoped roles.
            $this->registrar->setPermissionsTeamId($tenant->id);
            foreach (Roles::definitions() as $roleName => $permissions) {
                $role = Role::findOrCreate($roleName, self::GUARD);
                $role->syncPermissions($permissions);
            }
        });

        $this->registrar->forgetCachedPermissions();
    }
}
