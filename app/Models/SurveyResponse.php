<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ResponseStatus;
use App\Enums\SurveyMode;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A survey response bound to the exact answered version. Tenant-owned. A completed response is
 * immutable through the normal workflow; `invalidated` requires an authorized reason and never
 * deletes the row. `metadata` carries only a minimized allowlist — never answer content
 * (rule 32; Step 7 §18, §19).
 *
 * @property int $id
 * @property string $ulid
 * @property string $correlation_id
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property int $survey_id
 * @property int $survey_version_id
 * @property int|null $campaign_id
 * @property int|null $invitation_id
 * @property SurveyMode $mode
 * @property ResponseStatus $status
 * @property string|null $locale
 * @property Carbon|null $started_at
 * @property Carbon|null $submitted_at
 * @property Carbon|null $invalidated_at
 * @property string|null $invalidated_reason
 * @property array<string, mixed>|null $metadata
 */
class SurveyResponse extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'survey_id',
        'survey_version_id',
        'campaign_id',
        'invitation_id',
        'mode',
        'status',
        'locale',
        'started_at',
        'submitted_at',
        'invalidated_at',
        'invalidated_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ResponseStatus::class,
            'mode' => SurveyMode::class,
            'metadata' => 'array',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyResponse $response): void {
            if (empty($response->ulid)) {
                $response->ulid = (string) Str::ulid();
            }
            if (empty($response->correlation_id)) {
                $response->correlation_id = (string) Str::ulid();
            }
        });

        // A completed response is immutable except for the authorized invalidation fields.
        static::updating(function (SurveyResponse $response): void {
            $original = $response->getOriginal('status');
            $value = $original instanceof ResponseStatus ? $original->value : $original;

            if ($value !== ResponseStatus::Completed->value) {
                return;
            }

            $allowed = ['status', 'invalidated_at', 'invalidated_reason', 'updated_at'];
            foreach (array_keys($response->getDirty()) as $column) {
                if (! in_array($column, $allowed, true)) {
                    throw new \RuntimeException(
                        'A completed survey response is immutable except for authorized invalidation (rule 32).'
                    );
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return BelongsTo<SurveyVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(SurveyVersion::class, 'survey_version_id');
    }

    /** @return BelongsTo<SurveyCampaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }

    /** @return BelongsTo<SurveyInvitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(SurveyInvitation::class, 'invitation_id');
    }

    /** @return HasMany<SurveyAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }
}
