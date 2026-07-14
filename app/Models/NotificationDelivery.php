<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationState;
use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A single notification delivery (one recipient, one channel, one logical event).
 * Deliberately NOT tenant-scoped by the fail-closed TenantScope — a delivery can be a
 * legitimate platform event with a null tenant_id (see audit_logs precedent). Tenant
 * isolation of viewing is enforced by forTenant() + NotificationDeliveryPolicy; writes
 * always carry an explicit tenant_id (rule 03, rule 31).
 *
 * @property int $id
 * @property string $ulid
 * @property int|null $tenant_id
 * @property int|null $branch_id
 * @property int $recipient_id
 * @property NotificationState $state
 * @property NotificationChannel $channel
 * @property NotificationCategory $category
 * @property bool $critical
 * @property string $type
 * @property string $idempotency_key
 * @property string $dedup_key
 * @property string $subject
 * @property int $attempts
 * @property int $max_attempts
 * @property Carbon|null $read_at
 */
class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'recipient_id',
        'type',
        'category',
        'channel',
        'state',
        'critical',
        'idempotency_key',
        'dedup_key',
        'correlation_id',
        'subject',
        'body',
        'data',
        'attempts',
        'max_attempts',
        'queued_at',
        'sent_at',
        'failed_at',
        'cancelled_at',
        'read_at',
        'suppressed_reason',
        'failure_code',
    ];

    protected function casts(): array
    {
        return [
            'state' => NotificationState::class,
            'channel' => NotificationChannel::class,
            'category' => NotificationCategory::class,
            'critical' => 'boolean',
            'data' => 'array',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NotificationDelivery $delivery): void {
            if (empty($delivery->ulid)) {
                $delivery->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Constrain a query to one tenant's deliveries (tenant-facing views).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForRecipient(Builder $query, int $userId): Builder
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInApp(Builder $query): Builder
    {
        return $query->where('channel', NotificationChannel::InApp->value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
