<?php

declare(strict_types=1);

namespace App\Authorization;

/**
 * Stable, machine-readable foundation permission names. Business-module permissions are
 * added by their modules in later steps; this catalog is foundation-only (rule 30).
 */
final class Permissions
{
    public const TENANT_VIEW = 'tenant.view';

    public const TENANT_UPDATE = 'tenant.update';

    public const BRANCHES_VIEW = 'branches.view';

    public const BRANCHES_CREATE = 'branches.create';

    public const BRANCHES_UPDATE = 'branches.update';

    public const BRANCHES_DEACTIVATE = 'branches.deactivate';

    public const USERS_VIEW = 'users.view';

    public const USERS_INVITE = 'users.invite';

    public const USERS_MANAGE_MEMBERSHIPS = 'users.manage-memberships';

    public const ROLES_VIEW = 'roles.view';

    public const ROLES_ASSIGN = 'roles.assign';

    public const ROLES_MANAGE_FOUNDATION = 'roles.manage-foundation';

    public const AUDIT_VIEW = 'audit.view';

    public const CONTEXT_SWITCH_TENANT = 'context.switch-tenant';

    public const CONTEXT_SWITCH_BRANCH = 'context.switch-branch';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::TENANT_VIEW,
            self::TENANT_UPDATE,
            self::BRANCHES_VIEW,
            self::BRANCHES_CREATE,
            self::BRANCHES_UPDATE,
            self::BRANCHES_DEACTIVATE,
            self::USERS_VIEW,
            self::USERS_INVITE,
            self::USERS_MANAGE_MEMBERSHIPS,
            self::ROLES_VIEW,
            self::ROLES_ASSIGN,
            self::ROLES_MANAGE_FOUNDATION,
            self::AUDIT_VIEW,
            self::CONTEXT_SWITCH_TENANT,
            self::CONTEXT_SWITCH_BRANCH,
        ];
    }
}
