<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Authorization\Permissions;
use App\Authorization\Roles;
use App\Http\Controllers\Controller;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Services\Tenancy\Exceptions\LastOwnerException;
use App\Services\Tenancy\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Membership lifecycle management for the current tenant. The last-active-owner invariant
 * is protected by MembershipService; this controller authorizes and reports truthfully
 * (rule 16, rule 20).
 */
final class MembershipController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TenantMembership::class);

        $memberships = TenantMembership::with('user')->get();

        $invitations = TenantInvitation::whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        $roles = Roles::names();

        return view('users.index', compact('memberships', 'invitations', 'roles'));
    }

    public function suspend(Request $request, TenantMembership $membership): RedirectResponse
    {
        $this->authorize('manage', $membership);

        try {
            app(MembershipService::class)->suspend($membership, $request->user()->id);
        } catch (LastOwnerException $e) {
            return back()->withErrors(['membership' => $e->getMessage()]);
        }

        return back()->with('status', __('Membership suspended.'));
    }

    public function reactivate(Request $request, TenantMembership $membership): RedirectResponse
    {
        $this->authorize('manage', $membership);

        try {
            app(MembershipService::class)->reactivate($membership, $request->user()->id);
        } catch (LastOwnerException $e) {
            return back()->withErrors(['membership' => $e->getMessage()]);
        }

        return back()->with('status', __('Membership reactivated.'));
    }

    public function revoke(Request $request, TenantMembership $membership): RedirectResponse
    {
        $this->authorize('manage', $membership);

        try {
            app(MembershipService::class)->revoke($membership, $request->user()->id);
        } catch (LastOwnerException $e) {
            return back()->withErrors(['membership' => $e->getMessage()]);
        }

        return back()->with('status', __('Membership access revoked.'));
    }

    public function setRole(Request $request, TenantMembership $membership): RedirectResponse
    {
        $this->authorize('assignRole', $membership);

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(Roles::names())],
        ]);

        // No self privilege change — prevents self-escalation and accidental self-demotion.
        if ($membership->user_id === $request->user()->id) {
            abort(403, __('You cannot change your own role.'));
        }

        // Granting the foundation owner role is a structural operation gated by a distinct
        // permission, not by roles.assign alone (rule 30; prevents vertical escalation).
        if ($validated['role'] === Roles::BUSINESS_OWNER
            && ! $request->user()->can(Permissions::ROLES_MANAGE_FOUNDATION)) {
            abort(403, __('Assigning the Business Owner role requires foundation role management.'));
        }

        try {
            app(MembershipService::class)->setRole($membership, $validated['role'], $request->user()->id);
        } catch (LastOwnerException $e) {
            return back()->withErrors(['role' => $e->getMessage()]);
        }

        return back()->with('status', __('Role updated.'));
    }
}
