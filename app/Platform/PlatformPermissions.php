<?php

declare(strict_types=1);

namespace App\Platform;

/**
 * Stable, machine-readable platform permission names. These are the `platform.*` namespace
 * and are NEVER registered as tenant (Spatie) permissions — the two planes never share a
 * vocabulary (rule 31 §10.3).
 */
final class PlatformPermissions
{
    public const DASHBOARD_VIEW = 'platform.dashboard.view';

    public const TENANTS_VIEW = 'platform.tenants.view';

    public const TENANTS_MANAGE_STATUS = 'platform.tenants.manage-status';

    public const PLANS_VIEW = 'platform.plans.view';

    public const PLANS_MANAGE = 'platform.plans.manage';

    public const SUBSCRIPTIONS_VIEW = 'platform.subscriptions.view';

    public const SUBSCRIPTIONS_MANAGE = 'platform.subscriptions.manage';

    public const NOTIFICATIONS_VIEW_HEALTH = 'platform.notifications.view-health';

    public const SUPPORT_NOTES_VIEW = 'platform.support-notes.view';

    public const SUPPORT_NOTES_MANAGE = 'platform.support-notes.manage';

    public const AUDIT_VIEW = 'platform.audit.view';

    public const USERS_MANAGE = 'platform.users.manage';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::DASHBOARD_VIEW,
            self::TENANTS_VIEW,
            self::TENANTS_MANAGE_STATUS,
            self::PLANS_VIEW,
            self::PLANS_MANAGE,
            self::SUBSCRIPTIONS_VIEW,
            self::SUBSCRIPTIONS_MANAGE,
            self::NOTIFICATIONS_VIEW_HEALTH,
            self::SUPPORT_NOTES_VIEW,
            self::SUPPORT_NOTES_MANAGE,
            self::AUDIT_VIEW,
            self::USERS_MANAGE,
        ];
    }
}
