<?php

declare(strict_types=1);

namespace Tests\Feature\Subscriptions;

use App\Authorization\Roles;
use App\Enums\SubscriptionStatus;
use App\Models\NotificationDelivery;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Notifications\NotificationType;
use App\Subscriptions\Exceptions\InvalidSubscriptionTransitionException;
use App\Subscriptions\Exceptions\RetiredPlanException;
use App\Subscriptions\SubscriptionReconciler;
use App\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Subscription lifecycle: creation, plan change, guarded transitions, retired-plan refusal, and
 * idempotent reconciliation with a single notification (rule 31 §9.4, §9.8).
 */
final class SubscriptionLifecycleTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    private function service(): SubscriptionService
    {
        return app(SubscriptionService::class);
    }

    public function test_start_creates_a_trialing_subscription_records_an_event_and_notifies_owners(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();

        $subscription = $this->service()->start($tenant, $plan);

        $this->assertSame(SubscriptionStatus::Trialing, $subscription->status);
        $this->assertDatabaseHas('subscription_events', ['tenant_subscription_id' => $subscription->id, 'event' => 'created']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'subscription.created']);
        $this->assertTrue(
            NotificationDelivery::where('recipient_id', $owner->id)
                ->where('type', NotificationType::SubscriptionTrialStarted->value)
                ->exists(),
        );
    }

    public function test_a_retired_plan_cannot_be_assigned(): void
    {
        $tenant = $this->provisionTenant();
        $plan = Plan::factory()->retired()->create();

        $this->expectException(RetiredPlanException::class);
        $this->service()->start($tenant, $plan);
    }

    public function test_changing_to_a_retired_plan_is_refused(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $plan = Plan::factory()->create();
        $subscription = $this->service()->start($tenant, $plan);
        $retired = Plan::factory()->retired()->create();

        $this->expectException(RetiredPlanException::class);
        $this->service()->changePlan($subscription, $retired);
    }

    public function test_valid_transitions_are_applied_and_invalid_ones_are_rejected(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $this->service()->transition($subscription, SubscriptionStatus::Suspended, 'non-payment');
        $this->assertSame(SubscriptionStatus::Suspended, $subscription->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'subscription.status.changed']);

        // suspended -> cancelled is allowed; a later cancelled -> trialing is not.
        $this->service()->transition($subscription, SubscriptionStatus::Cancelled);
        $this->expectException(InvalidSubscriptionTransitionException::class);
        $this->service()->transition($subscription, SubscriptionStatus::Trialing);
    }

    public function test_transition_to_the_same_status_is_an_idempotent_no_op(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $this->service()->transition($subscription, SubscriptionStatus::Active);

        $this->assertDatabaseCount('subscription_events', 0);
    }

    public function test_reconciliation_is_idempotent_and_transitions_an_expired_trial_once(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->trialExpiredButUnreconciled()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $reconciler = app(SubscriptionReconciler::class);

        $first = $reconciler->reconcileAll(Carbon::now());
        $second = $reconciler->reconcileAll(Carbon::now());

        $this->assertSame(1, $first, 'The expired trial should transition exactly once.');
        $this->assertSame(0, $second, 'A second run must be a no-op.');
        $this->assertSame(SubscriptionStatus::Expired, $subscription->fresh()->status);
        // The status-changed notification is emitted once, not on every run.
        $this->assertSame(
            1,
            NotificationDelivery::where('recipient_id', $owner->id)
                ->where('type', NotificationType::SubscriptionStatusChanged->value)
                ->where('channel', 'in_app')
                ->count(),
        );
    }
}
