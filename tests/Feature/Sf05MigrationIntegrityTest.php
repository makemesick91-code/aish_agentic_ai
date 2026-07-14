<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlatformRole;
use App\Models\NotificationDelivery;
use App\Models\Plan;
use App\Models\PlatformRoleAssignment;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * SPRINT-SF-05 schema integrity: the unique constraints that enforce idempotency/isolation
 * exist and bite, and append-oriented tables have no updated_at (rule 31 §12).
 */
final class Sf05MigrationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_dedup_key_is_globally_unique(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();
        NotificationDelivery::factory()->create(['tenant_id' => $tenant->id, 'recipient_id' => $user->id, 'dedup_key' => 'dup']);

        $this->expectException(UniqueConstraintViolationException::class);
        NotificationDelivery::factory()->create(['tenant_id' => $tenant->id, 'recipient_id' => $user->id, 'dedup_key' => 'dup']);
    }

    public function test_a_tenant_has_at_most_one_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();
        TenantSubscription::factory()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        $this->expectException(UniqueConstraintViolationException::class);
        TenantSubscription::factory()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
    }

    public function test_plan_code_and_version_are_unique(): void
    {
        Plan::factory()->create(['code' => 'growth', 'version' => 1]);

        $this->expectException(UniqueConstraintViolationException::class);
        Plan::factory()->create(['code' => 'growth', 'version' => 1]);
    }

    public function test_a_platform_role_is_held_at_most_once_per_user(): void
    {
        $user = User::factory()->create();
        PlatformRoleAssignment::factory()->create(['user_id' => $user->id, 'role' => PlatformRole::Admin]);

        $this->expectException(UniqueConstraintViolationException::class);
        PlatformRoleAssignment::factory()->create(['user_id' => $user->id, 'role' => PlatformRole::Admin]);
    }

    public function test_append_oriented_tables_have_no_updated_at(): void
    {
        $this->assertTrue(Schema::hasColumn('subscription_events', 'created_at'));
        $this->assertFalse(Schema::hasColumn('subscription_events', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('platform_support_notes', 'created_at'));
        $this->assertFalse(Schema::hasColumn('platform_support_notes', 'updated_at'));
    }

    public function test_core_sf05_tables_exist(): void
    {
        foreach ([
            'notification_preferences',
            'notification_deliveries',
            'plans',
            'plan_features',
            'tenant_subscriptions',
            'subscription_events',
            'usage_records',
            'platform_role_assignments',
            'platform_support_notes',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }
}
