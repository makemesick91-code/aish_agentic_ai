<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;

/**
 * Test helper: give a tenant an active subscription to a plan that grants the Step 8 feedback
 * entitlements. Feedback attachments/exports/bulk actions fail closed without these, mirroring the
 * real fail-closed entitlement contract (rule 33; Step 8 §22).
 */
trait ProvisionsFeedbackPlan
{
    /**
     * @param  array<string, bool>  $flags  override any feedback entitlement (default: enabled)
     */
    protected function provisionFeedbackPlan(Tenant $tenant, array $flags = []): Plan
    {
        $plan = Plan::factory()->create();

        foreach ([
            EntitlementKeys::FEEDBACK_ENABLED,
            EntitlementKeys::FEEDBACK_ATTACHMENTS_ENABLED,
            EntitlementKeys::FEEDBACK_EXPORTS_ENABLED,
            EntitlementKeys::FEEDBACK_BULK_ACTIONS_ENABLED,
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
