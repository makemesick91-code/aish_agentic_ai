<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SubscriptionEvent;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionEvent>
 */
class SubscriptionEventFactory extends Factory
{
    protected $model = SubscriptionEvent::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'tenant_subscription_id' => TenantSubscription::factory(),
            'event' => 'created',
            'from_status' => null,
            'to_status' => 'trialing',
            'metadata' => [],
            'created_at' => now(),
        ];
    }
}
