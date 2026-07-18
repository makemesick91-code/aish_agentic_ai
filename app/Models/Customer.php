<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The canonical tenant-scoped customer aggregate — the single source of truth for customer
 * identity across the Experience OS. Other domains reference `customer_id`; they never create,
 * merge, or mutate identity (rule 36; ADR 0064, ADR 0070).
 *
 * A merged customer is RETAINED with status `merged` and a survivor pointer so the merge stays
 * fully reversible; merging never deletes (ADR 0072).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int|null $primary_branch_id
 * @property string|null $display_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property CustomerStatus $status
 * @property int|null $merged_into_customer_id
 * @property Carbon|null $pii_purged_at
 * @property bool $legal_hold
 * @property Carbon|null $first_seen_at
 * @property Carbon|null $last_seen_at
 * @property int|null $created_by
 */
class Customer extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /** Columns that must never change once the row exists — identity provenance stays truthful. */
    private const IMMUTABLE = ['ulid', 'tenant_id'];

    protected $fillable = [
        // Stamped from TenantContext by BelongsToTenant, which rejects any mismatch — fillable is
        // the repo-wide convention for tenant-owned models and is enforced by a fitness function.
        'tenant_id',
        'primary_branch_id',
        'display_name',
        'contact_email',
        'contact_phone',
        'status',
        'first_seen_at',
        'last_seen_at',
        'created_by',
    ];

    /**
     * Merge bookkeeping, purge state, and legal hold are deliberately NOT fillable: they may only
     * be set by the owning services, never by mass assignment from a request (rule 36).
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if (empty($customer->ulid)) {
                $customer->ulid = (string) Str::ulid();
            }

            if ($customer->first_seen_at === null) {
                $customer->first_seen_at = now();
            }
        });

        static::updating(function (Customer $customer): void {
            foreach (self::IMMUTABLE as $column) {
                if ($customer->isDirty($column)) {
                    throw new \RuntimeException(
                        "Customer column [{$column}] is immutable and cannot be changed (rule 36)."
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'legal_hold' => 'boolean',
            'pii_purged_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'merged_into_customer_id');
    }

    /** @return HasMany<CustomerIdentity, $this> */
    public function identities(): HasMany
    {
        return $this->hasMany(CustomerIdentity::class);
    }

    /** @return HasMany<CustomerConsent, $this> */
    public function consents(): HasMany
    {
        return $this->hasMany(CustomerConsent::class);
    }

    /** @return HasMany<FeedbackItem, $this> */
    public function feedbackItems(): HasMany
    {
        return $this->hasMany(FeedbackItem::class);
    }

    public function isMerged(): bool
    {
        return $this->status === CustomerStatus::Merged;
    }

    /** A customer that may still receive new identity links or participate in a merge. */
    public function isLinkable(): bool
    {
        return $this->status->isLinkable();
    }

    /**
     * Contact PII is gated by `customer.view-contact`; callers that lack it must render this
     * instead of the raw value, so a view can never leak an address by omission (rule 36).
     */
    public function maskedContactEmail(): ?string
    {
        if ($this->contact_email === null) {
            return null;
        }

        $parts = explode('@', $this->contact_email, 2);

        if (count($parts) !== 2 || $parts[0] === '') {
            return '•••';
        }

        return mb_substr($parts[0], 0, 1).'•••@'.$parts[1];
    }

    public function maskedContactPhone(): ?string
    {
        if ($this->contact_phone === null) {
            return null;
        }

        return '•••'.mb_substr($this->contact_phone, -3);
    }
}
