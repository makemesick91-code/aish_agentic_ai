<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Audit\AuditRecorder;
use App\Enums\SurveyStatus;
use App\Enums\SurveyVersionStatus;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\User;
use App\Surveys\Exceptions\SurveyStateException;
use Illuminate\Support\Facades\DB;

/**
 * Authoring operations for surveys, draft versions, questions, and options. All writes are
 * tenant-scoped (fail-closed via TenantScope) and audited. Content of a published version is
 * never mutated here — editing published content creates a NEW draft version (rule 32;
 * Step 7 §10-§12).
 */
final class SurveyService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Create a survey together with its first (draft) version.
     *
     * @param  array{name: string, description?: string|null, branch_id?: int|null, title?: string, introduction?: string|null, completion_message?: string|null, locale?: string, mode?: string}  $data
     */
    public function create(array $data, User $actor): Survey
    {
        return DB::transaction(function () use ($data, $actor): Survey {
            $survey = Survey::create([
                'branch_id' => $data['branch_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => SurveyStatus::Draft,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $version = $this->createDraftVersion($survey, [
                'version_number' => 1,
                'title' => $data['title'] ?? $data['name'],
                'introduction' => $data['introduction'] ?? null,
                'completion_message' => $data['completion_message'] ?? null,
                'locale' => $data['locale'] ?? 'id',
                'mode' => $data['mode'] ?? 'anonymous',
            ], $actor);

            $survey->forceFill(['current_version_id' => null])->save();

            $this->audit->record('survey.created', [
                'subject' => $survey,
                'actor_id' => $actor->id,
                'metadata' => ['survey_ulid' => $survey->ulid, 'version_number' => $version->version_number],
            ]);

            return $survey->fresh();
        });
    }

    /** The current editable draft version for a survey, or throw if none exists. */
    public function draftVersion(Survey $survey): SurveyVersion
    {
        $draft = $survey->versions()
            ->where('status', SurveyVersionStatus::Draft->value)
            ->orderByDesc('version_number')
            ->first();

        if ($draft === null) {
            throw SurveyStateException::message('This survey has no editable draft version.');
        }

        return $draft;
    }

    /** Add a question to a draft version. */
    public function addQuestion(SurveyVersion $version, array $data, User $actor): SurveyQuestion
    {
        $this->assertEditable($version);

        $question = DB::transaction(fn (): SurveyQuestion => SurveyQuestion::create([
            'survey_version_id' => $version->id,
            'question_key' => $data['question_key'],
            'type' => $data['type'],
            'prompt' => $data['prompt'],
            'help_text' => $data['help_text'] ?? null,
            'required' => $data['required'] ?? false,
            'display_order' => $data['display_order'],
            'scored' => $data['scored'] ?? false,
            'scoring_config' => $data['scoring_config'] ?? null,
            'validation_config' => $data['validation_config'] ?? null,
        ]));

        $this->audit->record('survey.question.created', [
            'subject' => $version,
            'actor_id' => $actor->id,
            'metadata' => ['question_key' => $question->question_key, 'type' => $question->type->value],
        ]);

        return $question;
    }

    /** Add an option to a question in a draft version. */
    public function addOption(SurveyQuestion $question, array $data, User $actor): SurveyOption
    {
        $this->assertEditable($question->version);

        $option = SurveyOption::create([
            'question_id' => $question->id,
            'option_key' => $data['option_key'],
            'label' => $data['label'],
            'value' => $data['value'],
            'display_order' => $data['display_order'],
            'score' => $data['score'] ?? null,
        ]);

        $this->audit->record('survey.option.changed', [
            'subject' => $question->version,
            'actor_id' => $actor->id,
            'metadata' => ['question_key' => $question->question_key, 'option_key' => $option->option_key],
        ]);

        return $option;
    }

    /** Pause a published survey. */
    public function pause(Survey $survey, User $actor): Survey
    {
        return $this->transition($survey, SurveyStatus::Paused, 'survey.paused', $actor);
    }

    /** Resume a paused survey. */
    public function resume(Survey $survey, User $actor): Survey
    {
        return $this->transition($survey, SurveyStatus::Published, 'survey.resumed', $actor);
    }

    /** Archive a survey (no new invitations afterward; history stays readable). */
    public function archive(Survey $survey, User $actor): Survey
    {
        return $this->transition($survey, SurveyStatus::Archived, 'survey.archived', $actor);
    }

    private function transition(Survey $survey, SurveyStatus $target, string $event, User $actor): Survey
    {
        if (! $survey->status->canTransitionTo($target)) {
            throw SurveyStateException::invalidTransition($survey->status->value, $target->value);
        }

        $survey->forceFill(['status' => $target, 'updated_by' => $actor->id])->save();

        $this->audit->record($event, [
            'subject' => $survey,
            'actor_id' => $actor->id,
            'metadata' => ['status' => $target->value],
        ]);

        return $survey->fresh();
    }

    /** @internal Create a draft version row (used by create() and the publisher's new-draft flow). */
    public function createDraftVersion(Survey $survey, array $data, User $actor): SurveyVersion
    {
        $version = SurveyVersion::create([
            'survey_id' => $survey->id,
            'version_number' => $data['version_number'],
            'status' => SurveyVersionStatus::Draft,
            'title' => $data['title'],
            'introduction' => $data['introduction'] ?? null,
            'completion_message' => $data['completion_message'] ?? null,
            'locale' => $data['locale'] ?? 'id',
            'mode' => $data['mode'] ?? 'anonymous',
        ]);

        $this->audit->record('survey.version.created', [
            'subject' => $survey,
            'actor_id' => $actor->id,
            'metadata' => ['version_number' => $version->version_number],
        ]);

        return $version;
    }

    private function assertEditable(SurveyVersion $version): void
    {
        if (! $version->isEditable()) {
            throw SurveyStateException::notEditable();
        }
    }
}
