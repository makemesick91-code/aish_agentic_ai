<?php

declare(strict_types=1);

namespace Tests\Feature\Subscriptions;

use App\Models\Tenant;
use App\Subscriptions\Exceptions\InvalidUsageException;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Usage metering is tenant-scoped, idempotent, and refuses unknown meters and negative
 * quantities (rule 31 §9.7).
 */
final class UsageMeterTest extends TestCase
{
    use RefreshDatabase;

    private function meter(): UsageMeter
    {
        return app(UsageMeter::class);
    }

    public function test_recording_is_idempotent_for_the_same_key(): void
    {
        $tenant = Tenant::factory()->create();

        $this->meter()->record($tenant, MeterKeys::FOUNDATION_VERIFICATION, 5, 'op-1');
        $this->meter()->record($tenant, MeterKeys::FOUNDATION_VERIFICATION, 5, 'op-1');

        $this->assertDatabaseCount('usage_records', 1);
        $this->assertSame(5, $this->meter()->total($tenant, MeterKeys::FOUNDATION_VERIFICATION));
        $this->assertDatabaseHas('audit_logs', ['event' => 'usage.recorded']);
    }

    public function test_an_unknown_meter_is_rejected(): void
    {
        $tenant = Tenant::factory()->create();

        $this->expectException(InvalidUsageException::class);
        $this->meter()->record($tenant, 'nope.meter', 1, 'op-x');
    }

    public function test_a_negative_quantity_is_rejected_and_audited(): void
    {
        $tenant = Tenant::factory()->create();

        try {
            $this->meter()->record($tenant, MeterKeys::FOUNDATION_VERIFICATION, -1, 'op-neg');
            $this->fail('Expected InvalidUsageException.');
        } catch (InvalidUsageException) {
            // expected
        }

        $this->assertDatabaseHas('audit_logs', ['event' => 'usage.correction.rejected']);
    }

    public function test_usage_is_isolated_per_tenant(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        // Same idempotency key in two tenants must not collide.
        $this->meter()->record($a, MeterKeys::FOUNDATION_VERIFICATION, 3, 'shared-key');
        $this->meter()->record($b, MeterKeys::FOUNDATION_VERIFICATION, 7, 'shared-key');

        $this->assertSame(3, $this->meter()->total($a, MeterKeys::FOUNDATION_VERIFICATION));
        $this->assertSame(7, $this->meter()->total($b, MeterKeys::FOUNDATION_VERIFICATION));
    }
}
