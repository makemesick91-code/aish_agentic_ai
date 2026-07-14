<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Enums\CampaignStatus;
use App\Enums\InvitationStatus;
use App\Enums\ResponseStatus;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Surveys\CampaignService;
use App\Surveys\Exceptions\InvalidSurveyLinkException;
use App\Surveys\Exceptions\ResponseValidationException;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\PublicSurveyGateway;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\TestCase;

final class PublicSurveyFlowTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsSurveyPlan;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->establishTenantContext($this->tenant);
        $this->actor = User::factory()->create();
        $this->provisionSurveyPlan($this->tenant);
    }

    /** @return array{0: Survey, 1: SurveyVersion} */
    private function publishedSurvey(): array
    {
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'Kepuasan', 'completion_message' => 'Terima kasih'], $this->actor);
        $draft = $svc->draftVersion($survey);

        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $this->actor);
        $svc->addQuestion($draft, ['question_key' => 'nps', 'type' => 'nps', 'prompt' => 'Rekomendasi?', 'required' => true, 'display_order' => 2, 'scored' => true, 'scoring_config' => ['scale_min' => 0, 'scale_max' => 10, 'direction' => 'higher_is_better']], $this->actor);
        $q = $svc->addQuestion($draft, ['question_key' => 'branch', 'type' => 'single_choice', 'prompt' => 'Cabang?', 'required' => true, 'display_order' => 3], $this->actor);
        $svc->addOption($q, ['option_key' => 'pusat', 'label' => 'Pusat', 'value' => 'pusat', 'display_order' => 1], $this->actor);
        $svc->addOption($q, ['option_key' => 'cabang', 'label' => 'Cabang', 'value' => 'cabang', 'display_order' => 2], $this->actor);
        $svc->addQuestion($draft, ['question_key' => 'comment', 'type' => 'long_text', 'prompt' => 'Komentar?', 'required' => false, 'display_order' => 4, 'validation_config' => ['max_length' => 500]], $this->actor);

        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $this->actor);

        return [$survey->fresh(), $version];
    }

    /** @return array<string, mixed> */
    private function validAnswers(): array
    {
        return ['csat' => 5, 'nps' => 9, 'branch' => 'pusat', 'comment' => 'Bagus'];
    }

    public function test_campaign_binds_a_published_version_and_activates(): void
    {
        [$survey, $version] = $this->publishedSurvey();

        $campaign = app(CampaignService::class)->create($survey, $version, ['name' => 'Juli'], $this->actor);
        $this->assertSame(CampaignStatus::Draft, $campaign->status);
        $this->assertNotEmpty($campaign->public_id);

        $active = app(CampaignService::class)->activate($campaign, $this->actor);
        $this->assertSame(CampaignStatus::Active, $active->status);
    }

    public function test_campaign_rejects_a_draft_version(): void
    {
        [$survey] = $this->publishedSurvey();
        $draft = SurveyVersion::factory()->create(['survey_id' => $survey->id, 'version_number' => 99]);

        $this->expectException(SurveyStateException::class);
        app(CampaignService::class)->create($survey, $draft, ['name' => 'X'], $this->actor);
    }

    public function test_issue_invitation_is_idempotent_and_stores_only_a_hash(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );

        $svc = app(SurveyInvitationService::class);
        $first = $svc->issue($campaign, ['idempotency_key' => 'k-1', 'recipient_email' => 'pasien@example.com'], $this->actor);
        $second = $svc->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);

        $this->assertSame($first->invitation->id, $second->invitation->id);
        $this->assertNotNull($first->plainToken);
        $this->assertNull($second->plainToken); // idempotent repeat reveals no token
        // Only the hash is stored; it never equals the plaintext.
        $this->assertSame(hash('sha256', $first->plainToken), $first->invitation->token_hash);
        $this->assertNotSame($first->plainToken, $first->invitation->token_hash);
        $this->assertSame(64, strlen($first->invitation->token_hash));
    }

    public function test_public_campaign_submission_records_response_answers_usage_and_audit(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $this->forgetTenantContext();

        $response = app(PublicSurveyGateway::class)->submitForCampaign($campaign->public_id, $this->validAnswers(), ['ip_hash' => 'abc']);

        $this->establishTenantContext($this->tenant);
        $fresh = SurveyResponse::findOrFail($response->id);
        $this->assertSame(ResponseStatus::Completed, $fresh->status);
        $this->assertSame($version->id, $fresh->survey_version_id);
        $this->assertSame(4, SurveyAnswer::where('response_id', $fresh->id)->count()); // csat, nps, branch, comment
        $this->assertSame(1, app(UsageMeter::class)->total($this->tenant, MeterKeys::SURVEY_RESPONSES_COMPLETED));
        $this->assertDatabaseHas('audit_logs', ['event' => 'survey.response.completed', 'tenant_id' => $this->tenant->id]);
        // No answer content leaked into audit metadata.
        $audit = AuditLog::where('event', 'survey.response.completed')->first();
        $this->assertStringNotContainsString('Bagus', json_encode($audit->metadata));
    }

    public function test_invitation_submission_is_one_time(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);
        $token = $issued->plainToken;
        $publicId = $issued->invitation->public_id;
        $this->forgetTenantContext();

        $gateway = app(PublicSurveyGateway::class);
        $gateway->submitForInvitation($publicId, $token, $this->validAnswers());

        $this->expectException(InvalidSurveyLinkException::class);
        $gateway->submitForInvitation($publicId, $token, $this->validAnswers());
    }

    public function test_completed_invitation_marks_status_completed(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);
        $this->forgetTenantContext();

        app(PublicSurveyGateway::class)->submitForInvitation($issued->invitation->public_id, $issued->plainToken, $this->validAnswers());

        $this->establishTenantContext($this->tenant);
        $this->assertSame(InvitationStatus::Completed, $issued->invitation->fresh()->status);
    }

    public function test_an_invalid_token_is_rejected_generically(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);
        $this->forgetTenantContext();

        $this->expectException(InvalidSurveyLinkException::class);
        app(PublicSurveyGateway::class)->submitForInvitation($issued->invitation->public_id, 'wrong-token', $this->validAnswers());
    }

    public function test_a_revoked_invitation_cannot_be_used(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);
        app(SurveyInvitationService::class)->revoke($issued->invitation, $this->actor);
        $this->forgetTenantContext();

        $this->expectException(InvalidSurveyLinkException::class);
        app(PublicSurveyGateway::class)->submitForInvitation($issued->invitation->public_id, $issued->plainToken, $this->validAnswers());
    }

    public function test_response_validation_rejects_unknown_and_out_of_range(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $this->forgetTenantContext();

        $gateway = app(PublicSurveyGateway::class);

        // Out-of-range CSAT (scale 1-5).
        try {
            $gateway->submitForCampaign($campaign->public_id, ['csat' => 9, 'nps' => 9, 'branch' => 'pusat']);
            $this->fail('Expected validation to reject out-of-range CSAT.');
        } catch (ResponseValidationException $e) {
            $this->assertArrayHasKey('csat', $e->errors);
        }

        // Unknown question + invalid option.
        try {
            $gateway->submitForCampaign($campaign->public_id, ['csat' => 5, 'nps' => 9, 'branch' => 'ghost', 'unknown' => 1]);
            $this->fail('Expected validation to reject unknown question and invalid option.');
        } catch (ResponseValidationException $e) {
            $this->assertArrayHasKey('unknown', $e->errors);
            $this->assertArrayHasKey('branch', $e->errors);
        }
    }

    public function test_a_missing_required_answer_is_rejected(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $this->forgetTenantContext();

        $this->expectException(ResponseValidationException::class);
        app(PublicSurveyGateway::class)->submitForCampaign($campaign->public_id, ['csat' => 5, 'nps' => 9]); // missing required 'branch'
    }
}
