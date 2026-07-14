<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveyMode;
use App\Enums\SurveyVersionStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * An immutable-once-published survey version. The content columns (title, introduction,
 * completion_message, locale, mode, version_number) cannot change after publication; only the
 * status may advance to `superseded`. Editing published content creates a NEW draft version
 * (rule 32; Step 7 §11).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $survey_id
 * @property int $version_number
 * @property SurveyVersionStatus $status
 * @property string $title
 * @property string|null $introduction
 * @property string|null $completion_message
 * @property string $locale
 * @property SurveyMode $mode
 * @property Carbon|null $published_at
 * @property int|null $published_by
 */
class SurveyVersion extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyVersionFactory> */
    use HasFactory;

    /** Columns frozen once the version is published. */
    private const IMMUTABLE_AFTER_PUBLISH = [
        'survey_id', 'tenant_id', 'version_number', 'title', 'introduction',
        'completion_message', 'locale', 'mode', 'published_at', 'published_by',
    ];

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'version_number',
        'status',
        'title',
        'introduction',
        'completion_message',
        'locale',
        'mode',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => SurveyVersionStatus::class,
            'mode' => SurveyMode::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyVersion $version): void {
            if (empty($version->ulid)) {
                $version->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (SurveyVersion $version): void {
            $original = $version->getOriginal('status');
            $value = $original instanceof SurveyVersionStatus ? $original->value : $original;

            // A draft is freely editable; a published/superseded version's content is frozen.
            if ($value === null || $value === SurveyVersionStatus::Draft->value) {
                return;
            }

            foreach (self::IMMUTABLE_AFTER_PUBLISH as $column) {
                if ($version->isDirty($column)) {
                    throw new \RuntimeException(
                        'Published survey version content is immutable; edit by creating a new draft version (rule 32).'
                    );
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isEditable(): bool
    {
        return $this->status === SurveyVersionStatus::Draft;
    }

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return HasMany<SurveyQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('display_order');
    }

    /** @return BelongsTo<User, $this> */
    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
