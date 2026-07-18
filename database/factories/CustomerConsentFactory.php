<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerConsentType;
use App\Models\Customer;
use App\Models\CustomerConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerConsent>
 */
class CustomerConsentFactory extends Factory
{
    protected $model = CustomerConsent::class;

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
            'consent_type' => CustomerConsentType::FollowUp,
            'accepted' => true,
            'consent_text_version' => 'v1',
            'source' => 'survey',
            'channel' => null,
            'recorded_by' => null,
            'created_at' => now(),
        ];
    }

    public function forCustomer(Customer $customer): self
    {
        return $this->state(fn (): array => [
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
        ]);
    }

    public function type(CustomerConsentType $type): self
    {
        return $this->state(fn (): array => ['consent_type' => $type]);
    }

    public function rejected(): self
    {
        return $this->state(fn (): array => ['accepted' => false]);
    }

    /** The suppression case: the customer explicitly asked not to be contacted. */
    public function doNotContact(): self
    {
        return $this->state(fn (): array => [
            'consent_type' => CustomerConsentType::DoNotContact,
            'accepted' => true,
        ]);
    }

    private function fallbackCustomer(): Customer
    {
        return $this->fallbackCustomer ??= Customer::factory()->create();
    }
}
