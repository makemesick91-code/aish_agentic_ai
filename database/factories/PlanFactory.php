<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'code' => 'plan_'.Str::lower(Str::random(8)),
            'version' => 1,
            'name' => fake()->words(2, true).' Plan',
            'description' => fake()->sentence(),
            'status' => PlanStatus::Active,
            'public_visible' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => PlanStatus::Draft]);
    }

    public function retired(): static
    {
        return $this->state(fn () => ['status' => PlanStatus::Retired]);
    }
}
