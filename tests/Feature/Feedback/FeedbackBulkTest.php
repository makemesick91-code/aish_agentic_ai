<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Authorization\Roles;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Feedback\Bulk\FeedbackBulkService;
use App\Feedback\Exceptions\FeedbackBulkException;
use App\Feedback\FeedbackTagService;
use App\Models\Branch;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class FeedbackBulkTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->provisionTenant();
        [$this->owner, $membership] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($this->tenant, $membership);
    }

    private function service(): FeedbackBulkService
    {
        return app(FeedbackBulkService::class);
    }

    public function test_bulk_transition_moves_all_and_traces_each(): void
    {
        $ids = FeedbackItem::factory()->count(3)->status(FeedbackStatus::New)->create()->pluck('id')->all();

        $result = $this->service()->transition($ids, FeedbackStatus::Triaged, $this->owner);

        $this->assertSame(3, $result->processed);
        foreach ($ids as $id) {
            $this->assertSame(FeedbackStatus::Triaged, FeedbackItem::find($id)->status);
            $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $id, 'type' => FeedbackEventType::StatusChanged->value]);
        }
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.bulk-operation.executed']);
    }

    public function test_invalid_item_aborts_whole_batch(): void
    {
        $valid = FeedbackItem::factory()->status(FeedbackStatus::New)->create();
        $invalid = FeedbackItem::factory()->status(FeedbackStatus::Resolved)->create();

        try {
            $this->service()->transition([$valid->id, $invalid->id], FeedbackStatus::Triaged, $this->owner);
            $this->fail('Expected FeedbackBulkException.');
        } catch (FeedbackBulkException) {
            // expected
        }

        // No partial mutation: the valid item is untouched.
        $this->assertSame(FeedbackStatus::New, $valid->fresh()->status);
    }

    public function test_oversized_batch_is_rejected(): void
    {
        $this->expectException(FeedbackBulkException::class);
        $this->service()->transition(range(1, FeedbackBulkService::MAX_BATCH + 1), FeedbackStatus::Triaged, $this->owner);
    }

    public function test_unresolved_id_aborts_batch(): void
    {
        $item = FeedbackItem::factory()->status(FeedbackStatus::New)->create();

        try {
            $this->service()->transition([$item->id, 999_999], FeedbackStatus::Triaged, $this->owner);
            $this->fail('Expected FeedbackBulkException.');
        } catch (FeedbackBulkException) {
            // expected
        }

        $this->assertSame(FeedbackStatus::New, $item->fresh()->status);
    }

    public function test_bulk_assign_sets_all(): void
    {
        [$assignee] = $this->memberWithRole($this->tenant, Roles::CORPORATE_ADMIN);
        $ids = FeedbackItem::factory()->count(2)->create()->pluck('id')->all();

        $this->service()->assign($ids, $assignee, $this->owner);

        foreach ($ids as $id) {
            $this->assertSame($assignee->id, FeedbackItem::find($id)->current_assignee_id);
        }
    }

    public function test_bulk_attach_tag_sets_all(): void
    {
        $tag = app(FeedbackTagService::class)->createTag('Bulk tag', $this->owner);
        $ids = FeedbackItem::factory()->count(2)->create()->pluck('id')->all();

        $this->service()->attachTag($ids, $tag, $this->owner);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('feedback_item_tags', ['feedback_item_id' => $id, 'feedback_tag_id' => $tag->id]);
        }
    }

    public function test_out_of_branch_item_is_forbidden(): void
    {
        $branch = Branch::factory()->create(['tenant_id' => $this->tenant->id]);
        $item = FeedbackItem::factory()->create(['branch_id' => $branch->id]);
        [$manager, $managerMembership] = $this->memberWithRole($this->tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);

        $this->endRequestScope();
        $this->establishTenantContext($this->tenant, $managerMembership->fresh());

        $this->expectException(FeedbackBulkException::class);
        $this->service()->transition([$item->id], FeedbackStatus::Triaged, $manager);
    }
}
