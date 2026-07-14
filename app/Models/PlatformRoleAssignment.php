<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformRole;
use Database\Factories\PlatformRoleAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A single platform role granted to a user. Platform-plane (global), NOT tenant-owned
 * (rule 31 §10.1).
 *
 * @property int $id
 * @property string $ulid
 * @property int $user_id
 * @property PlatformRole $role
 * @property int|null $granted_by
 */
class PlatformRoleAssignment extends Model
{
    /** @use HasFactory<PlatformRoleAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'granted_by',
    ];

    protected function casts(): array
    {
        return [
            'role' => PlatformRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PlatformRoleAssignment $assignment): void {
            if (empty($assignment->ulid)) {
                $assignment->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
