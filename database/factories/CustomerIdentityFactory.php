<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Customers\Identity\IdentityHasher;
use App\Customers\Identity\IdentityNormalizer;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerIdentity>
 */
class CustomerIdentityFactory extends Factory
{
    protected $model = CustomerIdentity::class;

    /**
     * Resolved on demand so that `forCustomer()` — which overrides both tenant_id and customer_id —
     * never triggers the creation of a stray customer in a different tenant.
     */
    private ?Customer $fallbackCustomer = null;

    public function definition(): array
    {
        return [
            'tenant_id' => fn (): int => $this->fallbackCustomer()->tenant_id,
            'customer_id' => fn (): int => $this->fallbackCustomer()->id,
            'source_type' => CustomerIdentitySource::Survey,
            'identity_type' => CustomerIdentityType::Email,
            // PII identities never persist a plaintext value (ADR 0071) — the model enforces this.
            'value_normalized' => null,
            'value_hash' => fn (array $attributes): string => $this->hashFor(
                (int) $attributes['tenant_id'],
                CustomerIdentityType::Email,
                $this->faker->unique()->safeEmail(),
            ),
            'normalizer_version' => IdentityNormalizer::VERSION,
            'provenance' => 'survey_response',
            'confidence' => 100,
            'is_deterministic' => true,
            'is_verified' => true,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ];
    }

    public function forCustomer(Customer $customer): self
    {
        return $this->state(fn (): array => [
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
        ]);
    }

    /** Build the identity from a real value so tests exercise the production hashing path. */
    public function withValue(CustomerIdentityType $type, string $value): self
    {
        return $this->state(fn (array $attributes): array => [
            'identity_type' => $type,
            'value_normalized' => $type->isPii() ? null : $value,
            'value_hash' => $this->hashFor((int) $attributes['tenant_id'], $type, $value),
        ]);
    }

    /** An unverified identity may only ever be a suggestion, never a deterministic link. */
    public function suggested(int $confidence = 70): self
    {
        return $this->state(fn (): array => [
            'is_deterministic' => false,
            'is_verified' => false,
            'confidence' => $confidence,
        ]);
    }

    private function fallbackCustomer(): Customer
    {
        return $this->fallbackCustomer ??= Customer::factory()->create();
    }

    private function hashFor(int $tenantId, CustomerIdentityType $type, string $value): string
    {
        $normalizer = app(IdentityNormalizer::class);
        $hasher = app(IdentityHasher::class);

        return $hasher->hash($tenantId, $normalizer->normalize($type, $value, '62'));
    }
}
