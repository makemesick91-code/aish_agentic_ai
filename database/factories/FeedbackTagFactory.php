<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackTagStatus;
use App\Models\FeedbackTag;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeedbackTag>
 */
class FeedbackTagFactory extends Factory
{
    protected $model = FeedbackTag::class;

    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'tenant_id' => $this->tenantId(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'status' => FeedbackTagStatus::Active,
            'color' => null,
            'created_by' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => FeedbackTagStatus::Archived]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
