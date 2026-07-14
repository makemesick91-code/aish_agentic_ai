<?php

declare(strict_types=1);

namespace Tests\Feature\Surveys;

use App\Models\Survey;
use App\Models\Tenant;
use App\Models\User;
use App\Surveys\CampaignService;
use App\Surveys\PublicSurveyGateway;
use App\Surveys\SurveyService;
use App\Surveys\SurveySummaryService;
use App\Surveys\SurveyVersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsSurveyPlan;
use Tests\TestCase;

final class SurveySummaryTest extends TestCase
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

    public function test_summary_aggregates_csat_nps_and_ces_deterministically(): void
    {
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $this->actor);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $this->actor);
        $svc->addQuestion($draft, ['question_key' => 'nps', 'type' => 'nps', 'prompt' => 'Rekomendasi?', 'required' => true, 'display_order' => 2, 'scored' => true, 'scoring_config' => ['scale_min' => 0, 'scale_max' => 10, 'direction' => 'higher_is_better']], $this->actor);
        $svc->addQuestion($draft, ['question_key' => 'ces', 'type' => 'ces', 'prompt' => 'Mudah?', 'required' => true, 'display_order' => 3, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 7, 'direction' => 'higher_is_better']], $this->actor);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $this->actor);
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey->fresh(), $version, ['name' => 'C'], $this->actor),
            $this->actor,
        );
        $publicId = $campaign->public_id;
        $this->forgetTenantContext();

        $gateway = app(PublicSurveyGateway::class);
        $gateway->submitForCampaign($publicId, ['csat' => 5, 'nps' => 9, 'ces' => 6]);
        $gateway->submitForCampaign($publicId, ['csat' => 4, 'nps' => 10, 'ces' => 7]);
        $gateway->submitForCampaign($publicId, ['csat' => 2, 'nps' => 6, 'ces' => 5]);

        $this->establishTenantContext($this->tenant);
        $metrics = app(SurveySummaryService::class)->metricsForVersion($version->fresh());

        // CSAT: 5,4,2 -> valid 3, satisfied 2 (>=4) -> 66.67%, avg 3.67
        $this->assertSame(3, $metrics['csat']['valid_count']);
        $this->assertSame(2, $metrics['csat']['satisfied_count']);
        $this->assertSame(66.67, $metrics['csat']['csat_percentage']);
        $this->assertSame(3.67, $metrics['csat']['average_score']);

        // NPS: 9,10,6 -> 2 promoters, 1 detractor -> (2-1)/3*100 = 33.33
        $this->assertSame(2, $metrics['nps']['promoters']);
        $this->assertSame(1, $metrics['nps']['detractors']);
        $this->assertSame(33.33, $metrics['nps']['nps_score']);

        // CES: 6,7,5 -> avg 6.0
        $this->assertSame(6.0, $metrics['ces']['average']);

        $overview = app(SurveySummaryService::class)->overview($survey->fresh());
        $this->assertSame(3, $overview['total_completed']);
    }

    public function test_summary_is_tenant_scoped(): void
    {
        // Another tenant's surveys never leak into this tenant's overview.
        $this->forgetTenantContext();
        $other = Tenant::factory()->create();
        $this->establishTenantContext($other);
        Survey::factory()->create();
        $this->forgetTenantContext();

        $this->establishTenantContext($this->tenant);
        $mine = Survey::factory()->create();
        $overview = app(SurveySummaryService::class)->overview($mine);
        $this->assertSame(0, $overview['total_completed']);
    }
}
