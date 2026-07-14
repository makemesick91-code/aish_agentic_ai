<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SubscriptionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-oriented subscription history record. Tenant-owned. Update/delete are blocked at the
 * model layer and there is no updated_at (rule 07, rule 31 §9.4).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $tenant_subscription_id
 * @property string $event
 * @property string|null $from_status
 * @property string|null $to_status
 * @property Carbon|null $created_at
 */
class SubscriptionEvent extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SubscriptionEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'tenant_subscription_id',
        'event',
        'from_status',
        'to_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionEvent $event): void {
            if (empty($event->ulid)) {
                $event->ulid = (string) Str::ulid();
            }
        });

        static::updating(fn () => throw new \RuntimeException('Subscription events are append-only and cannot be updated.'));
        static::deleting(fn () => throw new \RuntimeException('Subscription events are append-only and cannot be deleted.'));
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<TenantSubscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }
}
