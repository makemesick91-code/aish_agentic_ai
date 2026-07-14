<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Enums\SurveyStatus;
use App\Enums\SurveyVersionStatus;
use App\Models\Survey;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\Exceptions\SurveyValidationException;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class SurveyLifecycleTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);
        $this->actor = User::factory()->create();
    }

    private function service(): SurveyService
    {
        return app(SurveyService::class);
    }

    private function publisher(): SurveyVersionPublisher
    {
        return app(SurveyVersionPublisher::class);
    }

    private function buildPublishableDraft(): SurveyVersion
    {
        $survey = $this->service()->create(['name' => 'Kepuasan'], $this->actor);
        $draft = $this->service()->draftVersion($survey);

        $this->service()->addQuestion($draft, [
            'question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true,
            'display_order' => 1, 'scored' => true,
            'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better'],
        ], $this->actor);

        $this->service()->addQuestion($draft, [
            'question_key' => 'nps', 'type' => 'nps', 'prompt' => 'Rekomendasi?', 'required' => true,
            'display_order' => 2, 'scored' => true,
            'scoring_config' => ['scale_min' => 0, 'scale_max' => 10, 'direction' => 'higher_is_better'],
        ], $this->actor);

        $this->service()->addQuestion($draft, [
            'question_key' => 'ces', 'type' => 'ces', 'prompt' => 'Mudah?', 'required' => true,
            'display_order' => 3, 'scored' => true,
            'scoring_config' => ['scale_min' => 1, 'scale_max' => 7, 'direction' => 'higher_is_better'],
        ], $this->actor);

        return $draft->fresh();
    }

    public function test_create_makes_a_draft_survey_with_a_first_version(): void
    {
        $survey = $this->service()->create(['name' => 'Kepuasan Pasien'], $this->actor);

        $this->assertSame(SurveyStatus::Draft, $survey->status);
        $this->assertSame(1, $survey->versions()->count());
        $this->assertSame(SurveyVersionStatus::Draft, $survey->versions()->first()->status);
    }

    public function test_a_valid_version_publishes_and_becomes_current(): void
    {
        $draft = $this->buildPublishableDraft();

        $published = $this->publisher()->publish($draft, $this->actor);

        $this->assertSame(SurveyVersionStatus::Published, $published->status);
        $this->assertNotNull($published->published_at);

        $survey = $draft->survey->fresh();
        $this->assertSame(SurveyStatus::Published, $survey->status);
        $this->assertSame($published->id, $survey->current_version_id);
    }

    public function test_publishing_a_version_without_questions_is_rejected(): void
    {
        $survey = $this->service()->create(['name' => 'Empty'], $this->actor);
        $draft = $this->service()->draftVersion($survey);

        $this->expectException(SurveyValidationException::class);
        $this->publisher()->publish($draft, $this->actor);
    }

    public function test_publishing_with_an_invalid_csat_threshold_is_rejected(): void
    {
        $survey = $this->service()->create(['name' => 'Bad'], $this->actor);
        $draft = $this->service()->draftVersion($survey);
        $this->service()->addQuestion($draft, [
            'question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'display_order' => 1, 'scored' => true,
            'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 9, 'direction' => 'higher_is_better'],
        ], $this->actor);

        $this->expectException(SurveyValidationException::class);
        $this->publisher()->publish($draft->fresh(), $this->actor);
    }

    public function test_a_choice_question_needs_at_least_two_options_to_publish(): void
    {
        $survey = $this->service()->create(['name' => 'Choice'], $this->actor);
        $draft = $this->service()->draftVersion($survey);
        $question = $this->service()->addQuestion($draft, [
            'question_key' => 'branch', 'type' => 'single_choice', 'prompt' => 'Cabang?', 'display_order' => 1,
        ], $this->actor);
        $this->service()->addOption($question, ['option_key' => 'a', 'label' => 'Pusat', 'value' => 'pusat', 'display_order' => 1], $this->actor);

        $errors = $this->publisher()->validate($draft->fresh());
        $this->assertNotEmpty($errors);
    }

    public function test_publish_is_idempotent(): void
    {
        $draft = $this->buildPublishableDraft();
        $first = $this->publisher()->publish($draft, $this->actor);
        $second = $this->publisher()->publish($draft->fresh(), $this->actor);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, SurveyVersion::where('survey_id', $draft->survey_id)->where('status', SurveyVersionStatus::Published->value)->count());
    }

    public function test_editing_published_content_creates_a_new_draft_version(): void
    {
        $draft = $this->buildPublishableDraft();
        $published = $this->publisher()->publish($draft, $this->actor);

        $newDraft = $this->publisher()->newDraftFrom($published, $this->actor);

        $this->assertSame(SurveyVersionStatus::Draft, $newDraft->status);
        $this->assertSame(2, $newDraft->version_number);
        $this->assertSame(3, $newDraft->questions()->count()); // copied
        // The published version is still current until the new draft is published.
        $this->assertSame($published->id, $draft->survey->fresh()->current_version_id);
    }

    public function test_publishing_a_second_version_supersedes_the_first(): void
    {
        $draft = $this->buildPublishableDraft();
        $v1 = $this->publisher()->publish($draft, $this->actor);
        $draft2 = $this->publisher()->newDraftFrom($v1, $this->actor);
        $v2 = $this->publisher()->publish($draft2, $this->actor);

        $this->assertSame(SurveyVersionStatus::Superseded, $v1->fresh()->status);
        $this->assertSame(SurveyVersionStatus::Published, $v2->fresh()->status);
        $this->assertSame($v2->id, $draft->survey->fresh()->current_version_id);
    }

    public function test_a_published_survey_can_be_paused_and_resumed(): void
    {
        $draft = $this->buildPublishableDraft();
        $this->publisher()->publish($draft, $this->actor);
        $survey = $draft->survey->fresh();

        $this->service()->pause($survey, $this->actor);
        $this->assertSame(SurveyStatus::Paused, $survey->fresh()->status);

        $this->service()->resume($survey->fresh(), $this->actor);
        $this->assertSame(SurveyStatus::Published, $survey->fresh()->status);
    }

    public function test_an_archived_survey_cannot_be_paused(): void
    {
        $survey = $this->service()->create(['name' => 'Arc'], $this->actor);
        $this->service()->archive($survey, $this->actor);

        $this->expectException(SurveyStateException::class);
        $this->service()->pause($survey->fresh(), $this->actor);
    }

    public function test_cannot_add_a_question_to_a_published_version(): void
    {
        $draft = $this->buildPublishableDraft();
        $published = $this->publisher()->publish($draft, $this->actor);

        $this->expectException(SurveyStateException::class);
        $this->service()->addQuestion($published, [
            'question_key' => 'x', 'type' => 'short_text', 'prompt' => 'X', 'display_order' => 9,
        ], $this->actor);
    }
}
