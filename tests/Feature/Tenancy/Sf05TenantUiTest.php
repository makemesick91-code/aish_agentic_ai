<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\TenantSubscription;
use App\Subscriptions\EntitlementKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Tenant-facing SPRINT-SF-05 screens render with truthful states (rule 31 §17).
 */
final class Sf05TenantUiTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_notification_preferences_screen_renders(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/notification-preferences')
            ->assertOk()
            ->assertSee('Notification preferences');
    }

    public function test_subscription_overview_renders_with_entitlements(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();
        PlanFeature::factory()->integer(EntitlementKeys::BRANCHES_MAX, 3)->create(['plan_id' => $plan->id]);
        TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->get('/subscription')
            ->assertOk()
            ->assertSee('Effective entitlements');
    }
}
