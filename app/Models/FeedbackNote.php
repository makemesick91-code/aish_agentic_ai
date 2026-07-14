<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-only internal staff note on a feedback item. Operational only — never customer
 * communication. The body is untrusted free text: escaped on output, never written to logs, audit
 * metadata, or default notifications. Immutable at the model layer; a correction is a new note
 * (rule 33; Step 8 §13).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $feedback_item_id
 * @property int|null $branch_id
 * @property int|null $author_id
 * @property string $body
 * @property Carbon|null $created_at
 */
class FeedbackNote extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackNoteFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** Hard cap on note body length (also enforced by the form request). */
    public const MAX_BODY_LENGTH = 5000;

    protected $fillable = [
        'tenant_id',
        'feedback_item_id',
        'branch_id',
        'author_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackNote $note): void {
            if (empty($note->ulid)) {
                $note->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (): void {
            throw new \RuntimeException('Feedback notes are append-only and cannot be edited (rule 33).');
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Feedback notes are append-only and cannot be deleted (rule 33).');
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
