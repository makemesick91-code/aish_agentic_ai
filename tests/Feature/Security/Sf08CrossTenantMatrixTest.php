<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Authorization\Permissions;
use App\Authorization\Roles;
use App\Enums\FeedbackStatus;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\FeedbackAttachment;
use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsFeedbackPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Hostile cross-tenant / cross-branch access matrix for Step 8. Every attempt to reach another
 * tenant's feedback item, attachment, or export — or another branch's item — must fail closed. These
 * are release blockers if they regress (rule 33; Step 8 §27, §30).
 */
final class Sf08CrossTenantMatrixTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsFeedbackPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    /**
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function tenantWithOwner(): array
    {
        $tenant = $this->provisionTenant();
        $this->provisionFeedbackPlan($tenant);
        [$owner, $membership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        return [$tenant, $owner, $membership];
    }

    public function test_tenant_a_cannot_reach_tenant_b_feedback(): void
    {
        [$tenantB, , $membershipB] = $this->tenantWithOwner();
        $this->establishTenantContext($tenantB, $membershipB);
        $itemB = FeedbackItem::factory()->create();
        $this->endRequestScope();

        [$tenantA, $ownerA] = $this->tenantWithOwner();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get(route('feedback.show', $itemB))->assertNotFound();
        $this->endRequestScope();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post(route('feedback.status', $itemB), ['status' => FeedbackStatus::Triaged->value])->assertNotFound();
        $this->endRequestScope();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post(route('feedback.assign', $itemB), ['assignee_id' => $ownerA->id])->assertNotFound();
        $this->endRequestScope();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->post(route('feedback.notes.store', $itemB), ['body' => 'x'])->assertNotFound();

        $this->assertSame(FeedbackStatus::New, $itemB->fresh()->status);
        $this->assertDatabaseCount('feedback_notes', 0);
    }

    public function test_tenant_a_cannot_download_tenant_b_attachment(): void
    {
        [$tenantB, , $membershipB] = $this->tenantWithOwner();
        $this->establishTenantContext($tenantB, $membershipB);
        $attachmentB = FeedbackAttachment::factory()->create();
        $this->endRequestScope();

        [$tenantA, $ownerA, $membershipA] = $this->tenantWithOwner();
        $this->establishTenantContext($tenantA, $membershipA);
        $feedbackA = FeedbackItem::factory()->create();
        $this->endRequestScope();

        // Cross-tenant attachment id against the attacker's own feedback item → not found.
        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get(route('feedback.attachments.download', [$feedbackA, $attachmentB]))->assertNotFound();
    }

    public function test_tenant_a_cannot_download_tenant_b_export(): void
    {
        [$tenantB, , $membershipB] = $this->tenantWithOwner();
        $this->establishTenantContext($tenantB, $membershipB);
        $exportB = FeedbackExport::factory()->ready()->create();
        $this->endRequestScope();

        [$tenantA, $ownerA] = $this->tenantWithOwner();

        $this->actingAs($ownerA)->withSession(['current_tenant_id' => $tenantA->id])
            ->get(route('feedback.exports.download', $exportB))->assertNotFound();
    }

    public function test_branch_restricted_user_cannot_open_another_branch_item(): void
    {
        [$tenant, , $ownerMembership] = $this->tenantWithOwner();
        $this->establishTenantContext($tenant, $ownerMembership);
        $branchA = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $branchB = Branch::factory()->create(['tenant_id' => $tenant->id]);
        $itemB = FeedbackItem::factory()->create(['branch_id' => $branchB->id]);
        $this->endRequestScope();

        [$manager, $managerMembership] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        BranchAccessGrant::factory()->create([
            'tenant_id' => $tenant->id,
            'tenant_membership_id' => $managerMembership->id,
            'branch_id' => $branchA->id,
        ]);
        $this->endRequestScope();

        $this->actingAs($manager)
            ->withSession(['current_tenant_id' => $tenant->id, 'current_branch_id' => $branchA->id])
            ->get(route('feedback.show', $itemB))->assertForbidden();
    }

    // F-1 regression: a member who is not the requester cannot download another member's export.
    public function test_export_download_requires_the_requester(): void
    {
        [$tenant, $owner, $ownerMembership] = $this->tenantWithOwner();
        $this->establishTenantContext($tenant, $ownerMembership);
        $export = FeedbackExport::factory()->ready()->create(['requested_by' => $owner->id]);
        $this->endRequestScope();

        [$other] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN); // holds feedback.export
        $this->endRequestScope();

        $this->actingAs($other)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('feedback.exports.download', $export))->assertForbidden();
    }

    // F-2 regression: the feedback surface is gated on the FEEDBACK_ENABLED entitlement.
    public function test_feedback_surface_requires_entitlement(): void
    {
        $tenant = $this->provisionTenant(); // NO feedback plan/subscription
        [$owner] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $this->endRequestScope();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->id])
            ->get(route('feedback.index'))->assertForbidden();
    }

    // F-3 regression: bulk-manage is not a blanket grant — the per-action permission is required.
    public function test_bulk_requires_the_per_action_permission(): void
    {
        [$tenant, , $ownerMembership] = $this->tenantWithOwner();
        $this->establishTenantContext($tenant, $ownerMembership);
        $item = FeedbackItem::factory()->create();
        $this->endRequestScope();

        [$user] = $this->memberWithoutRole($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->givePermissionTo(Permissions::FEEDBACK_BULK_MANAGE); // but NOT manage-status
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->endRequestScope();

        $this->actingAs($user->fresh())->withSession(['current_tenant_id' => $tenant->id])
            ->post(route('feedback.bulk'), ['action' => 'status', 'ids' => [$item->id], 'status' => FeedbackStatus::Triaged->value])
            ->assertForbidden();

        $this->assertSame(FeedbackStatus::New, $item->fresh()->status);
    }
}
