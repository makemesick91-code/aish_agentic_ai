<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;

/**
 * Test helper: give a tenant an active subscription to a plan that grants the Step 7 survey
 * entitlements. Survey response acceptance and authoring fail closed without this, mirroring
 * the real fail-closed entitlement contract (rule 32; Step 7 §23).
 */
trait ProvisionsSurveyPlan
{
    /**
     * @param  array<string, int|bool>  $limits  override any survey entitlement (default: unlimited/enabled)
     */
    protected function provisionSurveyPlan(Tenant $tenant, array $limits = []): Plan
    {
        $plan = Plan::factory()->create();

        PlanFeature::factory()
            ->boolean(EntitlementKeys::SURVEYS_ENABLED, (bool) ($limits[EntitlementKeys::SURVEYS_ENABLED] ?? true))
            ->create(['plan_id' => $plan->id]);

        foreach ([
            EntitlementKeys::SURVEYS_MAX,
            EntitlementKeys::SURVEY_CAMPAIGNS_MAX,
            EntitlementKeys::SURVEY_INVITATIONS_MONTHLY,
            EntitlementKeys::SURVEY_RESPONSES_MONTHLY,
        ] as $key) {
            PlanFeature::factory()
                ->integer($key, (int) ($limits[$key] ?? EntitlementKeys::UNLIMITED))
                ->create(['plan_id' => $plan->id]);
        }

        TenantSubscription::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        return $plan;
    }
}
