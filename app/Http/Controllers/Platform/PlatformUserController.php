<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Models\PlatformRoleAssignment;
use App\Platform\Exceptions\LastPlatformSuperAdminException;
use App\Platform\Exceptions\PlatformSelfEscalationException;
use App\Platform\PlatformPermissions;
use App\Platform\PlatformRoleService;
use App\Platform\PlatformUserService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Platform user & role management — restricted to platform.users.manage (Super Admin only). All
 * mutations run through the services, which enforce no self-escalation, Super-Admin-grant
 * restriction, and last-Super-Admin protection regardless of caller (rule 31 §10.3).
 */
final class PlatformUserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::USERS_MANAGE);

        $assignments = PlatformRoleAssignment::with('user')->latest()->paginate(25);
        $roles = PlatformRole::cases();

        return view('platform.users.index', compact('assignments', 'roles'));
    }

    public function invite(Request $request, PlatformUserService $users): RedirectResponse
    {
        $this->authorize(PlatformPermissions::USERS_MANAGE);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(PlatformRole::values())],
        ]);

        try {
            $users->provision(
                $validated['name'],
                $validated['email'],
                PlatformRole::from($validated['role']),
                $request->user(),
            );
        } catch (PlatformSelfEscalationException $e) {
            return redirect()->route('platform.users.index')->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()->route('platform.users.index')->with('status', __('Platform operator provisioned; a reset link was sent.'));
    }

    public function removeRole(Request $request, PlatformRoleAssignment $assignment, PlatformRoleService $roles): RedirectResponse
    {
        $this->authorize(PlatformPermissions::USERS_MANAGE);

        try {
            $roles->remove($assignment->user, $assignment->role, $request->user());
        } catch (LastPlatformSuperAdminException|PlatformSelfEscalationException $e) {
            return redirect()->route('platform.users.index')->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()->route('platform.users.index')->with('status', __('Platform role removed.'));
    }
}
