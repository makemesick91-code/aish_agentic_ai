<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedbackSourceType;
use App\Enums\FeedbackStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Operational feedback item projected from a completed survey response. Tenant-owned; branch scope
 * follows the source response. Source identity and survey references are immutable once set; the
 * item is never hard-deleted through the normal workflow. It holds an operational projection and
 * allowlisted references only — never a free-text copy of the response (rule 33; Step 8 §9, §10, §25).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property FeedbackSourceType $source_type
 * @property int $source_id
 * @property int|null $survey_response_id
 * @property int|null $survey_id
 * @property int|null $survey_version_id
 * @property int|null $campaign_id
 * @property int|null $invitation_id
 * @property FeedbackStatus $status
 * @property int|null $current_assignee_id
 * @property Carbon|null $triaged_at
 * @property Carbon|null $assigned_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $archived_at
 * @property Carbon|null $reopened_at
 * @property Carbon|null $last_activity_at
 * @property array<string, mixed>|null $metric_snapshot
 * @property string|null $search_meta
 * @property string|null $search_content
 * @property int|null $created_by
 * @property-read Branch|null $branch
 * @property-read Survey|null $survey
 * @property-read SurveyCampaign|null $campaign
 * @property-read SurveyResponse|null $surveyResponse
 * @property-read User|null $assignee
 */
class FeedbackItem extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackItemFactory> */
    use HasFactory;

    /** Columns that are immutable once the item has been projected. */
    private const IMMUTABLE_SOURCE = [
        'tenant_id', 'source_type', 'source_id', 'survey_response_id',
        'survey_id', 'survey_version_id', 'campaign_id', 'invitation_id',
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'source_type',
        'source_id',
        'survey_response_id',
        'survey_id',
        'survey_version_id',
        'campaign_id',
        'invitation_id',
        'status',
        'current_assignee_id',
        'triaged_at',
        'assigned_at',
        'resolved_at',
        'closed_at',
        'archived_at',
        'reopened_at',
        'last_activity_at',
        'metric_snapshot',
        'search_meta',
        'search_content',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => FeedbackSourceType::class,
            'status' => FeedbackStatus::class,
            'metric_snapshot' => 'array',
            'triaged_at' => 'datetime',
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'archived_at' => 'datetime',
            'reopened_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackItem $item): void {
            if (empty($item->ulid)) {
                $item->ulid = (string) Str::ulid();
            }
            if (empty($item->last_activity_at)) {
                $item->last_activity_at = now();
            }
        });

        static::updating(function (FeedbackItem $item): void {
            foreach (self::IMMUTABLE_SOURCE as $column) {
                if ($item->isDirty($column) && $item->getOriginal($column) !== null) {
                    throw new \RuntimeException(
                        'Feedback source identity and survey references are immutable (rule 33).'
                    );
                }
            }
        });

        static::deleting(function (): void {
            throw new \RuntimeException('Feedback items are never hard-deleted through the normal workflow (rule 33).');
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

    /** @return BelongsTo<SurveyResponse, $this> */
    public function surveyResponse(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
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

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    /** @return HasMany<FeedbackEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(FeedbackEvent::class);
    }

    /** @return HasMany<FeedbackNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(FeedbackNote::class);
    }

    /** @return HasMany<FeedbackAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(FeedbackAttachment::class);
    }

    /** @return HasMany<FeedbackAssignment, $this> */
    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(FeedbackAssignment::class);
    }

    /** @return BelongsToMany<FeedbackTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(FeedbackTag::class, 'feedback_item_tags')
            ->withPivot(['tenant_id', 'attached_by', 'created_at']);
    }
}
