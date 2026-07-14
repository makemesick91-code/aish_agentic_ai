<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\PlatformRole;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * SPRINT-SF-05 console commands: secure platform-admin provisioning and idempotent
 * subscription reconciliation (rule 31 §10.5, §9.8).
 */
final class Sf05CommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_provision_creates_an_operator_without_exposing_a_password(): void
    {
        Notification::fake();

        $this->artisan('aish:platform-admin-provision', ['--email' => 'boss@example.test', '--role' => 'super_admin'])
            ->expectsOutputToContain('password-reset link has been sent')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'boss@example.test']);
        $this->assertDatabaseHas('platform_role_assignments', ['role' => PlatformRole::SuperAdmin->value]);
    }

    public function test_platform_admin_provision_requires_a_valid_email(): void
    {
        $this->artisan('aish:platform-admin-provision', ['--email' => 'not-an-email'])
            ->assertFailed();
    }

    public function test_subscription_reconcile_expires_a_lapsed_trial(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();
        $subscription = TenantSubscription::factory()->trialExpiredButUnreconciled()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        $this->artisan('aish:subscription-reconcile')->assertSuccessful();

        $this->assertSame(SubscriptionStatus::Expired, $subscription->fresh()->status);
    }
}
