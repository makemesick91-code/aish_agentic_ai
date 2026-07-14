<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BranchStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $ulid
 * @property string $code
 * @property BranchStatus $status
 */
class Branch extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'status',
        'timezone',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'status' => BranchStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Branch $branch): void {
            if (empty($branch->ulid)) {
                $branch->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isActive(): bool
    {
        return $this->status === BranchStatus::Active;
    }
}
