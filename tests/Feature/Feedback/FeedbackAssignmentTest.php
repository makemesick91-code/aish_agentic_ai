<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Authorization\Roles;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackStatus;
use App\Enums\UserStatus;
use App\Feedback\Exceptions\InvalidAssigneeException;
use App\Feedback\FeedbackAssignmentService;
use App\Models\Branch;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class FeedbackAssignmentTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->provisionTenant();
        [$this->actor, $membership] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($this->tenant, $membership);
    }

    private function service(): FeedbackAssignmentService
    {
        return app(FeedbackAssignmentService::class);
    }

    public function test_valid_member_can_be_assigned(): void
    {
        [$assignee] = $this->memberWithRole($this->tenant, Roles::CORPORATE_ADMIN);
        $item = FeedbackItem::factory()->create();

        $this->service()->assign($item, $assignee, $this->actor);

        $fresh = $item->fresh();
        $this->assertSame($assignee->id, $fresh->current_assignee_id);
        $this->assertSame(FeedbackStatus::Assigned, $fresh->status);
        $this->assertNotNull($fresh->assigned_at);
        $this->assertDatabaseHas('feedback_assignments', [
            'feedback_item_id' => $item->id,
            'new_assignee_id' => $assignee->id,
            'actor_id' => $this->actor->id,
        ]);
        $this->assertDatabaseHas('feedback_events', [
            'feedback_item_id' => $item->id,
            'type' => FeedbackEventType::Assigned->value,
        ]);
        $this->assertDatabaseHas('notification_deliveries', [
            'tenant_id' => $this->tenant->id,
            'recipient_id' => $assignee->id,
            'type' => 'feedback.assigned',
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.assigned']);
    }

    public function test_unassign_clears_current_assignee(): void
    {
        [$assignee] = $this->memberWithRole($this->tenant, Roles::CORPORATE_ADMIN);
        $item = FeedbackItem::factory()->create();
        $this->service()->assign($item, $assignee, $this->actor);

        $this->service()->assign($item->fresh(), null, $this->actor, 'No longer relevant');

        $this->assertNull($item->fresh()->current_assignee_id);
        $this->assertDatabaseHas('feedback_events', [
            'feedback_item_id' => $item->id,
            'type' => FeedbackEventType::Unassigned->value,
        ]);
    }

    public function test_non_member_cannot_be_assigned(): void
    {
        $stranger = User::factory()->create();
        $item = FeedbackItem::factory()->create();

        $this->expectException(InvalidAssigneeException::class);
        $this->service()->assign($item, $stranger, $this->actor);
    }

    public function test_suspended_user_cannot_be_assigned(): void
    {
        $suspended = User::factory()->create(['status' => UserStatus::Suspended]);
        TenantMembership::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $suspended->id,
        ]);
        $item = FeedbackItem::factory()->create();

        $this->expectException(InvalidAssigneeException::class);
        $this->service()->assign($item, $suspended, $this->actor);
    }

    public function test_member_without_feedback_permission_cannot_be_assigned(): void
    {
        [$assignee] = $this->memberWithoutRole($this->tenant);
        $item = FeedbackItem::factory()->create();

        $this->expectException(InvalidAssigneeException::class);
        $this->service()->assign($item, $assignee, $this->actor);
    }

    public function test_branch_restricted_member_cannot_be_assigned_outside_branch(): void
    {
        $branch = Branch::factory()->create(['tenant_id' => $this->tenant->id]);
        [$assignee] = $this->memberWithRole($this->tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        $item = FeedbackItem::factory()->create(['branch_id' => $branch->id]);

        $this->expectException(InvalidAssigneeException::class);
        $this->service()->assign($item, $assignee, $this->actor);
    }

    public function test_assignment_history_is_append_only(): void
    {
        [$assignee] = $this->memberWithRole($this->tenant, Roles::CORPORATE_ADMIN);
        $item = FeedbackItem::factory()->create();
        $this->service()->assign($item, $assignee, $this->actor);
        $record = $item->assignmentHistory()->latest('id')->first();

        $this->expectException(\RuntimeException::class);
        $record->update(['reason' => 'tampered']);
    }
}
