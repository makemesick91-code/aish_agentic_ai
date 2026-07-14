<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\UserStatus;
use App\Tenancy\TenantScope;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * Global user identity. A user is NOT tenant-owned: it exists once, globally, and gains
 * access to a tenant only through an active TenantMembership. No tenant role is stored on
 * this model — tenant-scoped roles live in Spatie's tenant-keyed tables (rule 30).
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property UserStatus $status
 * @property Carbon|null $last_authenticated_at
 * @property Carbon|null $email_verified_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_authenticated_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /** @return HasMany<TenantMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    /**
     * The user's platform (operator) role assignments. This is the SEPARATE platform plane —
     * it grants no tenant-data access and is unrelated to tenant Spatie roles (rule 31 §10.1).
     *
     * @return HasMany<PlatformRoleAssignment, $this>
     */
    public function platformRoleAssignments(): HasMany
    {
        return $this->hasMany(PlatformRoleAssignment::class);
    }

    /**
     * The user's active membership for a given tenant id, or null. Queried without the
     * tenant scope on purpose: this runs during context resolution, before a context
     * exists (allowlisted infrastructure; rule 30).
     */
    public function activeMembershipFor(int $tenantId): ?TenantMembership
    {
        return TenantMembership::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $this->id)
            ->where('status', MembershipStatus::Active->value)
            ->first();
    }

    /**
     * All of the user's memberships that currently grant access, across tenants. Used by
     * the tenant selector. Bypasses the tenant scope by design (no context yet).
     *
     * @return Collection<int, TenantMembership>
     */
    public function accessibleMemberships()
    {
        return TenantMembership::withoutGlobalScope(TenantScope::class)
            ->with('tenant')
            ->where('user_id', $this->id)
            ->where('status', MembershipStatus::Active->value)
            ->get();
    }
}
