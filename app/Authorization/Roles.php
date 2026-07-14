<?php

declare(strict_types=1);

namespace App\Authorization;

/**
 * Minimum tenant roles for the SaaS core foundation and their permission sets. Business
 * roles (CX Manager, Reputation Manager, etc.) are canonical future roles and MUST NOT
 * receive permissions to modules that do not exist yet (rule 30, rule 16).
 */
final class Roles
{
    public const BUSINESS_OWNER = 'Business Owner';

    public const CORPORATE_ADMIN = 'Corporate Admin';

    public const BRANCH_MANAGER = 'Branch Manager';

    public const READ_ONLY = 'Read-only User';

    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            // Full foundation authority, including structural role management.
            self::BUSINESS_OWNER => Permissions::all(),

            // Everything except structural (foundation) role management.
            self::CORPORATE_ADMIN => [
                Permissions::TENANT_VIEW,
                Permissions::TENANT_UPDATE,
                Permissions::BRANCHES_VIEW,
                Permissions::BRANCHES_CREATE,
                Permissions::BRANCHES_UPDATE,
                Permissions::BRANCHES_DEACTIVATE,
                Permissions::USERS_VIEW,
                Permissions::USERS_INVITE,
                Permissions::USERS_MANAGE_MEMBERSHIPS,
                Permissions::ROLES_VIEW,
                Permissions::ROLES_ASSIGN,
                Permissions::AUDIT_VIEW,
                Permissions::CONTEXT_SWITCH_TENANT,
                Permissions::CONTEXT_SWITCH_BRANCH,
                // Full survey authoring, distribution, and results.
                ...Permissions::surveyPermissions(),
            ],

            // Branch-scoped operational view; sees only its granted branch(es).
            self::BRANCH_MANAGER => [
                Permissions::TENANT_VIEW,
                Permissions::BRANCHES_VIEW,
                Permissions::USERS_VIEW,
                Permissions::AUDIT_VIEW,
                Permissions::CONTEXT_SWITCH_BRANCH,
                // Branch-scoped survey visibility only (no authoring).
                ...Permissions::SURVEY_BRANCH_VIEW,
            ],

            // Read-only foundation visibility.
            self::READ_ONLY => [
                Permissions::TENANT_VIEW,
                Permissions::BRANCHES_VIEW,
                Permissions::USERS_VIEW,
                Permissions::CONTEXT_SWITCH_BRANCH,
                ...Permissions::SURVEY_READ_ONLY,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::definitions());
    }
}
