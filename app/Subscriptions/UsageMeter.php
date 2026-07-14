<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Audit\AuditRecorder;
use App\Models\Tenant;
use App\Models\UsageRecord;
use App\Subscriptions\Exceptions\InvalidUsageException;
use App\Tenancy\TenantScope;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

/**
 * Generic, tenant-scoped, idempotent usage metering. Recording the same (tenant, meter,
 * idempotency key) twice is a no-op — a retry can never double-count. Period boundaries are
 * timezone-aware. Negative quantities are refused outside an explicit correction workflow
 * (rule 31 §9.7).
 */
final class UsageMeter
{
    private const PERIOD_TIMEZONE = 'Asia/Makassar';

    public function __construct(private readonly AuditRecorder $audit) {}

    public function record(
        Tenant $tenant,
        string $meterKey,
        int $quantity,
        string $idempotencyKey,
        ?CarbonInterface $occurredAt = null,
        ?string $periodKey = null,
        ?string $sourceReference = null,
        ?int $actorId = null,
    ): UsageRecord {
        if (! MeterKeys::isKnown($meterKey)) {
            throw InvalidUsageException::unknownMeter($meterKey);
        }

        if ($quantity < 0) {
            $this->audit->record('usage.correction.rejected', [
                'tenant_id' => $tenant->id,
                'actor_id' => $actorId,
                'metadata' => ['meter_key' => $meterKey, 'quantity' => $quantity],
            ]);

            throw InvalidUsageException::negativeQuantity($meterKey);
        }

        $occurredAt = $occurredAt === null ? Carbon::now() : Carbon::instance($occurredAt);
        $periodKey ??= $occurredAt->copy()
            ->setTimezone(self::PERIOD_TIMEZONE)
            ->format('Y-m');

        $existing = $this->find($tenant, $meterKey, $idempotencyKey);
        if ($existing !== null) {
            return $existing;
        }

        try {
            $record = new UsageRecord;
            $record->tenant_id = $tenant->id;
            $record->meter_key = $meterKey;
            $record->quantity = $quantity;
            $record->idempotency_key = $idempotencyKey;
            $record->occurred_at = $occurredAt;
            $record->period_key = $periodKey;
            $record->source_reference = $sourceReference;
            $record->save();

            $this->audit->record('usage.recorded', [
                'tenant_id' => $tenant->id,
                'actor_id' => $actorId,
                'subject' => $record,
                'metadata' => ['meter_key' => $meterKey, 'quantity' => $quantity, 'period_key' => $periodKey],
            ]);

            return $record;
        } catch (UniqueConstraintViolationException) {
            return $this->find($tenant, $meterKey, $idempotencyKey) ?? throw new \RuntimeException('Usage record vanished after a unique conflict.');
        }
    }

    /** Total recorded quantity for a meter in a period (tenant-scoped). */
    public function total(Tenant $tenant, string $meterKey, ?string $periodKey = null): int
    {
        $query = UsageRecord::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('meter_key', $meterKey);

        if ($periodKey !== null) {
            $query->where('period_key', $periodKey);
        }

        return (int) $query->sum('quantity');
    }

    private function find(Tenant $tenant, string $meterKey, string $idempotencyKey): ?UsageRecord
    {
        return UsageRecord::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('meter_key', $meterKey)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }
}
