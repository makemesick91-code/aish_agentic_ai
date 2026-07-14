<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Authorization\Roles;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\CampaignService;
use App\Surveys\PublicSurveyGateway;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Step 7 audit coverage: security-relevant survey actions are audited with actor + tenant, and
 * audit metadata never carries invitation tokens or free-text answer content (rule 32; Step 7
 * §25).
 */
final class Sf07AuditTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsSurveyPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private string $secretComment = 'RAHASIA_KELUHAN_MEDIS';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->provisionTenant();
        $this->provisionSurveyPlan($this->tenant);
        [$this->owner] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($this->tenant);
    }

    private function runFullFlow(): void
    {
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $this->owner);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $this->owner);
        $svc->addQuestion($draft, ['question_key' => 'comment', 'type' => 'long_text', 'prompt' => 'Komentar?', 'required' => false, 'display_order' => 2, 'validation_config' => ['max_length' => 500]], $this->owner);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $this->owner);
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey->fresh(), $version, ['name' => 'C'], $this->owner),
            $this->owner,
        );
        app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k1', 'recipient_email' => 'p@example.com'], $this->owner);
        $this->forgetTenantContext();

        app(PublicSurveyGateway::class)->submitForCampaign($campaign->public_id, ['csat' => 5, 'comment' => $this->secretComment]);
        $this->establishTenantContext($this->tenant);
    }

    public function test_survey_lifecycle_writes_the_expected_audit_events(): void
    {
        $this->runFullFlow();

        foreach ([
            'survey.created',
            'survey.version.published',
            'survey.campaign.created',
            'survey.campaign.activated',
            'survey.invitation.created',
            'survey.response.completed',
        ] as $event) {
            $this->assertDatabaseHas('audit_logs', ['event' => $event, 'tenant_id' => $this->tenant->id]);
        }
    }

    public function test_audit_records_the_actor_and_tenant(): void
    {
        $this->runFullFlow();

        $created = AuditLog::where('event', 'survey.created')->firstOrFail();
        $this->assertSame($this->owner->id, $created->actor_id);
        $this->assertSame($this->tenant->id, $created->tenant_id);
    }

    public function test_no_free_text_answer_or_token_appears_in_any_audit_metadata(): void
    {
        $this->runFullFlow();

        foreach (AuditLog::all() as $log) {
            $serialized = json_encode($log->metadata);
            $this->assertStringNotContainsString($this->secretComment, (string) $serialized, 'Answer content must never reach audit metadata.');
        }

        // The invitation audit carries only the opaque public id, never a token/hash.
        $invitationAudit = AuditLog::where('event', 'survey.invitation.created')->firstOrFail();
        $this->assertArrayHasKey('invitation_public_id', $invitationAudit->metadata);
        $this->assertArrayNotHasKey('token', $invitationAudit->metadata);
        $this->assertArrayNotHasKey('token_hash', $invitationAudit->metadata);
    }
}
