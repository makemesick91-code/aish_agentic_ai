<?php

declare(strict_types=1);

namespace App\Subscriptions;

/**
 * Allowlist of usage meter keys. The skeleton implements exactly one internal verification
 * meter; business meters (survey invitations, AI analyses, …) are defined by their modules
 * later and MUST NOT be claimed as billing-grade financial reconciliation (rule 31 §9.7).
 */
final class MeterKeys
{
    /** The single internal meter used to prove the metering foundation end-to-end. */
    public const FOUNDATION_VERIFICATION = 'foundation.verification';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::FOUNDATION_VERIFICATION,
        ];
    }

    public static function isKnown(string $key): bool
    {
        return in_array($key, self::all(), true);
    }
}
