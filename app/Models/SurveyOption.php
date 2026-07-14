<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A selectable option for a choice question. Tenant-owned. Frozen once the owning version is
 * published (rule 32; Step 7 §12).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $question_id
 * @property string $option_key
 * @property string $label
 * @property string $value
 * @property int $display_order
 * @property int|null $score
 */
class SurveyOption extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'question_id',
        'option_key',
        'label',
        'value',
        'display_order',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyOption $option): void {
            if (empty($option->ulid)) {
                $option->ulid = (string) Str::ulid();
            }
        });

        static::updating(fn (SurveyOption $o) => $o->guardVersionEditable());
        static::deleting(fn (SurveyOption $o) => $o->guardVersionEditable());
    }

    private function guardVersionEditable(): void
    {
        $versionId = DB::table('survey_questions')
            ->where('id', $this->question_id)
            ->value('survey_version_id');

        $status = $versionId === null ? null : DB::table('survey_versions')
            ->where('id', $versionId)
            ->value('status');

        if ($status !== null && $status !== 'draft') {
            throw new \RuntimeException(
                'An option in a published survey version cannot be modified (rule 32).'
            );
        }
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<SurveyQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }
}
