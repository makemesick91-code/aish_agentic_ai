<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeatureType;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Subscriptions\EntitlementKeys;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanFeature>
 */
class PlanFeatureFactory extends Factory
{
    protected $model = PlanFeature::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'key' => EntitlementKeys::BRANCHES_MAX,
            'type' => FeatureType::Integer,
            'value_int' => 5,
        ];
    }

    public function integer(string $key, int $value): static
    {
        return $this->state(fn () => [
            'key' => $key,
            'type' => FeatureType::Integer,
            'value_int' => $value,
            'value_boolean' => null,
            'value_string' => null,
        ]);
    }

    public function boolean(string $key, bool $value): static
    {
        return $this->state(fn () => [
            'key' => $key,
            'type' => FeatureType::Boolean,
            'value_boolean' => $value,
            'value_int' => null,
            'value_string' => null,
        ]);
    }
}
