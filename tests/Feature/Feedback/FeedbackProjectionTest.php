<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackEventType;
use App\Enums\FeedbackSourceType;
use App\Enums\FeedbackStatus;
use App\Events\SurveyResponseCompleted;
use App\Feedback\FeedbackProjector;
use App\Feedback\Listeners\ProjectFeedbackOnSurveyResponseCompleted;
use App\Models\FeedbackItem;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Subscriptions\MeterKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackProjectionTest extends TestCase
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

    private function projector(): FeedbackProjector
    {
        return app(FeedbackProjector::class);
    }

    public function test_completed_response_projects_exactly_one_feedback_item(): void
    {
        $response = SurveyResponse::factory()->completed()->create();

        $item = $this->projector()->projectFromSurveyResponse($response);

        $this->assertDatabaseCount('feedback_items', 1);
        $this->assertSame($response->id, $item->survey_response_id);
        $this->assertSame(FeedbackSourceType::SurveyResponse, $item->source_type);
        $this->assertSame($response->id, $item->source_id);
        $this->assertSame(FeedbackStatus::New, $item->status);
        $this->assertNull($item->created_by);

        $this->assertDatabaseHas('feedback_events', [
            'feedback_item_id' => $item->id,
            'type' => FeedbackEventType::Projected->value,
            'actor_id' => null,
        ]);
        $this->assertDatabaseHas('usage_records', [
            'tenant_id' => $this->tenant->id,
            'meter_key' => MeterKeys::FEEDBACK_ITEMS_PROJECTED,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.projected']);
    }

    public function test_projection_is_idempotent_on_retry(): void
    {
        $response = SurveyResponse::factory()->completed()->create();

        $first = $this->projector()->projectFromSurveyResponse($response);
        $second = $this->projector()->projectFromSurveyResponse($response);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('feedback_items', 1);
        // Usage must not double count on a retry.
        $this->assertSame(1, FeedbackItem::query()->count());
        $this->assertDatabaseCount('usage_records', 1);
    }

    public function test_queued_listener_projects_from_event(): void
    {
        $response = SurveyResponse::factory()->completed()->create();

        (new ProjectFeedbackOnSurveyResponseCompleted)->handle(
            new SurveyResponseCompleted($response->id, $this->tenant->id, $response->branch_id),
        );

        // The listener clears tenant context; use raw assertions (no global scope).
        $this->assertDatabaseCount('feedback_items', 1);
        $this->assertDatabaseHas('feedback_items', ['survey_response_id' => $response->id]);
    }

    public function test_listener_is_idempotent_across_duplicate_events(): void
    {
        $response = SurveyResponse::factory()->completed()->create();
        $event = new SurveyResponseCompleted($response->id, $this->tenant->id, $response->branch_id);

        (new ProjectFeedbackOnSurveyResponseCompleted)->handle($event);
        (new ProjectFeedbackOnSurveyResponseCompleted)->handle($event);

        $this->assertDatabaseCount('feedback_items', 1);
    }

    public function test_reconcile_projects_only_missing_items(): void
    {
        $projected = SurveyResponse::factory()->completed()->create();
        $missing = SurveyResponse::factory()->completed()->create();
        $this->projector()->projectFromSurveyResponse($projected);

        $this->artisan('aish:reconcile-feedback-projections', ['--tenant' => $this->tenant->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('feedback_items', 2);
        $this->assertDatabaseHas('feedback_items', ['survey_response_id' => $missing->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.projection.reconciled']);
    }

    public function test_started_response_is_not_projected_by_listener(): void
    {
        $response = SurveyResponse::factory()->create(); // status started

        (new ProjectFeedbackOnSurveyResponseCompleted)->handle(
            new SurveyResponseCompleted($response->id, $this->tenant->id, $response->branch_id),
        );

        $this->assertDatabaseCount('feedback_items', 0);
    }

    public function test_survey_completion_gateway_dispatches_the_event(): void
    {
        Event::fake([SurveyResponseCompleted::class]);

        Event::assertListening(
            SurveyResponseCompleted::class,
            ProjectFeedbackOnSurveyResponseCompleted::class,
        );
    }
}
