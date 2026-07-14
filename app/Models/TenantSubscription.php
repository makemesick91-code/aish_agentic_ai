<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\TenantSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A tenant's current subscription. Tenant-owned and fail-closed scoped. Commercial state
 * only — it never implies payment, and it never overrides tenant security suspension
 * (rule 31 §9.1, §9.5).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property Carbon|null $started_at
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_starts_at
 * @property Carbon|null $current_period_ends_at
 * @property Carbon|null $grace_ends_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $ended_at
 */
class TenantSubscription extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<TenantSubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'started_at',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'grace_ends_at',
        'cancelled_at',
        'ended_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TenantSubscription $subscription): void {
            if (empty($subscription->ulid)) {
                $subscription->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** @return HasMany<SubscriptionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class);
    }
}
