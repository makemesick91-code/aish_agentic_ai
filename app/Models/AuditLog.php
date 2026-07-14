<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-oriented audit record. Deliberately NOT tenant-scoped by the fail-closed
 * TenantScope: audit must record platform and pre-authentication events where no tenant
 * context exists (e.g. a failed login). Tenant isolation of *viewing* is enforced by the
 * forTenant() scope + AuditLogPolicy, and writes always carry an explicit tenant_id from
 * the recorder. Update/delete are blocked at the model layer (append-only; rule 07).
 *
 * @property int $id
 * @property string $ulid
 * @property int|null $tenant_id
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'actor_id',
        'event',
        'subject_type',
        'subject_id',
        'correlation_id',
        'channel',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log): void {
            if (empty($log->ulid)) {
                $log->ulid = (string) Str::ulid();
            }
        });

        // Append-only: block mutation and deletion through the application layer.
        static::updating(fn () => throw new \RuntimeException('Audit records are append-only and cannot be updated.'));
        static::deleting(fn () => throw new \RuntimeException('Audit records are append-only and cannot be deleted.'));
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Constrain a query to a single tenant's audit trail (used by tenant-facing views).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
