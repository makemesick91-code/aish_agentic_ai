<?php

declare(strict_types=1);

namespace Tests\Feature\Customer360;

use App\Authorization\Roles;
use App\Customers\CustomerInteractionsReadModel;
use App\Customers\CustomerMergeService;
use App\Models\Customer;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The interactions timeline is DERIVED, never materialized — which is exactly why it must stay
 * correct across a merge and its reversal with no reprojection step (rule 36; ADR 0070).
 */
final class CustomerInteractionsReadModelTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private CustomerInteractionsReadModel $readModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->readModel = app(CustomerInteractionsReadModel::class);
    }

    /**
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function workspace(string $role = Roles::BUSINESS_OWNER): array
    {
        $tenant = $this->provisionTenant();
        [$user, $membership] = $this->memberWithRole($tenant, $role);

        return [$tenant, $user, $membership];
    }

    private function linkedItem(Tenant $tenant, Customer $customer): FeedbackItem
    {
        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);
        $item->forceFill(['customer_id' => $customer->id])->save();

        return $item;
    }

    public function test_interactions_return_only_the_customers_own_items(): void
    {
        [$tenant, $owner, $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $other = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $mine = $this->linkedItem($tenant, $customer);
        $this->linkedItem($tenant, $other);
        FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);

        $results = $this->readModel->interactions($customer, $owner);

        $this->assertCount(1, $results->items());
        $this->assertSame($mine->id, $results->items()[0]->id);
    }

    /** After a merge the survivor must show the history it absorbed — with no reprojection job. */
    public function test_the_survivor_shows_the_merged_customers_history(): void
    {
        [$tenant, $owner, $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $duplicate = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->linkedItem($tenant, $survivor);
        $this->linkedItem($tenant, $duplicate);

        app(CustomerMergeService::class)->merge($survivor, $duplicate, 'Duplicate profile.');

        $this->assertCount(2, $this->readModel->interactions($survivor->fresh(), $owner)->items());
    }

    /** And after reversal it must stop showing it — immediately, because nothing is stored. */
    public function test_reversing_a_merge_restores_the_separate_timelines(): void
    {
        [$tenant, $owner, $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $duplicate = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->linkedItem($tenant, $survivor);
        $this->linkedItem($tenant, $duplicate);

        $service = app(CustomerMergeService::class);
        $event = $service->merge($survivor, $duplicate, 'Duplicate profile.');
        $service->split($event, 'Different people after all.');

        $this->assertCount(1, $this->readModel->interactions($survivor->fresh(), $owner)->items());
        $this->assertCount(1, $this->readModel->interactions($duplicate->fresh(), $owner)->items());
    }

    /**
     * Customer 360 must not become a way to read feedback content a viewer could not read in the
     * Feedback Inbox (rule 33/36).
     */
    public function test_content_columns_are_excluded_for_a_viewer_without_content_permission(): void
    {
        [$tenant, , $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $this->linkedItem($tenant, $customer);

        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $item = $this->readModel->interactions($customer, $reader)->items()[0];

        $this->assertNull($item->search_content);
        $this->assertFalse(array_key_exists('search_content', $item->getAttributes()));
    }

    public function test_the_summary_masks_contact_for_a_viewer_without_permission(): void
    {
        [$tenant, $owner, $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'contact_email' => 'ana.private@example.com',
        ]);

        [$reader] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $ownerSummary = $this->readModel->summary($customer, $owner);
        $readerSummary = $this->readModel->summary($customer, $reader);

        $this->assertSame('ana.private@example.com', $ownerSummary['contact_email']);
        $this->assertNotSame('ana.private@example.com', $readerSummary['contact_email']);
        $this->assertFalse($readerSummary['contact_visible']);
    }

    /** A long history must never produce an unbounded query. */
    public function test_pagination_is_bounded(): void
    {
        [$tenant, $owner, $membership] = $this->workspace();
        $this->establishTenantContext($tenant, $membership);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        for ($i = 0; $i < 3; $i++) {
            $this->linkedItem($tenant, $customer);
        }

        $results = $this->readModel->interactions($customer, $owner, perPage: 10_000);

        $this->assertLessThanOrEqual(100, $results->perPage());
    }
}
