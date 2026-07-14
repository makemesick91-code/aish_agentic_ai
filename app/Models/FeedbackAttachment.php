<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedbackAttachmentState;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Internal operational attachment. The file lives on a PRIVATE disk under a tenant-prefixed path
 * with a random stored filename; storage internals (`disk`, `path`, `stored_filename`) are hidden
 * from serialization so they never leak. Never hard-deleted through the normal workflow — removal is
 * a `Removed` state (rule 33; Step 8 §14).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $feedback_item_id
 * @property int|null $branch_id
 * @property int|null $uploaded_by
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string $stored_filename
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $checksum_sha256
 * @property FeedbackAttachmentState $state
 * @property string|null $rejected_reason
 * @property Carbon|null $removed_at
 */
class FeedbackAttachment extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'feedback_item_id',
        'branch_id',
        'uploaded_by',
        'disk',
        'path',
        'original_filename',
        'stored_filename',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'state',
        'rejected_reason',
        'removed_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'disk',
        'path',
        'stored_filename',
    ];

    protected function casts(): array
    {
        return [
            'state' => FeedbackAttachmentState::class,
            'size_bytes' => 'integer',
            'removed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackAttachment $attachment): void {
            if (empty($attachment->ulid)) {
                $attachment->ulid = (string) Str::ulid();
            }
        });

        static::deleting(function (): void {
            throw new \RuntimeException(
                'Feedback attachments are not hard-deleted through the normal workflow; use the removed state (rule 33).'
            );
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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
