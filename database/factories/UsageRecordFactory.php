<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\UsageRecord;
use App\Subscriptions\MeterKeys;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UsageRecord>
 */
class UsageRecordFactory extends Factory
{
    protected $model = UsageRecord::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'meter_key' => MeterKeys::FOUNDATION_VERIFICATION,
            'quantity' => 1,
            'idempotency_key' => (string) Str::ulid(),
            'occurred_at' => now(),
            'period_key' => now()->setTimezone('Asia/Makassar')->format('Y-m'),
        ];
    }
}
