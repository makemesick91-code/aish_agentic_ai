<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\TenantMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property string $ulid
 * @property MembershipStatus $status
 * @property bool $all_branches
 * @property Carbon|null $accepted_at
 * @property Carbon|null $suspended_at
 * @property Carbon|null $revoked_at
 */
class TenantMembership extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<TenantMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'status',
        'all_branches',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'all_branches' => 'boolean',
            'accepted_at' => 'datetime',
            'suspended_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TenantMembership $membership): void {
            if (empty($membership->ulid)) {
                $membership->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function grantsAccess(): bool
    {
        return $this->status === MembershipStatus::Active;
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<BranchAccessGrant, $this> */
    public function branchAccessGrants(): HasMany
    {
        return $this->hasMany(BranchAccessGrant::class);
    }
}
