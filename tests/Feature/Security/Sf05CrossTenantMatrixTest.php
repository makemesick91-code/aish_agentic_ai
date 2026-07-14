<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Authorization\Roles;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\UsageRecord;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * SPRINT-SF-05 cross-tenant attack matrix. Every case here is a CRITICAL release blocker: a
 * single failure means tenant data can cross a boundary (rule 31 §19.4).
 */
final class Sf05CrossTenantMatrixTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_notification_read_state_idor_is_blocked(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        [$victim] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $delivery = NotificationDelivery::factory()->unread()->create(['tenant_id' => $tenant->id, 'recipient_id' => $victim->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->patch("/notifications/{$delivery->ulid}/read")
            ->assertForbidden();
        $this->assertNull($delivery->fresh()->read_at);
    }

    public function test_notification_preferences_are_tenant_scoped(): void
    {
        $a = $this->provisionTenant();
        $b = $this->provisionTenant();
        [$ua] = $this->memberWithoutRole($a);
        [$ub] = $this->memberWithoutRole($b);
        NotificationPreference::factory()->create(['tenant_id' => $a->id, 'user_id' => $ua->id]);
        NotificationPreference::factory()->create(['tenant_id' => $b->id, 'user_id' => $ub->id]);

        $this->establishTenantContext($a);
        $this->assertSame(1, NotificationPreference::query()->count(), 'Preferences must be tenant-scoped.');
    }

    public function test_subscriptions_are_tenant_scoped_and_fail_closed(): void
    {
        $a = $this->provisionTenant();
        $b = $this->provisionTenant();
        $plan = Plan::factory()->create();
        TenantSubscription::factory()->active()->create(['tenant_id' => $a->id, 'plan_id' => $plan->id]);
        TenantSubscription::factory()->active()->create(['tenant_id' => $b->id, 'plan_id' => $plan->id]);

        $this->establishTenantContext($a);
        $this->assertSame(1, TenantSubscription::query()->count());
    }

    public function test_usage_records_are_tenant_scoped(): void
    {
        $a = $this->provisionTenant();
        $b = $this->provisionTenant();
        app(UsageMeter::class)->record($a, MeterKeys::FOUNDATION_VERIFICATION, 1, 'k1');
        app(UsageMeter::class)->record($b, MeterKeys::FOUNDATION_VERIFICATION, 1, 'k2');

        $this->establishTenantContext($a);
        $this->assertSame(1, UsageRecord::query()->count());
    }

    public function test_a_forged_tenant_id_on_create_is_rejected(): void
    {
        $a = $this->provisionTenant();
        $b = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($a);

        $this->establishTenantContext($a);

        // Mass-assigning a foreign tenant_id while acting in tenant A must fail closed.
        $this->expectException(RuntimeException::class);
        NotificationPreference::create([
            'tenant_id' => $b->id,
            'user_id' => $user->id,
            'timezone' => 'Asia/Makassar',
        ]);
    }

    public function test_a_tenant_user_cannot_reach_the_platform_subscription_assignment(): void
    {
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();

        $this->actingAs($owner)
            ->post('/platform-admin/subscriptions', ['tenant' => $tenant->ulid, 'plan' => $plan->ulid])
            ->assertForbidden();
    }
}
