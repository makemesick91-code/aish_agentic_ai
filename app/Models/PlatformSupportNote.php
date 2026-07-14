<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlatformSupportNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Append-oriented platform support note about a tenant. Update/delete blocked at the model
 * layer; no updated_at (rule 31 §10.9).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int|null $author_id
 * @property string $body
 * @property Carbon|null $created_at
 */
class PlatformSupportNote extends Model
{
    /** @use HasFactory<PlatformSupportNoteFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'author_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PlatformSupportNote $note): void {
            if (empty($note->ulid)) {
                $note->ulid = (string) Str::ulid();
            }
        });

        static::updating(fn () => throw new \RuntimeException('Support notes are append-only and cannot be updated.'));
        static::deleting(fn () => throw new \RuntimeException('Support notes are append-only and cannot be deleted.'));
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

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
