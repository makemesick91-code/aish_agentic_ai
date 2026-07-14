<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Authorization\Roles;
use App\Enums\PlatformRole;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\PlatformRoleAssignment;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\CampaignService;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Step 7 cross-tenant / cross-branch attack matrix. Every hostile access must fail closed:
 * IDOR across tenants and branches, published-version mutation, cross-tenant campaign binding,
 * token tampering, concurrent double-completion, and platform-to-tenant reads (rule 32; Step 7
 * §28).
 */
final class Sf07CrossTenantMatrixTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsSurveyPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    /** @return array{0: Tenant, 1: User} */
    private function tenantWithOwner(): array
    {
        $tenant = $this->provisionTenant();
        $this->provisionSurveyPlan($tenant);
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        return [$tenant, $owner];
    }

    private function publishedSurvey(Tenant $tenant, User $owner, ?int $branchId = null): Survey
    {
        $this->establishTenantContext($tenant);
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX', 'branch_id' => null], $owner);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $owner);
        app(SurveyVersionPublisher::class)->publish($draft->fresh(), $owner);
        $this->forgetTenantContext();

        return $survey->fresh();
    }

    public function test_tenant_a_cannot_open_or_publish_tenant_b_survey(): void
    {
        [$tenantB, $ownerB] = $this->tenantWithOwner();
        $surveyB = $this->publishedSurvey($tenantB, $ownerB);

        [$tenantA, $ownerA] = $this->tenantWithOwner();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get("/surveys/{$surveyB->ulid}")->assertNotFound();
        $this->endRequestScope();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post("/surveys/{$surveyB->ulid}/publish")->assertNotFound();
    }

    public function test_cross_tenant_campaign_binding_is_rejected(): void
    {
        [$tenantB, $ownerB] = $this->tenantWithOwner();
        $surveyB = $this->publishedSurvey($tenantB, $ownerB);

        [$tenantA, $ownerA] = $this->tenantWithOwner();

        // Tenant A tries to bind a campaign to tenant B's survey ULID.
        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post('/survey-campaigns', ['survey_ulid' => $surveyB->ulid, 'name' => 'Evil'])
            ->assertNotFound();

        $this->assertDatabaseMissing('survey_campaigns', ['name' => 'Evil']);
    }

    public function test_branch_manager_cannot_view_another_branchs_campaign(): void
    {
        $tenant = $this->provisionTenant();
        $this->provisionSurveyPlan($tenant);
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        $branchX = Branch::factory()->for($tenant)->create();
        $branchY = Branch::factory()->for($tenant)->create();

        // A campaign owned by branch Y.
        $this->establishTenantContext($tenant);
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $owner);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Q', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $owner);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $owner);
        $campaignY = app(CampaignService::class)->create($survey->fresh(), $version, ['name' => 'Y', 'branch_id' => $branchY->id], $owner);
        $this->forgetTenantContext();

        // Branch manager granted only branch X.
        [$manager, $membership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        BranchAccessGrant::factory()->create(['tenant_id' => $tenant->id, 'tenant_membership_id' => $membership->id, 'branch_id' => $branchX->id]);
        $this->endRequestScope();

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->id])
            ->get("/survey-campaigns/{$campaignY->ulid}")->assertForbidden();
    }

    public function test_a_published_version_cannot_be_mutated_via_http(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();
        $survey = $this->publishedSurvey($tenant, $owner); // no editable draft remains

        // Attempt to add a question to the published survey (no draft) — refused, no mutation.
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post("/surveys/{$survey->ulid}/questions", ['question_key' => 'x', 'type' => 'short_text', 'prompt' => 'X', 'display_order' => 9])
            ->assertSessionHasErrors('question');

        $this->establishTenantContext($tenant);
        $this->assertSame(1, $survey->currentVersion()->first()->questions()->count());
    }

    public function test_platform_user_has_no_tenant_survey_access(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();
        $this->publishedSurvey($tenant, $owner);

        // A platform admin with NO tenant membership cannot read the tenant's surveys.
        $platformUser = User::factory()->create();
        PlatformRoleAssignment::factory()->create(['user_id' => $platformUser->id, 'role' => PlatformRole::Admin]);
        $this->endRequestScope();

        $response = $this->actingAs($platformUser)->withSession(['current_tenant_id' => $tenant->id])->get('/surveys');
        $this->assertNotSame(200, $response->getStatusCode(), 'A platform user must not read tenant survey data.');
    }

    public function test_token_tampering_and_concurrent_double_completion_fail_over_http(): void
    {
        [$tenant, $owner] = $this->tenantWithOwner();
        $survey = $this->publishedSurvey($tenant, $owner);

        $this->establishTenantContext($tenant);
        $version = $survey->currentVersion()->first();
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey, $version, ['name' => 'C'], $owner),
            $owner,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => 'k1'], $owner);
        $this->forgetTenantContext();

        // Tampered token → truthful unavailable (410), no enumeration.
        $this->post('/s/i/'.$issued->invitation->public_id.'/tampered', ['answers' => ['csat' => 5]])->assertStatus(410);

        // First valid submit completes; the second (replay) is refused.
        $this->post('/s/i/'.$issued->invitation->public_id.'/'.$issued->plainToken, ['answers' => ['csat' => 5]])->assertOk();
        $this->post('/s/i/'.$issued->invitation->public_id.'/'.$issued->plainToken, ['answers' => ['csat' => 5]])->assertStatus(410);

        $this->establishTenantContext($tenant);
        $this->assertSame(1, SurveyResponse::where('invitation_id', $issued->invitation->id)->where('status', 'completed')->count());
    }
}
