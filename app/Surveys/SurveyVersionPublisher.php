<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Audit\AuditRecorder;
use App\Enums\QuestionType;
use App\Enums\ScoreDirection;
use App\Enums\SurveyStatus;
use App\Enums\SurveyVersionStatus;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\User;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\Exceptions\SurveyValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Validates and publishes a survey version, and derives a new draft from a published version.
 * Publishing is transactional and race-safe (the survey row is locked so concurrent publishes
 * serialize and cannot create two current versions). Once published, content is immutable
 * (rule 32; Step 7 §11, ADR 0057).
 */
final class SurveyVersionPublisher
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Validate a draft version without side effects.
     *
     * @return list<string> validation errors (empty when publishable)
     */
    public function validate(SurveyVersion $version): array
    {
        $errors = [];

        $survey = $version->survey;
        if ($survey !== null && $survey->status === SurveyStatus::Archived) {
            $errors[] = 'the survey is archived and cannot publish a new version';
        }

        /** @var \Illuminate\Support\Collection<int, SurveyQuestion> $questions */
        $questions = $version->questions()->with('options')->orderBy('display_order')->get();

        if ($questions->isEmpty()) {
            $errors[] = 'at least one question is required';
        }

        $seenOrders = [];
        $seenKeys = [];
        foreach ($questions as $question) {
            if (in_array($question->display_order, $seenOrders, true)) {
                $errors[] = "duplicate display order {$question->display_order}";
            }
            $seenOrders[] = $question->display_order;

            if (in_array($question->question_key, $seenKeys, true)) {
                $errors[] = "duplicate question key {$question->question_key}";
            }
            $seenKeys[] = $question->question_key;

            $type = $question->type;

            if ($type->usesOptions() && $question->options->count() < 2) {
                $errors[] = "choice question '{$question->question_key}' needs at least two options";
            }

            if ($type->usesNumericScale()) {
                $errors = array_merge($errors, $this->validateScale($type, $question->scoring_config ?? [], $question->question_key));
            }
        }

        return $errors;
    }

    /**
     * Publish a draft version. Idempotent: publishing an already-published version returns it.
     */
    public function publish(SurveyVersion $version, User $actor): SurveyVersion
    {
        return DB::transaction(function () use ($version, $actor): SurveyVersion {
            // Lock the survey row so concurrent publishes on the same survey serialize.
            $survey = Survey::whereKey($version->survey_id)->lockForUpdate()->firstOrFail();
            $locked = SurveyVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === SurveyVersionStatus::Published) {
                return $locked; // idempotent no-op
            }

            if ($locked->status !== SurveyVersionStatus::Draft) {
                throw SurveyStateException::message('Only a draft version can be published.');
            }

            $errors = $this->validate($locked);
            if ($errors !== []) {
                throw new SurveyValidationException($errors);
            }

            // Supersede the previously-current published version (status-only change is allowed).
            if ($survey->current_version_id !== null) {
                $previous = SurveyVersion::whereKey($survey->current_version_id)->first();
                if ($previous !== null && $previous->status === SurveyVersionStatus::Published) {
                    $previous->forceFill(['status' => SurveyVersionStatus::Superseded])->save();
                }
            }

            $locked->forceFill([
                'status' => SurveyVersionStatus::Published,
                'published_at' => now(),
                'published_by' => $actor->id,
            ])->save();

            $survey->forceFill([
                'current_version_id' => $locked->id,
                'status' => SurveyStatus::Published,
                'updated_by' => $actor->id,
            ])->save();

            $this->audit->record('survey.version.published', [
                'subject' => $survey,
                'actor_id' => $actor->id,
                'metadata' => [
                    'version_number' => $locked->version_number,
                    'version_ulid' => $locked->ulid,
                ],
            ]);

            return $locked->fresh();
        });
    }

    /**
     * Derive a new editable draft version from the survey's current published version, copying
     * its questions and options. This is how published content is "edited".
     */
    public function newDraftFrom(SurveyVersion $publishedVersion, User $actor): SurveyVersion
    {
        if ($publishedVersion->status === SurveyVersionStatus::Draft) {
            throw SurveyStateException::message('The version is already an editable draft.');
        }

        return DB::transaction(function () use ($publishedVersion, $actor): SurveyVersion {
            $survey = Survey::whereKey($publishedVersion->survey_id)->lockForUpdate()->firstOrFail();

            $nextNumber = (int) SurveyVersion::where('survey_id', $survey->id)->max('version_number') + 1;

            $draft = SurveyVersion::create([
                'survey_id' => $survey->id,
                'version_number' => $nextNumber,
                'status' => SurveyVersionStatus::Draft,
                'title' => $publishedVersion->title,
                'introduction' => $publishedVersion->introduction,
                'completion_message' => $publishedVersion->completion_message,
                'locale' => $publishedVersion->locale,
                'mode' => $publishedVersion->mode,
            ]);

            foreach ($publishedVersion->questions()->with('options')->orderBy('display_order')->get() as $question) {
                $newQuestion = SurveyQuestion::create([
                    'survey_version_id' => $draft->id,
                    'question_key' => $question->question_key,
                    'type' => $question->type,
                    'prompt' => $question->prompt,
                    'help_text' => $question->help_text,
                    'required' => $question->required,
                    'display_order' => $question->display_order,
                    'scored' => $question->scored,
                    'scoring_config' => $question->scoring_config,
                    'validation_config' => $question->validation_config,
                ]);

                foreach ($question->options as $option) {
                    SurveyOption::create([
                        'question_id' => $newQuestion->id,
                        'option_key' => $option->option_key,
                        'label' => $option->label,
                        'value' => $option->value,
                        'display_order' => $option->display_order,
                        'score' => $option->score,
                    ]);
                }
            }

            $this->audit->record('survey.version.created', [
                'subject' => $survey,
                'actor_id' => $actor->id,
                'metadata' => ['version_number' => $nextNumber, 'derived_from' => $publishedVersion->version_number],
            ]);

            return $draft->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function validateScale(QuestionType $type, array $config, string $key): array
    {
        $errors = [];

        // NPS is a fixed 0-10 scale.
        if ($type === QuestionType::Nps) {
            $min = $config['scale_min'] ?? 0;
            $max = $config['scale_max'] ?? 10;
            if ((int) $min !== 0 || (int) $max !== 10) {
                $errors[] = "NPS question '{$key}' must use the fixed 0-10 scale";
            }

            return $errors;
        }

        if (! isset($config['scale_min'], $config['scale_max'])) {
            return ["scored question '{$key}' is missing its scale configuration"];
        }

        $min = (int) $config['scale_min'];
        $max = (int) $config['scale_max'];

        if ($min >= $max) {
            $errors[] = "scored question '{$key}' has an invalid scale range";
        }

        if ($type === QuestionType::Csat) {
            if (! isset($config['satisfied_threshold'])) {
                $errors[] = "CSAT question '{$key}' is missing its satisfied threshold";
            } else {
                $threshold = (int) $config['satisfied_threshold'];
                if ($threshold < $min || $threshold > $max) {
                    $errors[] = "CSAT question '{$key}' threshold is outside the scale";
                }
            }
        }

        if (isset($config['direction']) && ScoreDirection::tryFrom((string) $config['direction']) === null) {
            $errors[] = "scored question '{$key}' has an invalid direction";
        }

        return $errors;
    }
}
