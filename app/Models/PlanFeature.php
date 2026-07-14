<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeatureType;
use Database\Factories\PlanFeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A typed entitlement value for a plan. The value lives in exactly one typed column, chosen
 * by $type (rule 31 §9.3).
 *
 * @property int $id
 * @property string $ulid
 * @property int $plan_id
 * @property string $key
 * @property FeatureType $type
 * @property bool|null $value_boolean
 * @property int|null $value_int
 * @property string|null $value_string
 */
class PlanFeature extends Model
{
    /** @use HasFactory<PlanFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'key',
        'type',
        'value_boolean',
        'value_int',
        'value_string',
    ];

    protected function casts(): array
    {
        return [
            'type' => FeatureType::class,
            'value_boolean' => 'boolean',
            'value_int' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PlanFeature $feature): void {
            if (empty($feature->ulid)) {
                $feature->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** The typed value for this feature. */
    public function typedValue(): bool|int|string|null
    {
        return match ($this->type) {
            FeatureType::Boolean => $this->value_boolean,
            FeatureType::Integer => $this->value_int,
            FeatureType::StringValue => $this->value_string,
        };
    }

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
