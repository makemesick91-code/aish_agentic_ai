<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A tenant user's notification preferences. Tenant-owned and fail-closed scoped, so a
 * preference row is only ever visible/writable within its own tenant (rule 03, rule 31).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property string $ulid
 * @property bool $in_app_enabled
 * @property bool $email_enabled
 * @property string|null $quiet_hours_start
 * @property string|null $quiet_hours_end
 * @property string $timezone
 * @property array<string, array<string, bool>>|null $category_overrides
 */
class NotificationPreference extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'in_app_enabled',
        'email_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
        'category_overrides',
    ];

    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'category_overrides' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NotificationPreference $preference): void {
            if (empty($preference->ulid)) {
                $preference->ulid = (string) Str::ulid();
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
}
