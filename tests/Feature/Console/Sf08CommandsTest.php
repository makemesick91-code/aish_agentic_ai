<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Feedback\FeedbackProjector;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

/**
 * Step 8 console commands. `aish:feedback-reconcile` back-fills feedback items for completed survey
 * responses that were never projected (e.g. a dropped after-commit dispatch), idempotently and
 * tenant-aware (rule 33; Step 8 §9.4).
 */
final class Sf08CommandsTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->establishTenantContext($this->tenant);
    }

    public function test_feedback_reconcile_backfills_missing_projections(): void
    {
        $missing = SurveyResponse::factory()->completed()->create();
        $alreadyProjected = SurveyResponse::factory()->completed()->create();
        app(FeedbackProjector::class)->projectFromSurveyResponse($alreadyProjected);

        $this->artisan('aish:feedback-reconcile', ['--tenant' => $this->tenant->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('feedback_items', 2);
        $this->assertDatabaseHas('feedback_items', ['survey_response_id' => $missing->id]);
    }

    public function test_feedback_reconcile_is_idempotent_on_rerun(): void
    {
        SurveyResponse::factory()->completed()->create();

        $this->artisan('aish:feedback-reconcile', ['--tenant' => $this->tenant->id])->assertSuccessful();
        $this->artisan('aish:feedback-reconcile', ['--tenant' => $this->tenant->id])->assertSuccessful();

        // The command clears tenant context; assert against the raw table (no global scope).
        $this->assertDatabaseCount('feedback_items', 1);
    }
}
