<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Customer;
use App\Models\FeedbackItem;
use App\Models\SurveyInvitation;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The Customer 360 backfill must be additive, idempotent, and resumable — a reconcile re-run can
 * never create a second customer or change an existing link (rule 36; ADR 0068, contract §6).
 */
final class Sf10CommandsTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    private function itemWithRedeemedInvitation(Tenant $tenant, string $email): FeedbackItem
    {
        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);

        $invitation = SurveyInvitation::factory()->create([
            'tenant_id' => $tenant->id,
            'recipient_email' => $email,
            'completed_at' => now(),
        ]);

        $item->forceFill(['invitation_id' => $invitation->id])->save();

        return $item;
    }

    public function test_reconcile_links_an_item_whose_invitation_was_redeemed(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $item = $this->itemWithRedeemedInvitation($tenant, 'ana@example.com');
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertNotNull($item->fresh()?->customer_id);
        $this->assertSame(1, Customer::query()->count());
    }

    /** Re-running must be a no-op: the second pass creates nothing and changes nothing. */
    public function test_reconcile_is_idempotent(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $item = $this->itemWithRedeemedInvitation($tenant, 'ana@example.com');
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $firstLink = $item->fresh()?->customer_id;
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertSame($firstLink, $item->fresh()?->customer_id);
        $this->assertSame(1, Customer::query()->count());
    }

    /**
     * An invitation that was merely SENT proves nothing about who answered, so it must not create
     * an identity (rule 36).
     */
    public function test_reconcile_ignores_an_invitation_that_was_never_redeemed(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);
        $invitation = SurveyInvitation::factory()->create([
            'tenant_id' => $tenant->id,
            'recipient_email' => 'never@example.com',
            'completed_at' => null,
        ]);
        $item->forceFill(['invitation_id' => $invitation->id])->save();
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertNull($item->fresh()?->customer_id);
        $this->assertSame(0, Customer::query()->count());
    }

    /** An anonymous response is genuinely anonymous, not broken — it must stay unlinked. */
    public function test_reconcile_leaves_anonymous_items_unlinked_and_valid(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertNull($item->fresh()?->customer_id);
        $this->assertNotNull($item->fresh());
        $this->assertSame(0, Customer::query()->count());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $item = $this->itemWithRedeemedInvitation($tenant, 'ana@example.com');
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile --dry-run')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertNull($item->fresh()?->customer_id);
        $this->assertSame(0, Customer::query()->count());
    }

    /** Two items from the same person must converge on ONE canonical customer. */
    public function test_two_items_with_the_same_verified_email_converge_on_one_customer(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $first = $this->itemWithRedeemedInvitation($tenant, 'ana@example.com');
        $second = $this->itemWithRedeemedInvitation($tenant, 'ANA@Example.com');
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile')->assertSuccessful();

        $this->establishTenantContext($tenant);
        $this->assertSame(1, Customer::query()->count());
        $this->assertSame($first->fresh()?->customer_id, $second->fresh()?->customer_id);
    }

    public function test_reconcile_can_be_limited_to_a_single_tenant(): void
    {
        $tenantA = $this->provisionTenant();
        $this->establishTenantContext($tenantA);
        $itemA = $this->itemWithRedeemedInvitation($tenantA, 'a@example.com');
        $this->forgetTenantContext();

        $tenantB = $this->provisionTenant();
        $this->establishTenantContext($tenantB);
        $itemB = $this->itemWithRedeemedInvitation($tenantB, 'b@example.com');
        $this->forgetTenantContext();

        $this->artisan('aish:customer-reconcile --tenant='.$tenantA->id)->assertSuccessful();

        $this->establishTenantContext($tenantA);
        $this->assertNotNull($itemA->fresh()?->customer_id);
        $this->forgetTenantContext();

        $this->establishTenantContext($tenantB);
        $this->assertNull($itemB->fresh()?->customer_id);
    }
}
