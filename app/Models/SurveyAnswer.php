<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Database\Factories\SurveyAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A single answer within a response. Tenant-owned and write-once: answers are created inside
 * the submission transaction and never mutated (update is blocked at the model layer). Only
 * the representation required by the question type is stored (rule 32; Step 7 §19).
 *
 * @property int $id
 * @property string $ulid
 * @property int $tenant_id
 * @property int $response_id
 * @property int $question_id
 * @property int|null $option_id
 * @property int|null $numeric_value
 * @property bool|null $boolean_value
 * @property string|null $text_value
 */
class SurveyAnswer extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<SurveyAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'response_id',
        'question_id',
        'option_id',
        'numeric_value',
        'boolean_value',
        'text_value',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'integer',
            'boolean_value' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SurveyAnswer $answer): void {
            if (empty($answer->ulid)) {
                $answer->ulid = (string) Str::ulid();
            }
        });

        static::updating(fn () => throw new \RuntimeException(
            'Survey answers are write-once and cannot be mutated (rule 32).'
        ));
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** @return BelongsTo<SurveyResponse, $this> */
    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'response_id');
    }

    /** @return BelongsTo<SurveyQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }

    /** @return BelongsTo<SurveyOption, $this> */
    public function option(): BelongsTo
    {
        return $this->belongsTo(SurveyOption::class, 'option_id');
    }
}
