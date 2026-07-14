<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-only assignment-history row. A null `new_assignee_id` is an unassignment. Immutable at the
 * model layer; the current assignee is denormalized onto the feedback item (rule 33; Step 8 §11).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $feedback_item_id
 * @property int|null $previous_assignee_id
 * @property int|null $new_assignee_id
 * @property int|null $actor_id
 * @property string|null $reason
 * @property Carbon|null $created_at
 */
class FeedbackAssignment extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackAssignmentFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'feedback_item_id',
        'previous_assignee_id',
        'new_assignee_id',
        'actor_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackAssignment $assignment): void {
            if (empty($assignment->ulid)) {
                $assignment->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (): void {
            throw new \RuntimeException('Feedback assignment history is append-only and cannot be updated (rule 33).');
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Feedback assignment history is append-only and cannot be deleted (rule 33).');
        });
    }

    /** @return BelongsTo<FeedbackItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(FeedbackItem::class, 'feedback_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function newAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_assignee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function previousAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_assignee_id');
    }
}
