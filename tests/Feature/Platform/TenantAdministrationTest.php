<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Authorization\Roles;
use App\Enums\PlatformRole;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\NotificationDelivery;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Notifications\NotificationType;
use App\Platform\TenantAdministrationService;
use App\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Tests\Concerns\InteractsWithPlatform;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Platform tenant administration: reason-required, audited status changes that notify owners,
 * and that a commercial (subscription) state never overrides a security state and vice versa
 * (rule 31 §10.8, §9.5).
 */
final class TenantAdministrationTest extends TestCase
{
    use InteractsWithPlatform;
    use ProvisionsTenants;
    use RefreshDatabase;

    private function admin(): TenantAdministrationService
    {
        return app(TenantAdministrationService::class);
    }

    public function test_suspend_requires_a_reason(): void
    {
        $tenant = $this->provisionTenant();

        $this->expectException(InvalidArgumentException::class);
        $this->admin()->suspend($tenant, '   ');
    }

    public function test_suspend_sets_status_audits_and_notifies_owners(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $actor = $this->platformUser(PlatformRole::Admin);

        $this->admin()->suspend($tenant, 'Policy violation', $actor);

        $this->assertSame(TenantStatus::Suspended, $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->suspended_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform.tenant.suspended', 'tenant_id' => $tenant->id]);
        $this->assertTrue(
            NotificationDelivery::where('recipient_id', $owner->id)
                ->where('type', NotificationType::TenantSuspended->value)
                ->exists(),
        );
    }

    public function test_reactivate_and_mark_deletion_pending(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $this->admin()->suspend($tenant, 'temp');
        $this->admin()->reactivate($tenant);
        $this->assertSame(TenantStatus::Active, $tenant->fresh()->status);

        $this->admin()->markDeletionPending($tenant, 'owner request');
        $this->assertSame(TenantStatus::DeletionPending, $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->deletion_requested_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform.tenant.deletion_pending']);
    }

    public function test_tenant_suspension_does_not_change_the_subscription(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $this->admin()->suspend($tenant, 'security');

        $this->assertSame(SubscriptionStatus::Active, $subscription->fresh()->status, 'Security suspension must not mutate commercial state.');
    }

    public function test_subscription_suspension_does_not_change_the_tenant(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        app(SubscriptionService::class)->transition($subscription, SubscriptionStatus::Suspended, 'billing');

        $this->assertSame(TenantStatus::Active, $tenant->fresh()->status, 'A commercial suspension must not suspend the tenant.');
    }

    public function test_an_admin_operator_can_suspend_a_tenant_over_http(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $admin = $this->platformUser(PlatformRole::Admin);

        $this->actingAs($admin)
            ->patch("/platform-admin/tenants/{$tenant->ulid}/suspend", ['reason' => 'abuse report'])
            ->assertRedirect(route('platform.tenants.show', $tenant));

        $this->assertSame(TenantStatus::Suspended, $tenant->fresh()->status);
    }
}
