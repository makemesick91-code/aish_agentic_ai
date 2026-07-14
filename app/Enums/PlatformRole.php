<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Platform (operator) roles. These are the SEPARATE platform authorization plane — entirely
 * distinct from tenant-scoped Spatie roles. A platform role grants NO tenant-data access, and
 * a tenant role grants NO platform access (rule 31 §10.1).
 */
enum PlatformRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Support = 'support';
    case Finance = 'finance';
    case Auditor = 'auditor';
    case ReadOnly = 'read_only';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Platform Super Admin',
            self::Admin => 'Platform Admin',
            self::Support => 'Platform Support',
            self::Finance => 'Platform Finance',
            self::Auditor => 'Platform Auditor',
            self::ReadOnly => 'Platform Read-only',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $r): string => $r->value, self::cases());
    }
}
