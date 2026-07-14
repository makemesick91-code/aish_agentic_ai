<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Audit\AuditRecorder;
use App\Enums\SubscriptionStatus;
use App\Models\TenantSubscription;
use App\Tenancy\TenantScope;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Deterministic, idempotent reconciliation of subscription deadlines (trial/period/grace
 * expiry). Safe to rerun: a subscription already in its target state is a no-op, so no
 * duplicate transition or notification is produced. It makes no payment assumption
 * (rule 31 §9.8).
 */
final class SubscriptionReconciler
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly AuditRecorder $audit,
    ) {}

    public function reconcileAll(?CarbonInterface $at = null): int
    {
        $at ??= Carbon::now();
        $transitioned = 0;

        TenantSubscription::withoutGlobalScope(TenantScope::class)
            ->get()
            ->each(function (TenantSubscription $subscription) use ($at, &$transitioned): void {
                if ($this->reconcileOne($subscription, $at)) {
                    $transitioned++;
                }
            });

        return $transitioned;
    }

    public function reconcileOne(TenantSubscription $subscription, ?CarbonInterface $at = null, ?int $actorId = null): bool
    {
        $at ??= Carbon::now();
        $status = $subscription->status;
        $target = $this->targetFor($subscription, $at);

        if ($target === null || $target === $status) {
            return false;
        }

        $this->subscriptions->transition($subscription, $target, 'reconciliation', $actorId);

        $this->audit->record('subscription.reconciled', [
            'tenant_id' => $subscription->tenant_id,
            'actor_id' => $actorId,
            'subject' => $subscription,
            'metadata' => ['from' => $status->value, 'to' => $target->value],
        ]);

        return true;
    }

    private function targetFor(TenantSubscription $subscription, CarbonInterface $at): ?SubscriptionStatus
    {
        return match ($subscription->status) {
            SubscriptionStatus::Trialing => ($subscription->trial_ends_at !== null && $at->greaterThan($subscription->trial_ends_at))
                ? SubscriptionStatus::Expired
                : null,
            SubscriptionStatus::Active => ($subscription->current_period_ends_at !== null && $at->greaterThan($subscription->current_period_ends_at))
                ? (($subscription->grace_ends_at !== null && $at->lessThanOrEqualTo($subscription->grace_ends_at))
                    ? SubscriptionStatus::Grace
                    : SubscriptionStatus::Expired)
                : null,
            SubscriptionStatus::Grace => ($subscription->grace_ends_at !== null && $at->greaterThan($subscription->grace_ends_at))
                ? SubscriptionStatus::Expired
                : null,
            default => null,
        };
    }
}
