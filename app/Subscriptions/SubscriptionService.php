<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Audit\AuditRecorder;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\SubscriptionEvent;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Tenancy\TenantOwnerLocator;
use App\Subscriptions\Exceptions\InvalidSubscriptionTransitionException;
use App\Subscriptions\Exceptions\RetiredPlanException;
use Illuminate\Support\Facades\DB;

/**
 * Owns subscription lifecycle: creation, plan change, and status transitions. Every mutation
 * is transactional, records an append-only subscription_event, is audited, and notifies the
 * tenant's owners. Invalid transitions and retired-plan assignment are refused. It never
 * implies payment (rule 31 §9.4).
 */
final class SubscriptionService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly NotificationDispatcher $dispatcher,
        private readonly TenantOwnerLocator $owners,
    ) {}

    public function start(
        Tenant $tenant,
        Plan $plan,
        SubscriptionStatus $status = SubscriptionStatus::Trialing,
        ?int $trialDays = 14,
        ?int $periodDays = null,
        string $source = 'platform',
        ?int $actorId = null,
    ): TenantSubscription {
        if (! $plan->isAssignable()) {
            throw RetiredPlanException::make($plan);
        }

        return DB::transaction(function () use ($tenant, $plan, $status, $trialDays, $periodDays, $source, $actorId): TenantSubscription {
            $now = now();

            $subscription = new TenantSubscription;
            $subscription->tenant_id = $tenant->id;
            $subscription->plan_id = $plan->id;
            $subscription->status = $status;
            $subscription->source = $source;
            $subscription->started_at = $now;

            if ($status === SubscriptionStatus::Trialing && $trialDays !== null) {
                $subscription->trial_ends_at = $now->copy()->addDays($trialDays);
            }

            if ($status === SubscriptionStatus::Active && $periodDays !== null) {
                $subscription->current_period_starts_at = $now;
                $subscription->current_period_ends_at = $now->copy()->addDays($periodDays);
            }

            $subscription->save();

            $this->recordEvent($subscription, 'created', null, $status->value, [
                'plan' => $plan->code,
                'version' => $plan->version,
            ]);

            $this->audit->record('subscription.created', [
                'tenant_id' => $tenant->id,
                'actor_id' => $actorId,
                'subject' => $subscription,
                'metadata' => ['plan' => $plan->code, 'status' => $status->value],
            ]);

            $type = $status === SubscriptionStatus::Trialing
                ? NotificationType::SubscriptionTrialStarted
                : NotificationType::SubscriptionStatusChanged;

            $this->notifyOwners(
                $tenant,
                $type,
                __('Subscription started'),
                __('Your workspace subscription is now :status.', ['status' => $status->label()]),
                "subscription.created.{$subscription->id}",
                $actorId,
            );

            return $subscription;
        });
    }

    public function changePlan(TenantSubscription $subscription, Plan $plan, ?int $actorId = null): TenantSubscription
    {
        if (! $plan->isAssignable()) {
            throw RetiredPlanException::make($plan);
        }

        return DB::transaction(function () use ($subscription, $plan, $actorId): TenantSubscription {
            $previousPlanId = $subscription->plan_id;
            $subscription->forceFill(['plan_id' => $plan->id])->save();

            $this->recordEvent($subscription, 'plan.changed', $subscription->status->value, $subscription->status->value, [
                'from_plan_id' => $previousPlanId,
                'to_plan' => $plan->code,
                'version' => $plan->version,
            ]);

            $this->audit->record('subscription.plan.changed', [
                'tenant_id' => $subscription->tenant_id,
                'actor_id' => $actorId,
                'subject' => $subscription,
                'metadata' => ['to_plan' => $plan->code, 'version' => $plan->version],
            ]);

            $tenant = Tenant::find($subscription->tenant_id);
            if ($tenant !== null) {
                $this->notifyOwners(
                    $tenant,
                    NotificationType::SubscriptionEntitlementChanged,
                    __('Plan changed'),
                    __('Your workspace plan changed to :plan.', ['plan' => $plan->name]),
                    "subscription.plan.{$subscription->id}.{$plan->id}",
                    $actorId,
                );
            }

            return $subscription;
        });
    }

    public function transition(
        TenantSubscription $subscription,
        SubscriptionStatus $to,
        ?string $reason = null,
        ?int $actorId = null,
    ): TenantSubscription {
        $from = $subscription->status;

        if ($from === $to) {
            // Idempotent: reconciliation and repeated calls do not re-emit events.
            return $subscription;
        }

        if (! SubscriptionStateMachine::canTransition($from, $to)) {
            throw InvalidSubscriptionTransitionException::make($from, $to);
        }

        return DB::transaction(function () use ($subscription, $from, $to, $reason, $actorId): TenantSubscription {
            $now = now();
            $attributes = ['status' => $to];

            if ($to === SubscriptionStatus::Cancelled) {
                $attributes['cancelled_at'] = $now;
            } elseif ($to === SubscriptionStatus::Expired) {
                $attributes['ended_at'] = $now;
            } elseif ($to === SubscriptionStatus::Active) {
                $attributes['cancelled_at'] = null;
                $attributes['ended_at'] = null;
            }

            $subscription->forceFill($attributes)->save();

            $this->recordEvent($subscription, 'status.changed', $from->value, $to->value, ['reason' => $reason]);

            $this->audit->record('subscription.status.changed', [
                'tenant_id' => $subscription->tenant_id,
                'actor_id' => $actorId,
                'subject' => $subscription,
                'metadata' => ['from' => $from->value, 'to' => $to->value, 'reason' => $reason],
            ]);

            $tenant = Tenant::find($subscription->tenant_id);
            if ($tenant !== null) {
                $this->notifyOwners(
                    $tenant,
                    NotificationType::SubscriptionStatusChanged,
                    __('Subscription status changed'),
                    __('Your workspace subscription is now :status.', ['status' => $to->label()]),
                    "subscription.status.{$subscription->id}.{$to->value}",
                    $actorId,
                );
            }

            return $subscription;
        });
    }

    /**
     * @param  array<string, scalar|null>  $metadata
     */
    private function recordEvent(TenantSubscription $subscription, string $event, ?string $from, ?string $to, array $metadata): void
    {
        SubscriptionEvent::create([
            'tenant_id' => $subscription->tenant_id,
            'tenant_subscription_id' => $subscription->id,
            'event' => $event,
            'from_status' => $from,
            'to_status' => $to,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    private function notifyOwners(
        Tenant $tenant,
        NotificationType $type,
        string $subject,
        string $body,
        string $idempotencyKey,
        ?int $actorId,
    ): void {
        foreach ($this->owners->activeOwners($tenant) as $owner) {
            $this->dispatcher->dispatch(
                type: $type,
                recipient: $owner,
                idempotencyKey: $idempotencyKey.':'.$owner->id,
                subject: $subject,
                tenant: $tenant,
                body: $body,
                actorId: $actorId,
            );
        }
    }
}
