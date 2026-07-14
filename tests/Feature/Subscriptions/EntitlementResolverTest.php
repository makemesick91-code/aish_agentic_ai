<?php

declare(strict_types=1);

namespace Tests\Feature\Subscriptions;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The authoritative entitlement resolver fails closed on every uncertainty and reflects an
 * elapsed deadline immediately (rule 31 §9.6).
 */
final class EntitlementResolverTest extends TestCase
{
    use RefreshDatabase;

    private function resolver(): EntitlementResolver
    {
        return app(EntitlementResolver::class);
    }

    private function tenantWithPlan(callable $withFeatures, string $subscriptionState = 'active'): Tenant
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();
        $withFeatures($plan);
        TenantSubscription::factory()->{$subscriptionState}()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        return $tenant;
    }

    public function test_an_unknown_feature_fails_closed(): void
    {
        $tenant = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->integer(EntitlementKeys::BRANCHES_MAX, 3)->create(['plan_id' => $p->id]));

        $decision = $this->resolver()->resolve($tenant, 'made.up.key');

        $this->assertFalse($decision->allowed);
        $this->assertSame('unknown_feature', $decision->reasonCode);
    }

    public function test_a_tenant_without_a_subscription_fails_closed(): void
    {
        $tenant = Tenant::factory()->create();

        $decision = $this->resolver()->resolve($tenant, EntitlementKeys::BRANCHES_MAX);

        $this->assertFalse($decision->allowed);
        $this->assertSame('no_subscription', $decision->reasonCode);
    }

    public function test_a_feature_not_defined_on_the_plan_fails_closed(): void
    {
        $tenant = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->integer(EntitlementKeys::BRANCHES_MAX, 3)->create(['plan_id' => $p->id]));

        $decision = $this->resolver()->resolve($tenant, EntitlementKeys::USERS_MAX);

        $this->assertFalse($decision->allowed);
        $this->assertSame('feature_not_in_plan', $decision->reasonCode);
    }

    public function test_boolean_entitlements_resolve(): void
    {
        $tenant = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->boolean(EntitlementKeys::API_ENABLED, true)->create(['plan_id' => $p->id]));
        $this->assertTrue($this->resolver()->resolve($tenant, EntitlementKeys::API_ENABLED)->allowed);

        $tenant2 = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->boolean(EntitlementKeys::API_ENABLED, false)->create(['plan_id' => $p->id]));
        $decision = $this->resolver()->resolve($tenant2, EntitlementKeys::API_ENABLED);
        $this->assertFalse($decision->allowed);
        $this->assertSame('feature_disabled', $decision->reasonCode);
    }

    public function test_integer_limits_and_the_unlimited_sentinel(): void
    {
        $tenant = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->integer(EntitlementKeys::BRANCHES_MAX, 3)->create(['plan_id' => $p->id]));

        $this->assertTrue($this->resolver()->resolve($tenant, EntitlementKeys::BRANCHES_MAX, 3)->allowed);
        $exceeded = $this->resolver()->resolve($tenant, EntitlementKeys::BRANCHES_MAX, 4);
        $this->assertFalse($exceeded->allowed);
        $this->assertSame('limit_exceeded', $exceeded->reasonCode);

        $unlimited = $this->tenantWithPlan(fn (Plan $p) => PlanFeature::factory()->integer(EntitlementKeys::BRANCHES_MAX, EntitlementKeys::UNLIMITED)->create(['plan_id' => $p->id]));
        $this->assertTrue($this->resolver()->resolve($unlimited, EntitlementKeys::BRANCHES_MAX, 99999)->allowed);
    }

    public function test_an_expired_subscription_denies_entitlements(): void
    {
        $tenant = $this->tenantWithPlan(
            fn (Plan $p) => PlanFeature::factory()->boolean(EntitlementKeys::API_ENABLED, true)->create(['plan_id' => $p->id]),
            'expired',
        );

        $decision = $this->resolver()->resolve($tenant, EntitlementKeys::API_ENABLED);

        $this->assertFalse($decision->allowed);
        $this->assertSame('subscription_expired', $decision->reasonCode);
    }

    public function test_a_trial_past_its_end_is_treated_as_expired_before_reconciliation(): void
    {
        $tenant = $this->tenantWithPlan(
            fn (Plan $p) => PlanFeature::factory()->boolean(EntitlementKeys::API_ENABLED, true)->create(['plan_id' => $p->id]),
            'trialExpiredButUnreconciled',
        );

        $decision = $this->resolver()->resolve($tenant, EntitlementKeys::API_ENABLED);

        $this->assertFalse($decision->allowed);
        $this->assertSame('subscription_expired', $decision->reasonCode);
    }
}
