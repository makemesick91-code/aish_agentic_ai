<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationState;
use App\Models\NotificationDelivery;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    protected $model = NotificationDelivery::class;

    public function definition(): array
    {
        $key = (string) Str::ulid();

        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => null,
            'recipient_id' => User::factory(),
            'type' => NotificationType::MembershipActivated->value,
            'category' => NotificationCategory::Membership->value,
            'channel' => NotificationChannel::InApp->value,
            'state' => NotificationState::Sent->value,
            'critical' => false,
            'idempotency_key' => $key,
            'dedup_key' => $key.':in_app',
            'subject' => 'Membership activated',
            'body' => 'Your membership is now active.',
            'attempts' => 1,
            'max_attempts' => 3,
            'sent_at' => now(),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn () => ['read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => NotificationChannel::Email->value,
            'dedup_key' => ($attributes['idempotency_key'] ?? Str::ulid()).':email',
        ]);
    }
}
