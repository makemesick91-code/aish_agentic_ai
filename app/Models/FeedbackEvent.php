<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedbackEventType;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-only operational timeline event for a feedback item. Immutable: no `updated_at`, and
 * update/delete are blocked at the model layer. Metadata is allowlisted and sanitized — never a
 * note body, response free text, attachment content, token, or storage path (rule 33; Step 8 §15).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $feedback_item_id
 * @property int|null $branch_id
 * @property FeedbackEventType $type
 * @property int|null $actor_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 */
class FeedbackEvent extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'feedback_item_id',
        'branch_id',
        'type',
        'actor_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => FeedbackEventType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackEvent $event): void {
            if (empty($event->ulid)) {
                $event->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (): void {
            throw new \RuntimeException('Feedback timeline events are append-only and cannot be updated (rule 33).');
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Feedback timeline events are append-only and cannot be deleted (rule 33).');
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<FeedbackItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(FeedbackItem::class, 'feedback_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
