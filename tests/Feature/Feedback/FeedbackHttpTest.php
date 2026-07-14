<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Authorization\Roles;
use App\Enums\FeedbackStatus;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsFeedbackPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class FeedbackHttpTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsFeedbackPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    /**
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function tenantWithRole(string $role): array
    {
        $tenant = $this->provisionTenant();
        $this->provisionFeedbackPlan($tenant);
        [$user, $membership] = $this->memberWithRole($tenant, $role);
        $this->endRequestScope();

        return [$tenant, $user, $membership];
    }

    private function feedbackFor(Tenant $tenant, TenantMembership $membership): FeedbackItem
    {
        $this->establishTenantContext($tenant, $membership);
        $item = FeedbackItem::factory()->create();
        $this->endRequestScope();

        return $item;
    }

    public function test_owner_can_view_inbox_and_detail(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $item = $this->feedbackFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('feedback.index'))->assertOk()->assertSee('Feedback');
        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('feedback.show', $item))->assertOk()->assertSee($item->ulid);
    }

    public function test_cross_tenant_feedback_is_not_found(): void
    {
        [$tenantB, , $membershipB] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $itemB = $this->feedbackFor($tenantB, $membershipB);

        [$tenantA, $ownerA] = $this->tenantWithRole(Roles::BUSINESS_OWNER);

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get(route('feedback.show', $itemB))->assertNotFound();
        $this->endRequestScope();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post(route('feedback.status', $itemB), ['status' => FeedbackStatus::Triaged->value])
            ->assertNotFound();
    }

    public function test_owner_can_change_status(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $item = $this->feedbackFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('feedback.status', $item), ['status' => FeedbackStatus::Triaged->value])
            ->assertRedirect();

        $this->assertSame(FeedbackStatus::Triaged, $item->fresh()->status);
    }

    public function test_read_only_user_cannot_change_status(): void
    {
        [$tenant, $reader, $membership] = $this->tenantWithRole(Roles::READ_ONLY);
        $item = $this->feedbackFor($tenant, $membership);

        $this->actingAs($reader)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('feedback.status', $item), ['status' => FeedbackStatus::Triaged->value])
            ->assertForbidden();

        $this->assertSame(FeedbackStatus::New, $item->fresh()->status);
    }

    public function test_owner_can_add_note(): void
    {
        [$tenant, $owner, $membership] = $this->tenantWithRole(Roles::BUSINESS_OWNER);
        $item = $this->feedbackFor($tenant, $membership);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('feedback.notes.store', $item), ['body' => 'Called the customer.'])
            ->assertRedirect();

        $this->assertDatabaseHas('feedback_notes', ['feedback_item_id' => $item->id]);
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('feedback.index'))->assertRedirect();
    }
}
