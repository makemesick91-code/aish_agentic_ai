<?php

declare(strict_types=1);

namespace App\Subscriptions;

use Carbon\CarbonInterface;

/**
 * The structured result of an entitlement evaluation. Every field is explicit so callers
 * never re-derive plan logic; `allowed` plus `reasonCode` are the contract (rule 31 §9.6).
 */
final class EntitlementDecision
{
    public function __construct(
        public readonly bool $allowed,
        public readonly string $featureKey,
        public readonly bool|int|string|null $effectiveValue,
        public readonly ?string $sourcePlanCode,
        public readonly ?int $sourcePlanVersion,
        public readonly ?string $subscriptionStatus,
        public readonly string $reasonCode,
        public readonly string $evaluatedAt,
    ) {}

    public static function deny(
        string $featureKey,
        string $reasonCode,
        ?string $subscriptionStatus,
        CarbonInterface $at,
        bool|int|string|null $effectiveValue = null,
    ): self {
        return new self(
            allowed: false,
            featureKey: $featureKey,
            effectiveValue: $effectiveValue,
            sourcePlanCode: null,
            sourcePlanVersion: null,
            subscriptionStatus: $subscriptionStatus,
            reasonCode: $reasonCode,
            evaluatedAt: $at->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'feature_key' => $this->featureKey,
            'effective_value' => $this->effectiveValue,
            'source_plan_code' => $this->sourcePlanCode,
            'source_plan_version' => $this->sourcePlanVersion,
            'subscription_status' => $this->subscriptionStatus,
            'reason_code' => $this->reasonCode,
            'evaluated_at' => $this->evaluatedAt,
        ];
    }
}
