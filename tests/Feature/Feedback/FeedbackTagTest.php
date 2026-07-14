<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackEventType;
use App\Feedback\Exceptions\FeedbackTagException;
use App\Feedback\FeedbackTagService;
use App\Models\FeedbackItem;
use App\Models\FeedbackTag;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackTagTest extends TestCase
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

    private function service(): FeedbackTagService
    {
        return app(FeedbackTagService::class);
    }

    public function test_create_tag_is_audited(): void
    {
        $tag = $this->service()->createTag('Billing issue', $this->actor);

        $this->assertDatabaseHas('feedback_tags', ['id' => $tag->id, 'name' => 'Billing issue']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.tag.created']);
    }

    public function test_duplicate_tag_name_is_rejected(): void
    {
        $this->service()->createTag('Billing issue', $this->actor);

        $this->expectException(FeedbackTagException::class);
        $this->service()->createTag('Billing issue', $this->actor);
    }

    public function test_attach_and_remove_tag_records_timeline_and_audit(): void
    {
        $item = FeedbackItem::factory()->create();
        $tag = $this->service()->createTag('Wait time', $this->actor);

        $this->service()->attach($item, $tag, $this->actor);
        $this->assertDatabaseHas('feedback_item_tags', ['feedback_item_id' => $item->id, 'feedback_tag_id' => $tag->id]);
        $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $item->id, 'type' => FeedbackEventType::TagAttached->value]);

        $this->service()->remove($item, $tag, $this->actor);
        $this->assertDatabaseMissing('feedback_item_tags', ['feedback_item_id' => $item->id, 'feedback_tag_id' => $tag->id]);
        $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $item->id, 'type' => FeedbackEventType::TagRemoved->value]);
    }

    public function test_attach_is_idempotent(): void
    {
        $item = FeedbackItem::factory()->create();
        $tag = $this->service()->createTag('Repeat', $this->actor);

        $this->service()->attach($item, $tag, $this->actor);
        $this->service()->attach($item, $tag, $this->actor);

        $this->assertDatabaseCount('feedback_item_tags', 1);
    }

    public function test_archived_tag_cannot_be_attached(): void
    {
        $item = FeedbackItem::factory()->create();
        $tag = $this->service()->createTag('Old', $this->actor);
        $this->service()->archiveTag($tag, $this->actor);

        $this->expectException(FeedbackTagException::class);
        $this->service()->attach($item, $tag->fresh(), $this->actor);
    }

    public function test_cross_tenant_attach_is_rejected(): void
    {
        $item = FeedbackItem::factory()->create();
        $tag = FeedbackTag::factory()->make(['tenant_id' => $item->tenant_id + 999]);

        $this->expectException(FeedbackTagException::class);
        $this->service()->attach($item, $tag, $this->actor);
    }
}
