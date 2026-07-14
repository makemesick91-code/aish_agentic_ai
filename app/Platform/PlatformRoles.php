<?php

declare(strict_types=1);

namespace App\Platform;

use App\Enums\PlatformRole;

/**
 * The permission set for each platform role. Least privilege is mandatory: Support cannot
 * manage plans or subscriptions, Finance cannot touch support notes or platform users,
 * Auditor and Read-only cannot mutate anything, and only Super Admin manages platform users
 * (rule 31 §10.3).
 */
final class PlatformRoles
{
    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            PlatformRole::SuperAdmin->value => PlatformPermissions::all(),

            PlatformRole::Admin->value => [
                PlatformPermissions::DASHBOARD_VIEW,
                PlatformPermissions::TENANTS_VIEW,
                PlatformPermissions::TENANTS_MANAGE_STATUS,
                PlatformPermissions::PLANS_VIEW,
                PlatformPermissions::PLANS_MANAGE,
                PlatformPermissions::SUBSCRIPTIONS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_MANAGE,
                PlatformPermissions::NOTIFICATIONS_VIEW_HEALTH,
                PlatformPermissions::SUPPORT_NOTES_VIEW,
                PlatformPermissions::SUPPORT_NOTES_MANAGE,
                PlatformPermissions::AUDIT_VIEW,
                // NOT users.manage — only Super Admin manages platform users/roles.
            ],

            PlatformRole::Support->value => [
                PlatformPermissions::DASHBOARD_VIEW,
                PlatformPermissions::TENANTS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_VIEW,
                PlatformPermissions::NOTIFICATIONS_VIEW_HEALTH,
                PlatformPermissions::SUPPORT_NOTES_VIEW,
                PlatformPermissions::SUPPORT_NOTES_MANAGE,
                // NOT plans.manage / subscriptions.manage (rule 31 §10.3).
            ],

            PlatformRole::Finance->value => [
                PlatformPermissions::DASHBOARD_VIEW,
                PlatformPermissions::TENANTS_VIEW,
                PlatformPermissions::PLANS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_MANAGE,
                // NO tenant business data, NO support notes, NO users.
            ],

            PlatformRole::Auditor->value => [
                PlatformPermissions::DASHBOARD_VIEW,
                PlatformPermissions::TENANTS_VIEW,
                PlatformPermissions::PLANS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_VIEW,
                PlatformPermissions::NOTIFICATIONS_VIEW_HEALTH,
                PlatformPermissions::SUPPORT_NOTES_VIEW,
                PlatformPermissions::AUDIT_VIEW,
                // Read-only: no manage permissions.
            ],

            PlatformRole::ReadOnly->value => [
                PlatformPermissions::DASHBOARD_VIEW,
                PlatformPermissions::TENANTS_VIEW,
                PlatformPermissions::PLANS_VIEW,
                PlatformPermissions::SUBSCRIPTIONS_VIEW,
            ],
        ];
    }

    /** @return list<string> */
    public static function permissionsFor(PlatformRole $role): array
    {
        return self::definitions()[$role->value] ?? [];
    }
}
