<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\UsageRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A single idempotent usage increment. Tenant-owned; the tenant-leading unique
 * (tenant_id, meter_key, idempotency_key) guarantees a repeated increment is a no-op
 * (rule 31 §9.7).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property string $meter_key
 * @property int $quantity
 * @property string $idempotency_key
 * @property string $period_key
 * @property Carbon $occurred_at
 */
class UsageRecord extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<UsageRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'meter_key',
        'quantity',
        'idempotency_key',
        'occurred_at',
        'period_key',
        'source_reference',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UsageRecord $record): void {
            if (empty($record->ulid)) {
                $record->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
