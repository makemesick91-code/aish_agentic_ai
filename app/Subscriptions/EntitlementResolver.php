<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Enums\FeatureType;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Tenancy\TenantScope;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * The single authoritative entitlement resolver. Controllers/modules MUST use this — never
 * their own plan checks. It fails closed: an unknown key, a missing subscription, a
 * non-granting status, or a feature the plan does not define all deny (rule 31 §9.6).
 */
final class EntitlementResolver
{
    public function resolve(
        Tenant $tenant,
        string $featureKey,
        ?int $requestedQuantity = null,
        ?CarbonInterface $at = null,
    ): EntitlementDecision {
        $at ??= Carbon::now();

        if (! EntitlementKeys::isKnown($featureKey)) {
            return EntitlementDecision::deny($featureKey, 'unknown_feature', null, $at);
        }

        $subscription = TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->with('plan')
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($subscription === null) {
            return EntitlementDecision::deny($featureKey, 'no_subscription', null, $at);
        }

        $effectiveStatus = $this->effectiveStatus($subscription, $at);

        if (! $effectiveStatus->grantsEntitlements()) {
            return EntitlementDecision::deny(
                $featureKey,
                $this->reasonForStatus($effectiveStatus),
                $effectiveStatus->value,
                $at,
            );
        }

        $plan = $subscription->plan;
        $feature = $plan?->features()->where('key', $featureKey)->first();

        if ($plan === null || $feature === null) {
            return EntitlementDecision::deny($featureKey, 'feature_not_in_plan', $effectiveStatus->value, $at);
        }

        $value = $feature->typedValue();

        if ($feature->type === FeatureType::Boolean) {
            $allowed = $value === true;

            return $this->decision($allowed, $featureKey, $value, $plan->code, $plan->version, $effectiveStatus, $allowed ? 'ok' : 'feature_disabled', $at);
        }

        if ($feature->type === FeatureType::Integer) {
            $limitExceeded = $requestedQuantity !== null
                && $value !== EntitlementKeys::UNLIMITED
                && $requestedQuantity > (int) $value;

            return $this->decision(! $limitExceeded, $featureKey, $value, $plan->code, $plan->version, $effectiveStatus, $limitExceeded ? 'limit_exceeded' : 'ok', $at);
        }

        return $this->decision(true, $featureKey, $value, $plan->code, $plan->version, $effectiveStatus, 'ok', $at);
    }

    /**
     * Truthful status independent of whether the reconciler has run yet: an elapsed
     * trial/period/grace deadline is reflected immediately so entitlements never over-grant.
     */
    private function effectiveStatus(TenantSubscription $subscription, CarbonInterface $at): SubscriptionStatus
    {
        $status = $subscription->status;

        if ($status === SubscriptionStatus::Trialing
            && $subscription->trial_ends_at !== null
            && $at->greaterThan($subscription->trial_ends_at)
        ) {
            return SubscriptionStatus::Expired;
        }

        if ($status === SubscriptionStatus::Active
            && $subscription->current_period_ends_at !== null
            && $at->greaterThan($subscription->current_period_ends_at)
        ) {
            if ($subscription->grace_ends_at !== null && $at->lessThanOrEqualTo($subscription->grace_ends_at)) {
                return SubscriptionStatus::Grace;
            }

            return SubscriptionStatus::Expired;
        }

        if ($status === SubscriptionStatus::Grace
            && $subscription->grace_ends_at !== null
            && $at->greaterThan($subscription->grace_ends_at)
        ) {
            return SubscriptionStatus::Expired;
        }

        return $status;
    }

    private function reasonForStatus(SubscriptionStatus $status): string
    {
        return match ($status) {
            SubscriptionStatus::Suspended => 'subscription_suspended',
            SubscriptionStatus::Cancelled => 'subscription_cancelled',
            SubscriptionStatus::Expired => 'subscription_expired',
            default => 'subscription_not_active',
        };
    }

    private function decision(
        bool $allowed,
        string $featureKey,
        bool|int|string|null $value,
        string $planCode,
        int $planVersion,
        SubscriptionStatus $status,
        string $reason,
        CarbonInterface $at,
    ): EntitlementDecision {
        return new EntitlementDecision(
            allowed: $allowed,
            featureKey: $featureKey,
            effectiveValue: $value,
            sourcePlanCode: $planCode,
            sourcePlanVersion: $planVersion,
            subscriptionStatus: $status->value,
            reasonCode: $reason,
            evaluatedAt: $at->toIso8601String(),
        );
    }
}
