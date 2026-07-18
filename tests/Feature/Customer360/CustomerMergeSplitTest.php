<?php

declare(strict_types=1);

namespace Tests\Feature\Customer360;

use App\Customers\CustomerMergeService;
use App\Customers\Exceptions\CustomerMergeException;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\CustomerMergeEvent;
use App\Models\FeedbackItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Merge is the highest-blast-radius operation in Customer 360. These tests pin the guarantee that
 * makes it survivable: a merge never deletes, and a split restores EXACTLY what that merge moved
 * (rule 36; ADR 0072).
 */
final class CustomerMergeSplitTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private CustomerMergeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CustomerMergeService::class);
    }

    public function test_merge_moves_identities_and_retains_the_merged_customer(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);
        CustomerIdentity::factory()->forCustomer($merged)->create();
        CustomerIdentity::factory()->forCustomer($merged)->create();

        $event = $this->service->merge($survivor, $merged, 'Same person, duplicate profile.');

        $merged->refresh();

        // Retained, not deleted — this is what makes reversal possible at all.
        $this->assertNotNull(Customer::query()->find($merged->id));
        $this->assertSame(CustomerStatus::Merged, $merged->status);
        $this->assertSame($survivor->id, $merged->merged_into_customer_id);

        $this->assertSame(2, CustomerIdentity::query()->where('customer_id', $survivor->id)->count());
        $this->assertSame(0, CustomerIdentity::query()->where('customer_id', $merged->id)->count());
        $this->assertSame(CustomerMergeEvent::ACTION_MERGE, $event->action);
        $this->assertCount(2, $event->snapshot['moved_identity_ids']);
    }

    public function test_merge_moves_feedback_links(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);
        $item->forceFill(['customer_id' => $merged->id])->save();

        $event = $this->service->merge($survivor, $merged, 'Duplicate.');

        $this->assertSame($survivor->id, $item->fresh()?->customer_id);
        $this->assertSame([$item->id], $event->snapshot['moved_feedback_item_ids']);
    }

    /** The core reversibility guarantee. */
    public function test_split_restores_exactly_what_the_merge_moved(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $movedIdentity = CustomerIdentity::factory()->forCustomer($merged)->create();
        $survivorIdentity = CustomerIdentity::factory()->forCustomer($survivor)->create();

        $movedItem = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);
        $movedItem->forceFill(['customer_id' => $merged->id])->save();

        $mergeEvent = $this->service->merge($survivor, $merged, 'Merged in error.');
        $this->service->split($mergeEvent, 'Operator confirmed these are different people.');

        $merged->refresh();

        $this->assertSame(CustomerStatus::Active, $merged->status);
        $this->assertNull($merged->merged_into_customer_id);

        // The moved identity went back; the survivor's own identity never moved.
        $this->assertSame($merged->id, $movedIdentity->fresh()?->customer_id);
        $this->assertNull($movedIdentity->fresh()?->merged_from_customer_id);
        $this->assertSame($survivor->id, $survivorIdentity->fresh()?->customer_id);

        $this->assertSame($merged->id, $movedItem->fresh()?->customer_id);
    }

    public function test_split_appends_a_new_event_and_never_mutates_the_merge(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $mergeEvent = $this->service->merge($survivor, $merged, 'Duplicate.');
        $originalReason = $mergeEvent->reason;

        $splitEvent = $this->service->split($mergeEvent, 'Reversed.');

        $this->assertSame(CustomerMergeEvent::ACTION_SPLIT, $splitEvent->action);
        $this->assertSame($mergeEvent->id, $splitEvent->reverses_merge_event_id);

        // The merge row is untouched: the ledger keeps the mistake next to its correction.
        $this->assertSame($originalReason, $mergeEvent->fresh()?->reason);
        $this->assertSame(2, CustomerMergeEvent::query()->count());
    }

    public function test_the_merge_ledger_is_append_only(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $event = $this->service->merge($survivor, $merged, 'Duplicate.');

        $this->expectException(\RuntimeException::class);
        $event->update(['reason' => 'rewritten history']);
    }

    public function test_the_merge_ledger_cannot_be_deleted(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $event = $this->service->merge($survivor, $merged, 'Duplicate.');

        $this->expectException(\RuntimeException::class);
        $event->delete();
    }

    public function test_merging_an_already_merged_customer_is_refused(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $third = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->service->merge($survivor, $merged, 'Duplicate.');

        $this->expectException(CustomerMergeException::class);
        $this->service->merge($third, $merged->fresh(), 'Second merge of the same row.');
    }

    public function test_merging_a_customer_into_itself_is_refused(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->expectException(CustomerMergeException::class);
        $this->service->merge($customer, $customer, 'Nonsense.');
    }

    public function test_a_merge_cannot_be_reversed_twice(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $event = $this->service->merge($survivor, $merged, 'Duplicate.');
        $this->service->split($event, 'Reversed.');

        $this->expectException(CustomerMergeException::class);
        $this->service->split($event, 'Reversed again.');
    }

    public function test_a_split_event_cannot_itself_be_reversed(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $event = $this->service->merge($survivor, $merged, 'Duplicate.');
        $split = $this->service->split($event, 'Reversed.');

        $this->expectException(CustomerMergeException::class);
        $this->service->split($split, 'Nonsense.');
    }

    /**
     * Reversing out of order would restore links a later merge has since moved on, so the later
     * merge must be reversed first.
     */
    public function test_a_merge_superseded_by_a_later_merge_cannot_be_reversed_first(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $a = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $b = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $c = Customer::factory()->create(['tenant_id' => $tenant->id]);

        // b merges into a, then a merges into c.
        $first = $this->service->merge($a, $b, 'First.');
        $this->service->merge($c, $a->fresh(), 'Second.');

        $this->expectException(CustomerMergeException::class);
        $this->service->split($first, 'Out of order.');
    }

    /** An inactive customer must not silently become active through a merge reversal. */
    public function test_split_restores_the_previous_status_rather_than_forcing_active(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $merged = Customer::factory()->inactive()->create(['tenant_id' => $tenant->id]);

        $event = $this->service->merge($survivor, $merged, 'Duplicate.');
        $this->service->split($event, 'Reversed.');

        $this->assertSame(CustomerStatus::Inactive, $merged->fresh()?->status);
    }

    /** The snapshot must be a safe audit artifact, not a copy of the customer's contact details. */
    public function test_the_snapshot_contains_no_contact_pii(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'contact_email' => 'survivor@example.com',
        ]);
        $merged = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'contact_email' => 'merged@example.com',
        ]);

        $event = $this->service->merge($survivor, $merged, 'Duplicate.');
        $encoded = json_encode($event->snapshot);

        $this->assertStringNotContainsString('survivor@example.com', (string) $encoded);
        $this->assertStringNotContainsString('merged@example.com', (string) $encoded);
        $this->assertTrue($event->snapshot['merged']['has_contact_email']);
    }
}
