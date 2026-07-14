<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Authorization\Roles;
use App\Enums\MembershipStatus;
use App\Enums\TenantStatus;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Tenant-context resolution middleware: fail-closed selection, no enumeration of tenants
 * the user cannot access, state gating (suspended tenant/membership), and branch
 * selection safety (rule 03, rule 30).
 */
final class TenantContextResolutionTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_dashboard_redirects_to_tenant_select_without_a_session_tenant(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('tenant.select'));
    }

    public function test_single_membership_auto_selects_and_redirects_to_dashboard(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $response = $this->actingAs($user)->get('/tenant/select');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('current_tenant_id', $tenant->id);
    }

    public function test_a_tampered_session_tenant_the_user_is_not_a_member_of_is_refused(): void
    {
        $home = $this->provisionTenant();
        [$user] = $this->memberWithRole($home, Roles::BUSINESS_OWNER);
        $foreign = $this->provisionTenant();

        $response = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $foreign->id])
            ->get('/dashboard');

        // No existence disclosure: same redirect as "no selection", and it is cleared.
        $response->assertRedirect(route('tenant.select'));
        $response->assertSessionMissing('current_tenant_id');
    }

    public function test_a_suspended_tenant_cannot_be_entered(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $tenant->forceFill(['status' => TenantStatus::Suspended, 'suspended_at' => now()])->save();

        $response = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->get('/dashboard');

        $response->assertRedirect(route('tenant.select'));
        $response->assertSessionHasErrors('tenant');
        $response->assertSessionMissing('current_tenant_id');
    }

    public function test_a_suspended_membership_cannot_enter_the_tenant(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN, ['status' => MembershipStatus::Suspended]);

        $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->get('/dashboard')
            ->assertRedirect(route('tenant.select'));
    }

    public function test_a_revoked_membership_cannot_enter_the_tenant(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::CORPORATE_ADMIN, ['status' => MembershipStatus::Revoked]);

        $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->get('/dashboard')
            ->assertRedirect(route('tenant.select'));
    }

    public function test_switching_tenant_clears_the_selected_branch(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);

        $response = $this->actingAs($user)
            ->withSession([
                'current_tenant_id' => $tenant->id,
                'current_branch_id' => 999,
            ])
            ->post('/tenant/select', ['tenant' => $tenant->ulid]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionMissing('current_branch_id');
        $response->assertSessionHas('current_tenant_id', $tenant->id);
    }

    public function test_selecting_a_branch_the_member_cannot_access_is_forbidden(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BRANCH_MANAGER, ['all_branches' => false]);
        $branch = Branch::factory()->for($tenant)->create();

        $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branch/select', ['branch' => $branch->ulid])
            ->assertForbidden();

        // The branch is never stored as the working context on a forbidden selection.
        $this->assertNull(session('current_branch_id'));
    }

    public function test_an_inactive_branch_cannot_be_selected(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $branch = Branch::factory()->for($tenant)->inactive()->create();

        $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/branch/select', ['branch' => $branch->ulid])
            ->assertForbidden();
    }
}
