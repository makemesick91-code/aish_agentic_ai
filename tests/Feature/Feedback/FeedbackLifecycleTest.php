<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Feedback\Exceptions\InvalidStatusTransitionException;
use App\Feedback\FeedbackLifecycle;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackLifecycleTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);
        $this->actor = User::factory()->create();
    }

    private function service(): FeedbackLifecycle
    {
        return app(FeedbackLifecycle::class);
    }

    private function item(FeedbackStatus $status = FeedbackStatus::New): FeedbackItem
    {
        return FeedbackItem::factory()->status($status)->create();
    }

    public function test_new_to_triaged_stamps_time_and_records_events(): void
    {
        $item = $this->item(FeedbackStatus::New);

        $this->service()->transition($item, FeedbackStatus::Triaged, $this->actor);

        $this->assertSame(FeedbackStatus::Triaged, $item->fresh()->status);
        $this->assertNotNull($item->fresh()->triaged_at);
        $this->assertDatabaseHas('feedback_events', [
            'feedback_item_id' => $item->id,
            'type' => FeedbackEventType::StatusChanged->value,
            'actor_id' => $this->actor->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.status.changed']);
    }

    public function test_invalid_transition_fails_closed(): void
    {
        $item = $this->item(FeedbackStatus::New);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->service()->transition($item, FeedbackStatus::Resolved, $this->actor);
    }

    public function test_full_operational_path(): void
    {
        $item = $this->item(FeedbackStatus::New);
        $this->service()->transition($item, FeedbackStatus::Triaged, $this->actor);
        $this->service()->transition($item, FeedbackStatus::InProgress, $this->actor);
        $this->service()->transition($item, FeedbackStatus::Resolved, $this->actor);
        $this->service()->transition($item, FeedbackStatus::Closed, $this->actor);

        $fresh = $item->fresh();
        $this->assertSame(FeedbackStatus::Closed, $fresh->status);
        $this->assertNotNull($fresh->resolved_at);
        $this->assertNotNull($fresh->closed_at);
    }

    public function test_reopen_requires_a_reason(): void
    {
        $item = $this->item(FeedbackStatus::Resolved);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->service()->transition($item, FeedbackStatus::InProgress, $this->actor);
    }

    public function test_reopen_with_reason_records_reopened_event(): void
    {
        $item = $this->item(FeedbackStatus::Resolved);

        $this->service()->transition($item, FeedbackStatus::InProgress, $this->actor, 'Customer replied');

        $this->assertSame(FeedbackStatus::InProgress, $item->fresh()->status);
        $this->assertNotNull($item->fresh()->reopened_at);
        $this->assertDatabaseHas('feedback_events', [
            'feedback_item_id' => $item->id,
            'type' => FeedbackEventType::Reopened->value,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.reopened']);
    }

    public function test_archived_is_read_only(): void
    {
        $item = $this->item(FeedbackStatus::Archived);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->service()->transition($item, FeedbackStatus::Triaged, $this->actor);
    }

    public function test_timeline_events_are_immutable(): void
    {
        $item = $this->item(FeedbackStatus::New);
        $this->service()->transition($item, FeedbackStatus::Triaged, $this->actor);
        $event = $item->events()->latest('id')->first();

        $this->expectException(\RuntimeException::class);
        $event->update(['type' => FeedbackEventType::Assigned]);
    }
}
