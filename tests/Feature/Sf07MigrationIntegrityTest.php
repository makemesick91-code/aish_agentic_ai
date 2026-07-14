<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SurveyInvitation;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

/**
 * Step 7 schema integrity: the constraints that enforce immutable versioning, answer integrity,
 * one-time invitations, and idempotency exist and bite at the PostgreSQL layer — not only in PHP
 * (rule 32; Step 7 §26).
 */
final class Sf07MigrationIntegrityTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->establishTenantContext(Tenant::factory()->create());
    }

    public function test_survey_version_number_is_unique_per_survey(): void
    {
        $version = SurveyVersion::factory()->create(['version_number' => 1]);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyVersion::factory()->create(['survey_id' => $version->survey_id, 'version_number' => 1]);
    }

    public function test_question_key_is_unique_per_version(): void
    {
        $q = SurveyQuestion::factory()->create(['question_key' => 'csat']);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyQuestion::factory()->create(['survey_version_id' => $q->survey_version_id, 'question_key' => 'csat', 'display_order' => 99]);
    }

    public function test_question_display_order_is_unique_per_version(): void
    {
        $q = SurveyQuestion::factory()->create(['display_order' => 5]);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyQuestion::factory()->create(['survey_version_id' => $q->survey_version_id, 'display_order' => 5]);
    }

    public function test_option_key_is_unique_per_question(): void
    {
        $opt = SurveyOption::factory()->create(['option_key' => 'a']);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyOption::factory()->create(['question_id' => $opt->question_id, 'option_key' => 'a', 'display_order' => 99]);
    }

    public function test_invitation_idempotency_key_is_unique_per_tenant(): void
    {
        $inv = SurveyInvitation::factory()->create(['idempotency_key' => 'k1']);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyInvitation::factory()->create(['idempotency_key' => 'k1']);
    }

    public function test_invitation_token_hash_is_globally_unique(): void
    {
        $hash = hash('sha256', 'secret');
        SurveyInvitation::factory()->create(['token_hash' => $hash]);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyInvitation::factory()->create(['token_hash' => $hash]);
    }

    public function test_only_one_completed_response_per_invitation(): void
    {
        $inv = SurveyInvitation::factory()->create();
        SurveyResponse::factory()->completed()->create(['invitation_id' => $inv->id]);

        $this->expectException(UniqueConstraintViolationException::class);
        SurveyResponse::factory()->completed()->create(['invitation_id' => $inv->id]);
    }

    public function test_multiple_incomplete_responses_per_invitation_are_allowed(): void
    {
        $inv = SurveyInvitation::factory()->create();
        SurveyResponse::factory()->create(['invitation_id' => $inv->id, 'status' => 'started']);
        SurveyResponse::factory()->create(['invitation_id' => $inv->id, 'status' => 'started']);

        $this->assertSame(2, SurveyResponse::where('invitation_id', $inv->id)->count());
    }

    public function test_all_step7_tables_exist(): void
    {
        foreach ([
            'surveys', 'survey_versions', 'survey_questions', 'survey_options',
            'survey_campaigns', 'survey_invitations', 'survey_responses', 'survey_answers',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "missing table {$table}");
        }
    }
}
