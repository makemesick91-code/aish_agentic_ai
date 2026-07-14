<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\SurveyMode;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyCampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A campaign binds an immutable published survey version to a distribution. Tenant-owned;
 * `public_id` is an opaque ULID used in public/QR links (no sequential id, tenant not
 * inferable). It never silently switches version (rule 32; Step 7 §16, ADR 0058).
 *
 * @property int $id
 * @property string $ulid
 * @property string $public_id
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property int $survey_id
 * @property int $survey_version_id
 * @property string $name
 * @property CampaignStatus $status
 * @property SurveyMode $mode
 * @property array<string, mixed>|null $channel_config
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int|null $invitation_expiry_days
 * @property array<string, mixed>|null $frequency_config
 * @property int|null $created_by
 */
class SurveyCampaign extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyCampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'survey_id',
        'survey_version_id',
        'name',
        'status',
        'mode',
        'channel_config',
        'starts_at',
        'ends_at',
        'invitation_expiry_days',
        'frequency_config',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'mode' => SurveyMode::class,
            'channel_config' => 'array',
            'frequency_config' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'invitation_expiry_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyCampaign $campaign): void {
            if (empty($campaign->ulid)) {
                $campaign->ulid = (string) Str::ulid();
            }
            if (empty($campaign->public_id)) {
                $campaign->public_id = (string) Str::ulid();
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

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return HasMany<SurveyInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(SurveyInvitation::class, 'campaign_id');
    }

    /** @return HasMany<SurveyResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'campaign_id');
    }
}
