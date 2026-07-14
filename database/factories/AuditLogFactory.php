<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 *
 * AuditLog is deliberately NOT tenant-scoped (it records platform/pre-auth events too),
 * so tenant_id is set explicitly here rather than stamped from a context.
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => null,
            'actor_id' => null,
            'event' => 'test.event',
            'subject_type' => null,
            'subject_id' => null,
            'correlation_id' => null,
            'channel' => 'web',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'metadata' => [],
        ];
    }
}
