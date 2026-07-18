<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-only merge/split ledger.
 *
 * A split is a NEW appended row referencing the merge it reverses — the original merge row is
 * never updated or deleted, so the ledger truthfully records the mistake as well as its
 * correction (rule 36; ADR 0072).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property string $action
 * @property int $survivor_customer_id
 * @property int $merged_customer_id
 * @property int|null $reverses_merge_event_id
 * @property int|null $actor_user_id
 * @property string $reason
 * @property array<string, mixed> $snapshot
 * @property Carbon|null $created_at
 */
class CustomerMergeEvent extends Model implements TenantOwned
{
    use BelongsToTenant;

    public const ACTION_MERGE = 'merge';

    public const ACTION_SPLIT = 'split';

    /** Append-only: no updated_at column (rule 36). */
    public const UPDATED_AT = null;

    protected $fillable = [
        'action',
        'survivor_customer_id',
        'merged_customer_id',
        'reverses_merge_event_id',
        'actor_user_id',
        'reason',
        'snapshot',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomerMergeEvent $event): void {
            if (empty($event->ulid)) {
                $event->ulid = (string) Str::ulid();
            }
        });

        // Hard append-only enforcement at the model layer — no service, job, or console command
        // can rewrite identity history (rule 36; ADR 0072).
        static::updating(function (): void {
            throw new \RuntimeException(
                'Customer merge events are append-only and cannot be edited (rule 36).'
            );
        });

        static::deleting(function (): void {
            throw new \RuntimeException(
                'Customer merge events are append-only and cannot be deleted (rule 36).'
            );
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Customer, $this> */
    public function survivor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'survivor_customer_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function mergedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'merged_customer_id');
    }

    /** @return BelongsTo<CustomerMergeEvent, $this> */
    public function reversesMergeEvent(): BelongsTo
    {
        return $this->belongsTo(CustomerMergeEvent::class, 'reverses_merge_event_id');
    }

    public function isMerge(): bool
    {
        return $this->action === self::ACTION_MERGE;
    }

    public function isSplit(): bool
    {
        return $this->action === self::ACTION_SPLIT;
    }
}
