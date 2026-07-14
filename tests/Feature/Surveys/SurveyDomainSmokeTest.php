<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Enums\ResponseStatus;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class SurveyDomainSmokeTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    public function test_the_full_survey_hierarchy_is_tenant_scoped(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        $this->establishTenantContext($a);
        $survey = Survey::factory()->create(['name' => 'Kepuasan Pasien']);
        $version = SurveyVersion::factory()->create(['survey_id' => $survey->id]);
        $question = SurveyQuestion::factory()->csat()->create(['survey_version_id' => $version->id]);

        $this->assertSame($a->id, $survey->tenant_id);
        $this->assertSame($a->id, $question->tenant_id);
        $this->assertSame(1, Survey::query()->count());

        $this->forgetTenantContext();
        $this->establishTenantContext($b);
        $this->assertSame(0, Survey::query()->count());
    }

    public function test_published_version_content_is_immutable(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $version = SurveyVersion::factory()->published()->create();
        $question = SurveyQuestion::factory()->csat()->create(['survey_version_id' => $version->id]);
        $option = SurveyOption::factory()->create([
            'question_id' => SurveyQuestion::factory()->singleChoice()->create(['survey_version_id' => $version->id])->id,
        ]);

        // Version content frozen.
        try {
            $version->update(['title' => 'Changed']);
            $this->fail('Expected published version title to be immutable.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Question in a published version frozen.
        $this->expectException(\RuntimeException::class);
        $question->update(['prompt' => 'Changed?']);
    }

    public function test_published_version_options_are_frozen(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $version = SurveyVersion::factory()->published()->create();
        $question = SurveyQuestion::factory()->singleChoice()->create(['survey_version_id' => $version->id]);
        $option = SurveyOption::factory()->create(['question_id' => $question->id]);

        $this->expectException(\RuntimeException::class);
        $option->update(['label' => 'Changed']);
    }

    public function test_a_draft_version_content_is_editable(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $version = SurveyVersion::factory()->create();
        $question = SurveyQuestion::factory()->csat()->create(['survey_version_id' => $version->id]);

        $version->update(['title' => 'Editable title']);
        $question->update(['prompt' => 'Editable?']);

        $this->assertSame('Editable title', $version->fresh()->title);
        $this->assertSame('Editable?', $question->fresh()->prompt);
    }

    public function test_answers_are_write_once(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $response = SurveyResponse::factory()->create();
        $question = SurveyQuestion::factory()->csat()->create([
            'survey_version_id' => $response->survey_version_id,
        ]);
        $answer = SurveyAnswer::factory()->numeric(4)->create([
            'response_id' => $response->id,
            'question_id' => $question->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $answer->update(['numeric_value' => 1]);
    }

    public function test_a_completed_response_is_immutable_except_authorized_invalidation(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $response = SurveyResponse::factory()->completed()->create();

        // Authorized invalidation is permitted.
        $response->update([
            'status' => ResponseStatus::Invalidated,
            'invalidated_at' => now(),
            'invalidated_reason' => 'duplicate',
        ]);
        $this->assertSame(ResponseStatus::Invalidated, $response->fresh()->status);

        // A different completed response cannot mutate arbitrary fields.
        $other = SurveyResponse::factory()->completed()->create();
        $this->expectException(\RuntimeException::class);
        $other->update(['locale' => 'en']);
    }

    public function test_a_published_survey_cannot_be_hard_deleted(): void
    {
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);

        $survey = Survey::factory()->published()->create();

        $this->expectException(\RuntimeException::class);
        $survey->delete();
    }
}
