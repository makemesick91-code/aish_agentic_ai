<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A question within a survey version. Tenant-owned. Content is frozen once the owning version
 * is published: update/delete are blocked at the model layer while the version is not a draft
 * (defence in depth over the service-layer guard) (rule 32; Step 7 §12).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $survey_version_id
 * @property string $question_key
 * @property QuestionType $type
 * @property string $prompt
 * @property string|null $help_text
 * @property bool $required
 * @property int $display_order
 * @property bool $scored
 * @property array<string, mixed>|null $scoring_config
 * @property array<string, mixed>|null $validation_config
 */
class SurveyQuestion extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'survey_version_id',
        'question_key',
        'type',
        'prompt',
        'help_text',
        'required',
        'display_order',
        'scored',
        'scoring_config',
        'validation_config',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'required' => 'boolean',
            'scored' => 'boolean',
            'scoring_config' => 'array',
            'validation_config' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyQuestion $question): void {
            if (empty($question->ulid)) {
                $question->ulid = (string) Str::ulid();
            }
        });

        static::updating(fn (SurveyQuestion $q) => $q->guardVersionEditable());
        static::deleting(fn (SurveyQuestion $q) => $q->guardVersionEditable());
    }

    /** Throw if the owning version is no longer a draft (published content is immutable). */
    private function guardVersionEditable(): void
    {
        $status = DB::table('survey_versions')
            ->where('id', $this->survey_version_id)
            ->value('status');

        if ($status !== null && $status !== 'draft') {
            throw new \RuntimeException(
                'A question in a published survey version cannot be modified (rule 32).'
            );
        }
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<SurveyVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(SurveyVersion::class, 'survey_version_id');
    }

    /** @return HasMany<SurveyOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(SurveyOption::class, 'question_id')->orderBy('display_order');
    }
}
