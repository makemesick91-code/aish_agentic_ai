<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackExportStatus;
use App\Models\FeedbackExport;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeedbackExport>
 */
class FeedbackExportFactory extends Factory
{
    protected $model = FeedbackExport::class;

    public function definition(): array
    {
        return [
            'tenant_id' => $this->tenantId(),
            'branch_id' => null,
            'requested_by' => User::factory(),
            'status' => FeedbackExportStatus::Pending,
            'format' => 'csv',
            'includes_content' => false,
            'filters' => [],
            'idempotency_key' => (string) Str::ulid(),
        ];
    }

    public function ready(): static
    {
        return $this->state(fn () => [
            'status' => FeedbackExportStatus::Ready,
            'disk' => 'local',
            'path' => 'tenants/exports/'.Str::ulid().'.csv',
            'row_count' => 3,
            'size_bytes' => 256,
            'ready_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
    }

    protected function tenantId(): mixed
    {
        $context = app(TenantContext::class);

        return $context->hasTenant() ? $context->tenantId() : Tenant::factory();
    }
}
