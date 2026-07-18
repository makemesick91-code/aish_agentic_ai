<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of source identity a customer can be matched on. `Email` and `Phone` are PII and are
 * stored only as a keyed tenant-bound hash; `ExternalRef` is opaque and may retain its normalized
 * value (rule 36; ADR 0071).
 */
enum CustomerIdentityType: string
{
    case Email = 'email';
    case Phone = 'phone';
    case ExternalRef = 'external_ref';

    /** PII types must never persist their plaintext on the identity row (ADR 0071). */
    public function isPii(): bool
    {
        return in_array($this, [self::Email, self::Phone], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Phone => 'Phone',
            self::ExternalRef => 'External reference',
        };
    }
}
