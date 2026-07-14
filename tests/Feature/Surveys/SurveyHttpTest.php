<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Authorization\Roles;
use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\CampaignService;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * HTTP-layer authorization, public boundary, and entitlement enforcement for Step 7. Server-side
 * policy checks (never UI hiding), fail-closed route binding, public draft inaccessibility, and
 * truthful states (rule 32; Step 7 §14, §18, §23, §27, §28).
 */
final class SurveyHttpTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsSurveyPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->provisionTenant();
        $this->provisionSurveyPlan($this->tenant);
    }

    private function owner(): User
    {
        [$owner] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        return $owner;
    }

    private function tenantSession(User $user): array
    {
        return ['current_tenant_id' => $this->tenant->id];
    }

    /** Build a published survey + active campaign directly via services (as $actor). */
    private function publishedCampaign(User $actor): SurveyCampaign
    {
        $this->establishTenantContext($this->tenant);
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $actor);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $actor);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $actor);
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey->fresh(), $version, ['name' => 'C'], $actor),
            $actor,
        );
        $this->forgetTenantContext();

        return $campaign;
    }

    public function test_owner_can_create_add_question_and_publish_over_http(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->withSession($this->tenantSession($owner))
            ->post('/surveys', ['name' => 'Kepuasan'])->assertRedirect();
        $this->endRequestScope();

        $survey = Survey::withoutGlobalScopes()->where('name', 'Kepuasan')->firstOrFail();

        $this->actingAs($owner)->withSession($this->tenantSession($owner))
            ->post("/surveys/{$survey->ulid}/questions", [
                'question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true,
                'display_order' => 1, 'scored' => true,
                'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better'],
            ])->assertRedirect();
        $this->endRequestScope();

        $this->actingAs($owner)->withSession($this->tenantSession($owner))
            ->post("/surveys/{$survey->ulid}/publish")->assertRedirect();

        $this->assertDatabaseHas('survey_versions', ['survey_id' => $survey->id, 'status' => 'published']);
    }

    public function test_read_only_user_cannot_create_a_survey(): void
    {
        [$reader] = $this->memberWithRole($this->tenant, Roles::READ_ONLY);
        $this->endRequestScope();

        $this->actingAs($reader)->withSession($this->tenantSession($reader))
            ->post('/surveys', ['name' => 'Nope'])->assertForbidden();

        $this->assertDatabaseMissing('surveys', ['name' => 'Nope']);
    }

    public function test_branch_manager_cannot_create_a_survey(): void
    {
        [$manager] = $this->memberWithRole($this->tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        $this->endRequestScope();

        $this->actingAs($manager)->withSession($this->tenantSession($manager))
            ->post('/surveys', ['name' => 'Nope'])->assertForbidden();
    }

    public function test_creating_a_survey_without_a_plan_is_denied(): void
    {
        $other = $this->provisionTenant(); // no survey plan
        [$owner] = $this->memberWithRole($other, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $other->id])
            ->post('/surveys', ['name' => 'NoPlan'])
            ->assertRedirect()
            ->assertSessionHasErrors('entitlement');

        $this->assertDatabaseMissing('surveys', ['name' => 'NoPlan']);
    }

    public function test_mass_assignment_of_tenant_id_is_ignored(): void
    {
        $foreign = Tenant::factory()->create();
        $owner = $this->owner();

        $this->actingAs($owner)->withSession($this->tenantSession($owner))
            ->post('/surveys', ['name' => 'Stamped', 'tenant_id' => $foreign->id])->assertRedirect();

        // Stamped from context, never from the request body.
        $this->assertDatabaseHas('surveys', ['name' => 'Stamped', 'tenant_id' => $this->tenant->id]);
        $this->assertDatabaseMissing('surveys', ['name' => 'Stamped', 'tenant_id' => $foreign->id]);
    }

    public function test_preview_requires_authentication(): void
    {
        $owner = $this->owner();
        $campaign = $this->publishedCampaign($owner);
        $version = $campaign->survey_version_id;
        $survey = Survey::withoutGlobalScopes()->findOrFail($campaign->survey_id);
        $versionUlid = SurveyVersion::withoutGlobalScopes()->findOrFail($version)->ulid;

        // Unauthenticated preview attempt is redirected to login (never public).
        $this->get("/surveys/{$survey->ulid}/preview/{$versionUlid}")->assertRedirect(route('login'));
    }

    public function test_public_campaign_page_and_submission_over_http(): void
    {
        $owner = $this->owner();
        $campaign = $this->publishedCampaign($owner);

        $this->get('/s/c/'.$campaign->public_id)->assertOk()->assertSee('Puas?');

        $this->post('/s/c/'.$campaign->public_id, ['answers' => ['csat' => 5]])->assertOk();

        $this->assertDatabaseHas('survey_responses', ['campaign_id' => $campaign->id, 'status' => 'completed']);
    }

    public function test_public_invalid_link_returns_truthful_unavailable_state(): void
    {
        $this->get('/s/c/01JUNKUNKNOWNPUBLICID000000')->assertStatus(410)->assertSee('tidak tersedia');
    }

    public function test_qr_endpoint_returns_svg_of_the_public_url(): void
    {
        $owner = $this->owner();
        $campaign = $this->publishedCampaign($owner);

        $response = $this->get('/s/c/'.$campaign->public_id.'/qr');
        $response->assertOk();
        $this->assertStringContainsString('image/svg+xml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_a_draft_survey_is_not_reachable_through_any_public_route(): void
    {
        $owner = $this->owner();
        $this->establishTenantContext($this->tenant);
        $survey = app(SurveyService::class)->create(['name' => 'Draft'], $owner);
        $this->forgetTenantContext();

        // A draft has no campaign public link; guessing the survey ulid on a public route 410s.
        $this->get('/s/c/'.$survey->ulid)->assertStatus(410);
    }
}
