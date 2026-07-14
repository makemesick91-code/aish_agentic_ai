<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'in_app_enabled' => true,
            'email_enabled' => true,
            'timezone' => 'Asia/Makassar',
            'category_overrides' => null,
        ];
    }

    public function emailDisabled(): static
    {
        return $this->state(fn () => ['email_enabled' => false]);
    }

    public function quietHours(string $start, string $end): static
    {
        return $this->state(fn () => ['quiet_hours_start' => $start, 'quiet_hours_end' => $end]);
    }
}
