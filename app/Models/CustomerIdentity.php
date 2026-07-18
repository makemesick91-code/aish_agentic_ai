<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\CustomerIdentityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A source identity a customer can be matched on.
 *
 * For PII types (email, phone) the plaintext is NOT stored here — only a keyed hash bound to the
 * tenant, so the table is neither an offline-enumerable customer directory nor a cross-tenant
 * correlation oracle (rule 36; ADR 0071). The displayable value lives on the customer record
 * behind `customer.view-contact`.
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $customer_id
 * @property CustomerIdentitySource $source_type
 * @property CustomerIdentityType $identity_type
 * @property string|null $value_normalized
 * @property string $value_hash
 * @property int $normalizer_version
 * @property string|null $provenance
 * @property int $confidence
 * @property bool $is_deterministic
 * @property bool $is_verified
 * @property int|null $merged_from_customer_id
 * @property Carbon|null $first_seen_at
 * @property Carbon|null $last_seen_at
 */
class CustomerIdentity extends Model implements TenantOwned
{
    /** @use HasFactory<CustomerIdentityFactory> */
    use BelongsToTenant;

    use HasFactory;

    /**
     * The matched value and its hashing scheme can never be rewritten in place — a normalization
     * change ships as a new version plus an additive backfill, so historical links stay
     * explainable (rule 36; ADR 0071).
     */
    private const IMMUTABLE = ['ulid', 'tenant_id', 'identity_type', 'value_hash', 'normalizer_version'];

    protected $fillable = [
        'customer_id',
        'source_type',
        'identity_type',
        'value_normalized',
        'value_hash',
        'normalizer_version',
        'provenance',
        'confidence',
        'is_deterministic',
        'is_verified',
        'first_seen_at',
        'last_seen_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomerIdentity $identity): void {
            if (empty($identity->ulid)) {
                $identity->ulid = (string) Str::ulid();
            }

            if ($identity->first_seen_at === null) {
                $identity->first_seen_at = now();
            }

            $identity->guardAgainstPlaintextPii();
        });

        static::updating(function (CustomerIdentity $identity): void {
            foreach (self::IMMUTABLE as $column) {
                if ($identity->isDirty($column)) {
                    throw new \RuntimeException(
                        "Customer identity column [{$column}] is immutable and cannot be changed (rule 36)."
                    );
                }
            }

            $identity->guardAgainstPlaintextPii();
        });
    }

    /**
     * Structural enforcement of ADR 0071: a PII identity must never carry a plaintext value.
     * Enforcing it at the model layer means no service, job, import, or future caller can bypass
     * it by writing the column directly.
     */
    private function guardAgainstPlaintextPii(): void
    {
        if ($this->identity_type instanceof CustomerIdentityType
            && $this->identity_type->isPii()
            && $this->value_normalized !== null) {
            throw new \RuntimeException(
                'A PII customer identity must not store a plaintext value (rule 36; ADR 0071).'
            );
        }
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_type' => CustomerIdentitySource::class,
            'identity_type' => CustomerIdentityType::class,
            'confidence' => 'integer',
            'normalizer_version' => 'integer',
            'is_deterministic' => 'boolean',
            'is_verified' => 'boolean',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
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
