<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Enums\PlatformRole;
use App\Models\NotificationDelivery;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlatformSupportNote;
use App\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPlatform;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Smoke test: every platform screen renders for an authorized operator (and users management is
 * Super-Admin-only). This exercises the Blade views end-to-end (rule 31 §17).
 */
final class PlatformUiSmokeTest extends TestCase
{
    use InteractsWithPlatform;
    use ProvisionsTenants;
    use RefreshDatabase;

    private function seedPlatformData(): array
    {
        $tenant = $this->provisionTenant();
        [$member] = $this->memberWithoutRole($tenant);
        $plan = Plan::factory()->create();
        PlanFeature::factory()->create(['plan_id' => $plan->id]);
        $subscription = TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
        NotificationDelivery::factory()->create(['tenant_id' => $tenant->id, 'recipient_id' => $member->id]);
        PlatformSupportNote::factory()->create(['tenant_id' => $tenant->id, 'author_id' => $member->id]);

        return compact('tenant', 'plan', 'subscription');
    }

    public function test_all_platform_screens_render_for_an_admin_operator(): void
    {
        ['tenant' => $tenant, 'plan' => $plan] = $this->seedPlatformData();
        $admin = $this->platformUser(PlatformRole::Admin);

        foreach ([
            '/platform-admin',
            '/platform-admin/tenants',
            "/platform-admin/tenants/{$tenant->ulid}",
            '/platform-admin/plans',
            "/platform-admin/plans/{$plan->ulid}",
            '/platform-admin/subscriptions',
            '/platform-admin/notifications',
            '/platform-admin/audit',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
            $this->endRequestScopeForPlatform();
        }
    }

    public function test_users_management_is_super_admin_only(): void
    {
        $this->actingAs($this->platformUser(PlatformRole::Admin))->get('/platform-admin/users')->assertForbidden();
        $this->actingAs($this->platformUser(PlatformRole::SuperAdmin))->get('/platform-admin/users')->assertOk();
    }

    private function endRequestScopeForPlatform(): void
    {
        $this->app->forgetScopedInstances();
    }
}
