<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Enums\FeatureType;

/**
 * Stable, typed allowlist of entitlement keys. A key not in this registry fails closed at the
 * resolver. These are entitlement DEFINITIONS only — the business features they gate
 * (surveys, Google locations, AI analyses, …) are NOT implemented in this sprint and MUST NOT
 * be claimed as implemented (rule 27, rule 31 §9.3).
 *
 * Integer value -1 represents "unlimited" (an explicit sentinel; never a negative limit).
 */
final class EntitlementKeys
{
    public const UNLIMITED = -1;

    public const BRANCHES_MAX = 'branches.max';

    public const USERS_MAX = 'users.max';

    public const SURVEY_INVITATIONS_MONTHLY = 'survey_invitations.monthly';

    public const GOOGLE_LOCATIONS_MAX = 'google_locations.max';

    public const AI_ANALYSES_MONTHLY = 'ai_analyses.monthly';

    public const API_ENABLED = 'api.enabled';

    public const WEBHOOKS_ENABLED = 'webhooks.enabled';

    public const ADVANCED_ANALYTICS_ENABLED = 'advanced_analytics.enabled';

    /**
     * @return array<string, FeatureType>
     */
    public static function map(): array
    {
        return [
            self::BRANCHES_MAX => FeatureType::Integer,
            self::USERS_MAX => FeatureType::Integer,
            self::SURVEY_INVITATIONS_MONTHLY => FeatureType::Integer,
            self::GOOGLE_LOCATIONS_MAX => FeatureType::Integer,
            self::AI_ANALYSES_MONTHLY => FeatureType::Integer,
            self::API_ENABLED => FeatureType::Boolean,
            self::WEBHOOKS_ENABLED => FeatureType::Boolean,
            self::ADVANCED_ANALYTICS_ENABLED => FeatureType::Boolean,
        ];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::map());
    }

    public static function isKnown(string $key): bool
    {
        return array_key_exists($key, self::map());
    }

    public static function typeFor(string $key): ?FeatureType
    {
        return self::map()[$key] ?? null;
    }
}
