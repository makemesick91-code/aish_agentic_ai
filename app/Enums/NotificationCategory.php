<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Preference categories a tenant user can tune. Categories group notification types so a
 * user can silence a class of non-critical messages without disabling critical security
 * notifications (rule 31).
 */
enum NotificationCategory: string
{
    case Membership = 'membership';
    case Tenant = 'tenant';
    case Subscription = 'subscription';
    case Security = 'security';

    public function label(): string
    {
        return match ($this) {
            self::Membership => 'Membership & roles',
            self::Tenant => 'Workspace status',
            self::Subscription => 'Subscription',
            self::Security => 'Security alerts',
        };
    }

    /** @return list<self> */
    public static function tunable(): array
    {
        // Security is deliberately excluded: its notifications are mandatory (critical).
        return [self::Membership, self::Tenant, self::Subscription];
    }
}
