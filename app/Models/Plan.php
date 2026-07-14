<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanStatus;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A plan in the global catalog (platform-owned; NOT tenant-owned). Identified by (code,
 * version). Only an active plan is assignable; a retired plan stays valid for existing
 * references (rule 31 §9.2).
 *
 * @property int $id
 * @property string $ulid
 * @property string $code
 * @property int $version
 * @property PlanStatus $status
 * @property bool $public_visible
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'version',
        'name',
        'description',
        'status',
        'public_visible',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'version' => 'integer',
            'public_visible' => 'boolean',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Plan $plan): void {
            if (empty($plan->ulid)) {
                $plan->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function isAssignable(): bool
    {
        return $this->status->isAssignable();
    }

    /** @return HasMany<PlanFeature, $this> */
    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
}
