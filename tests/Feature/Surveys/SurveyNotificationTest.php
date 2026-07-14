<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Authorization\Roles;
use App\Enums\InvitationStatus;
use App\Mail\SurveyInvitationMail;
use App\Models\Survey;
use App\Models\SurveyInvitation;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\CampaignService;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\PublicSurveyGateway;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class SurveyNotificationTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsSurveyPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->provisionTenant();
        [$this->actor] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($this->tenant);
        $this->provisionSurveyPlan($this->tenant);
    }

    /** @return array{0: Survey, 1: SurveyVersion} */
    private function publishedSurvey(): array
    {
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $this->actor);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $this->actor);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $this->actor);

        return [$survey->fresh(), $version];
    }

    public function test_invitation_delivery_sends_mail_and_marks_sent_without_storing_the_token(): void
    {
        Mail::fake();
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1', 'recipient_email' => 'pasien@example.com'], $this->actor);

        $url = 'https://app.example/s/i/'.$issued->invitation->public_id.'/'.$issued->plainToken;
        $delivered = app(SurveyInvitationService::class)->deliver($issued->invitation, $url, $this->actor);

        Mail::assertSent(SurveyInvitationMail::class, fn (SurveyInvitationMail $m) => $m->hasTo('pasien@example.com'));
        $this->assertSame(InvitationStatus::Sent, $delivered->status);

        // The plaintext token is never persisted anywhere on the invitation row.
        $row = SurveyInvitation::withoutGlobalScopes()->findOrFail($issued->invitation->id)->getAttributes();
        $this->assertNotContains($issued->plainToken, array_map('strval', array_values($row)));
        $this->assertDatabaseHas('audit_logs', ['event' => 'survey.invitation.delivery_requested']);
    }

    public function test_response_completion_notifies_the_campaign_owner_internally(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $publicId = $campaign->public_id;
        $this->forgetTenantContext();

        app(PublicSurveyGateway::class)->submitForCampaign($publicId, ['csat' => 5]);

        $this->establishTenantContext($this->tenant);
        $this->assertDatabaseHas('notification_deliveries', [
            'tenant_id' => $this->tenant->id,
            'recipient_id' => $this->actor->id,
            'type' => 'survey.response.completed.internal',
        ]);
    }

    public function test_delivery_without_a_recipient_email_is_rejected(): void
    {
        [$survey, $version] = $this->publishedSurvey();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k-1'], $this->actor);

        $this->expectException(SurveyStateException::class);
        app(SurveyInvitationService::class)->deliver($issued->invitation, 'https://x', $this->actor);
    }
}
