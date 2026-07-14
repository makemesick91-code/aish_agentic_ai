<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The feedback-item ↔ tag link. Both parents are pinned to the same tenant by composite FKs at the
 * database layer, so a link can never join records from two tenants. Append-oriented: attaching and
 * removing a tag are distinct operations recorded on the timeline; the row itself carries only its
 * creation time (rule 33; Step 8 §12).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $feedback_item_id
 * @property int $feedback_tag_id
 * @property int|null $attached_by
 * @property Carbon|null $created_at
 */
class FeedbackItemTag extends Model implements TenantOwned
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    protected $table = 'feedback_item_tags';

    protected $fillable = [
        'tenant_id',
        'feedback_item_id',
        'feedback_tag_id',
        'attached_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<FeedbackItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(FeedbackItem::class, 'feedback_item_id');
    }

    /** @return BelongsTo<FeedbackTag, $this> */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(FeedbackTag::class, 'feedback_tag_id');
    }
}
