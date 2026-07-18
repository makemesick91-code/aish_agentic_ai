<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;

/**
 * Test helper: give a tenant an active subscription to a plan that grants the Step 10 Customer 360
 * entitlements. Customer surfaces and merge fail closed without these, mirroring the real
 * fail-closed entitlement contract (rule 36; contract §10).
 */
trait ProvisionsCustomer360Plan
{
    /**
     * @param  array<string, bool>  $flags  override any customer entitlement (default: enabled)
     */
    protected function provisionCustomer360Plan(Tenant $tenant, array $flags = []): Plan
    {
        $plan = Plan::factory()->create();

        foreach ([
            EntitlementKeys::CUSTOMER_360_ENABLED,
            EntitlementKeys::CUSTOMER_360_MERGE_ENABLED,
        ] as $key) {
            PlanFeature::factory()
                ->boolean($key, $flags[$key] ?? true)
                ->create(['plan_id' => $plan->id]);
        }

        TenantSubscription::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        return $plan;
    }
}
