<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerConsentType;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\CustomerConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-only consent and communication-preference history. Withdrawing consent appends a new
 * row rather than editing the old one, so what the customer agreed to — and when — stays
 * provable (rule 36, rule 32; ADR 0064).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $customer_id
 * @property CustomerConsentType $consent_type
 * @property bool $accepted
 * @property string $consent_text_version
 * @property string $source
 * @property string|null $channel
 * @property int|null $recorded_by
 * @property Carbon|null $created_at
 */
class CustomerConsent extends Model implements TenantOwned
{
    /** @use HasFactory<CustomerConsentFactory> */
    use BelongsToTenant;

    use HasFactory;

    /** Append-only: no updated_at column (rule 36). */
    public const UPDATED_AT = null;

    protected $fillable = [
        'customer_id',
        'consent_type',
        'accepted',
        'consent_text_version',
        'source',
        'channel',
        'recorded_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomerConsent $consent): void {
            if (empty($consent->ulid)) {
                $consent->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (): void {
            throw new \RuntimeException(
                'Customer consent records are append-only and cannot be edited (rule 36).'
            );
        });

        static::deleting(function (): void {
            throw new \RuntimeException(
                'Customer consent records are append-only and cannot be deleted (rule 36).'
            );
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consent_type' => CustomerConsentType::class,
            'accepted' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
