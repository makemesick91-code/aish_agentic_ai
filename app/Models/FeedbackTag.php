<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedbackTagStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Tenant-owned manual feedback tag. Names/slugs are unique within a tenant. Archived tags cannot be
 * newly attached; renaming does not rewrite historical event meaning. Manual tags are distinct from
 * future AI-generated topics (rule 33; Step 8 §12).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property string $name
 * @property string $slug
 * @property FeedbackTagStatus $status
 * @property string|null $color
 * @property int|null $created_by
 */
class FeedbackTag extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackTagFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'status',
        'color',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeedbackTagStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackTag $tag): void {
            if (empty($tag->ulid)) {
                $tag->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsToMany<FeedbackItem, $this> */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(FeedbackItem::class, 'feedback_item_tags')
            ->withPivot(['tenant_id', 'attached_by', 'created_at']);
    }
}
