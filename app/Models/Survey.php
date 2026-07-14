<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveyStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A survey: a tenant-owned stable identity whose content lives in immutable-once-published
 * versions. `branch_id` null = tenant-wide. A published survey is never hard-deleted
 * (rule 32; Step 7 §10).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property string $name
 * @property string|null $description
 * @property SurveyStatus $status
 * @property int|null $current_version_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Survey extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'description',
        'status',
        'current_version_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => SurveyStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Survey $survey): void {
            if (empty($survey->ulid)) {
                $survey->ulid = (string) Str::ulid();
            }
        });

        static::deleting(function (Survey $survey): void {
            $original = $survey->getOriginal('status');
            $value = $original instanceof SurveyStatus ? $original->value : $original;
            if ($value !== null && $value !== SurveyStatus::Draft->value) {
                throw new \RuntimeException('A published survey cannot be hard-deleted (rule 32).');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<SurveyVersion, $this> */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(SurveyVersion::class, 'current_version_id');
    }

    /** @return HasMany<SurveyVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(SurveyVersion::class);
    }

    /** @return HasMany<SurveyCampaign, $this> */
    public function campaigns(): HasMany
    {
        return $this->hasMany(SurveyCampaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
