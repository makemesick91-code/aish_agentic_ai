<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Authorization\Permissions;
use App\Authorization\Roles;
use App\Http\Controllers\Controller;
use App\Models\TenantInvitation;
use App\Services\Tenancy\InvitationService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Issue and revoke tenant invitations. Auditing is handled inside InvitationService; this
 * controller only authorizes, validates, and delegates (rule 20).
 */
final class InvitationController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TenantInvitation::class);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(Roles::names())],
            'all_branches' => ['sometimes', 'boolean'],
        ]);

        // Inviting a foundation owner is a structural operation gated by a distinct
        // permission, not by users.invite alone (rule 30; prevents vertical escalation).
        if ($validated['role'] === Roles::BUSINESS_OWNER
            && ! $request->user()->can(Permissions::ROLES_MANAGE_FOUNDATION)) {
            abort(403, __('Inviting a Business Owner requires foundation role management.'));
        }

        $allBranches = (bool) ($validated['all_branches'] ?? true);

        app(InvitationService::class)->invite(
            app(TenantContext::class)->tenant(),
            $validated['email'],
            $validated['role'],
            $allBranches,
            null,
            $request->user(),
        );

        return back()->with('status', __('Invitation sent.'));
    }

    public function destroy(Request $request, TenantInvitation $invitation): RedirectResponse
    {
        $this->authorize('revoke', $invitation);

        app(InvitationService::class)->revoke($invitation, $request->user());

        return back()->with('status', __('Invitation revoked.'));
    }
}
