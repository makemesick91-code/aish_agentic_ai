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

    public function definition(): array
    {
        $customer = Customer::factory()->create();

        return [
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
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
}
