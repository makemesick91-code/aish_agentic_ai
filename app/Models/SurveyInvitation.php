<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A unique survey invitation. Tenant-owned. Only the SHA-256 `token_hash` is stored — the
 * plaintext secret is transient (built once for the link) and is never persisted or logged.
 * `public_id` (opaque ULID) is what appears in the URL (rule 32; Step 7 §17, ADR 0058).
 *
 * @property int $id
 * @property string $ulid
 * @property string $public_id
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property int $campaign_id
 * @property int $survey_version_id
 * @property string $token_hash
 * @property string|null $recipient_email
 * @property InvitationStatus $status
 * @property string $idempotency_key
 * @property Carbon|null $expires_at
 * @property Carbon|null $opened_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $revoked_at
 * @property string|null $delivery_failure_code
 * @property int|null $created_by
 */
class SurveyInvitation extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyInvitationFactory> */
    use HasFactory;

    /**
     * Never expose the token hash through array/JSON serialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'token_hash',
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'campaign_id',
        'survey_version_id',
        'token_hash',
        'recipient_email',
        'status',
        'idempotency_key',
        'expires_at',
        'opened_at',
        'completed_at',
        'revoked_at',
        'delivery_failure_code',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
            'opened_at' => 'datetime',
            'completed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyInvitation $invitation): void {
            if (empty($invitation->ulid)) {
                $invitation->ulid = (string) Str::ulid();
            }
            if (empty($invitation->public_id)) {
                $invitation->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** Whether this invitation is presently usable, accounting for expiry. */
    public function isUsable(): bool
    {
        if (! $this->status->isUsable()) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /** @return BelongsTo<SurveyCampaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
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
}
