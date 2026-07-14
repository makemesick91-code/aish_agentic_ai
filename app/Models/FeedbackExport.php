<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeedbackExportStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\FeedbackExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A queued feedback export. The generated CSV lives on a PRIVATE disk (storage internals hidden),
 * has an expiry, and is downloadable only by an authorized member of the owning tenant. State is
 * truthful — `Ready` only after the file is written (rule 33; Step 8 §18).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int|null $branch_id
 * @property int|null $requested_by
 * @property FeedbackExportStatus $status
 * @property string $format
 * @property bool $includes_content
 * @property array<string, mixed>|null $filters
 * @property string|null $disk
 * @property string|null $path
 * @property int|null $row_count
 * @property int|null $size_bytes
 * @property string|null $failure_code
 * @property string $idempotency_key
 * @property Carbon|null $ready_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $downloaded_at
 */
class FeedbackExport extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<FeedbackExportFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'requested_by',
        'status',
        'format',
        'includes_content',
        'filters',
        'disk',
        'path',
        'row_count',
        'size_bytes',
        'failure_code',
        'idempotency_key',
        'ready_at',
        'expires_at',
        'downloaded_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'disk',
        'path',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeedbackExportStatus::class,
            'includes_content' => 'boolean',
            'filters' => 'array',
            'row_count' => 'integer',
            'size_bytes' => 'integer',
            'ready_at' => 'datetime',
            'expires_at' => 'datetime',
            'downloaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FeedbackExport $export): void {
            if (empty($export->ulid)) {
                $export->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isExpired(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->expires_at !== null && $this->expires_at->lessThanOrEqualTo($at);
    }
}
