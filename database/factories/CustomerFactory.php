<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => fn () => Tenant::factory(),
            'primary_branch_id' => null,
            'display_name' => $this->faker->name(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => '+6281'.$this->faker->unique()->numerify('#########'),
            'status' => CustomerStatus::Active,
            'legal_hold' => false,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'created_by' => null,
        ];
    }

    public function status(CustomerStatus $status): self
    {
        return $this->state(fn (): array => ['status' => $status]);
    }

    public function inactive(): self
    {
        return $this->status(CustomerStatus::Inactive);
    }

    /** A customer with no contact PII — the anonymous-source case. */
    public function withoutContact(): self
    {
        return $this->state(fn (): array => [
            'contact_email' => null,
            'contact_phone' => null,
        ]);
    }

    public function forBranch(int $branchId): self
    {
        return $this->state(fn (): array => ['primary_branch_id' => $branchId]);
    }

    public function legalHold(): self
    {
        return $this->state(fn (): array => ['legal_hold' => true]);
    }
}
