<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The tenant aggregate root. Not itself tenant-owned (it *is* the tenant). Public
 * routing uses the non-enumerable ULID; the integer id is internal and doubles as the
 * Spatie RBAC "team" key (rule 03, rule 20).
 *
 * @property int $id
 * @property string $ulid
 * @property TenantStatus $status
 * @property Carbon|null $suspended_at
 * @property Carbon|null $deletion_requested_at
 */
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'timezone',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'suspended_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            if (empty($tenant->ulid)) {
                $tenant->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::Active;
    }

    /** @return HasOne<TenantSetting, $this> */
    public function settings(): HasOne
    {
        return $this->hasOne(TenantSetting::class);
    }

    /** @return HasMany<Branch, $this> */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** @return HasMany<TenantMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    /** @return HasMany<TenantInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }
}
