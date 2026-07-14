<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Enums\SubscriptionStatus;

/**
 * Allowed subscription status transitions. Invalid transitions are rejected: e.g. a
 * cancelled subscription can never silently become trialing, and reaching `active` from
 * `cancelled`/`expired` is only via an explicit reactivation (rule 31 §9.4).
 */
final class SubscriptionStateMachine
{
    /** @return list<SubscriptionStatus> */
    public static function allowedNext(SubscriptionStatus $from): array
    {
        return match ($from) {
            SubscriptionStatus::Trialing => [
                SubscriptionStatus::Active,
                SubscriptionStatus::Grace,
                SubscriptionStatus::Suspended,
                SubscriptionStatus::Cancelled,
                SubscriptionStatus::Expired,
            ],
            SubscriptionStatus::Active => [
                SubscriptionStatus::Grace,
                SubscriptionStatus::Suspended,
                SubscriptionStatus::Cancelled,
                SubscriptionStatus::Expired,
            ],
            SubscriptionStatus::Grace => [
                SubscriptionStatus::Active,
                SubscriptionStatus::Suspended,
                SubscriptionStatus::Cancelled,
                SubscriptionStatus::Expired,
            ],
            SubscriptionStatus::Suspended => [
                SubscriptionStatus::Active,
                SubscriptionStatus::Cancelled,
                SubscriptionStatus::Expired,
            ],
            // Reactivation only — never back to trialing.
            SubscriptionStatus::Cancelled => [SubscriptionStatus::Active],
            SubscriptionStatus::Expired => [SubscriptionStatus::Active],
        };
    }

    public static function canTransition(SubscriptionStatus $from, SubscriptionStatus $to): bool
    {
        return in_array($to, self::allowedNext($from), true);
    }
}
