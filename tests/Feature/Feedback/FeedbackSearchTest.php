<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Authorization\Roles;
use App\Enums\FeedbackStatus;
use App\Feedback\Search\FeedbackSearchCriteria;
use App\Feedback\Search\FeedbackSearchService;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class FeedbackSearchTest extends TestCase
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

    private function service(): FeedbackSearchService
    {
        return app(FeedbackSearchService::class);
    }

    public function test_status_filter_limits_results(): void
    {
        FeedbackItem::factory()->status(FeedbackStatus::New)->create();
        FeedbackItem::factory()->status(FeedbackStatus::Resolved)->create();

        $result = $this->service()->search(
            new FeedbackSearchCriteria(statuses: [FeedbackStatus::Resolved->value]),
            $this->owner,
        );

        $this->assertCount(1, $result->items());
        $this->assertSame(FeedbackStatus::Resolved, $result->items()[0]->status);
    }

    public function test_content_search_requires_content_permission(): void
    {
        FeedbackItem::factory()->create(['search_meta' => 'checkout survey', 'search_content' => 'the needle word here']);

        // Owner has feedback.view-content.
        $withContent = $this->service()->search(new FeedbackSearchCriteria(query: 'needle'), $this->owner);
        $this->assertCount(1, $withContent->items());

        // A read-only user has feedback.view but NOT feedback.view-content.
        [$readOnly] = $this->memberWithRole($this->tenant, Roles::READ_ONLY);
        $withoutContent = $this->service()->search(new FeedbackSearchCriteria(query: 'needle'), $readOnly);
        $this->assertCount(0, $withoutContent->items());

        // Both can still match safe metadata.
        $metaMatch = $this->service()->search(new FeedbackSearchCriteria(query: 'checkout'), $readOnly);
        $this->assertCount(1, $metaMatch->items());
    }

    public function test_metric_filter_matches_snapshot_values(): void
    {
        FeedbackItem::factory()->create(['metric_snapshot' => ['csat' => 4, 'nps' => 9, 'ces' => null]]);
        FeedbackItem::factory()->create(['metric_snapshot' => ['csat' => 2, 'nps' => 3, 'ces' => null]]);

        $result = $this->service()->search(new FeedbackSearchCriteria(metric: 'csat', metricValue: 4), $this->owner);

        $this->assertCount(1, $result->items());
    }

    public function test_unknown_sort_falls_back_without_error(): void
    {
        FeedbackItem::factory()->count(2)->create();

        $result = $this->service()->search(new FeedbackSearchCriteria(sort: 'name; drop table'), $this->owner);

        $this->assertCount(2, $result->items());
    }

    public function test_branch_restricted_user_only_sees_accessible_branches(): void
    {
        $branchA = Branch::factory()->create(['tenant_id' => $this->tenant->id]);
        $branchB = Branch::factory()->create(['tenant_id' => $this->tenant->id]);
        FeedbackItem::factory()->create(['branch_id' => $branchA->id]);
        FeedbackItem::factory()->create(['branch_id' => $branchB->id]);

        [$manager, $managerMembership] = $this->memberWithRole($this->tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        BranchAccessGrant::factory()->create([
            'tenant_id' => $this->tenant->id,
            'tenant_membership_id' => $managerMembership->id,
            'branch_id' => $branchA->id,
        ]);

        $this->endRequestScope();
        $this->establishTenantContext($this->tenant, $managerMembership->fresh());

        $result = $this->service()->search(new FeedbackSearchCriteria, $manager);

        $branchIds = array_map(fn (FeedbackItem $i) => $i->branch_id, $result->items());
        $this->assertContains($branchA->id, $branchIds);
        $this->assertNotContains($branchB->id, $branchIds);
    }
}
