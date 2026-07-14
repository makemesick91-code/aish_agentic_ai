<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use App\Models\FeedbackItemTag;
use App\Models\FeedbackTag;
use App\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

/**
 * Proves the Step 8 database-layer invariants bite (not just PHP validation): one feedback item per
 * source, unique tag names/slugs per tenant, a tag attached at most once, and idempotent exports
 * (rule 33; Step 8 §25).
 */
final class Sf08MigrationIntegrityTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->establishTenantContext(Tenant::factory()->create());
    }

    public function test_one_feedback_item_per_source(): void
    {
        $item = FeedbackItem::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        FeedbackItem::factory()->create([
            'source_type' => $item->source_type,
            'source_id' => $item->source_id,
        ]);
    }

    public function test_survey_response_projects_once(): void
    {
        $item = FeedbackItem::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        FeedbackItem::factory()->create(['survey_response_id' => $item->survey_response_id]);
    }

    public function test_tag_slug_is_unique_per_tenant(): void
    {
        FeedbackTag::factory()->create(['slug' => 'wait-time', 'name' => 'Wait time']);

        $this->expectException(UniqueConstraintViolationException::class);
        FeedbackTag::factory()->create(['slug' => 'wait-time', 'name' => 'Different name']);
    }

    public function test_tag_can_be_attached_once(): void
    {
        $item = FeedbackItem::factory()->create();
        $tag = FeedbackTag::factory()->create();
        FeedbackItemTag::create(['tenant_id' => $item->tenant_id, 'feedback_item_id' => $item->id, 'feedback_tag_id' => $tag->id]);

        $this->expectException(UniqueConstraintViolationException::class);
        FeedbackItemTag::create(['tenant_id' => $item->tenant_id, 'feedback_item_id' => $item->id, 'feedback_tag_id' => $tag->id]);
    }

    public function test_export_idempotency_key_is_unique_per_tenant(): void
    {
        FeedbackExport::factory()->create(['idempotency_key' => 'dup-key']);

        $this->expectException(UniqueConstraintViolationException::class);
        FeedbackExport::factory()->create(['idempotency_key' => 'dup-key']);
    }
}
