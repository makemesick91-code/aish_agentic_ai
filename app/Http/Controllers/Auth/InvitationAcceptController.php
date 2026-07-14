<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Tenancy\Exceptions\InvalidInvitationException;
use App\Services\Tenancy\InvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Public, token-authenticated invitation acceptance. Carries NO tenant context: an
 * invitee may be establishing their account before any membership exists (rule 30).
 */
final class InvitationAcceptController extends Controller
{
    public function show(Request $request, string $token): View|Response
    {
        try {
            $invitation = app(InvitationService::class)->resolve($token);
        } catch (InvalidInvitationException) {
            return response()->view('invitations.invalid', [], 410);
        }

        $user = User::where('email', $invitation->email)->first();
        $claimable = $user === null || ! $user->hasVerifiedEmail();

        return view('invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
            'claimable' => $claimable,
            'email' => $invitation->email,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse|Response
    {
        try {
            $invitation = app(InvitationService::class)->resolve($token);
        } catch (InvalidInvitationException) {
            return response()->view('invitations.invalid', [], 410);
        }

        $existing = User::where('email', $invitation->email)->first();
        $claimable = $existing === null || ! $existing->hasVerifiedEmail();

        if ($claimable) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
            ]);

            $user = $existing ?? new User;
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'status' => UserStatus::Active,
            ])->save();
        } else {
            $authUser = $request->user();

            if ($authUser === null || $authUser->email !== $invitation->email) {
                return redirect()->route('login')
                    ->with('status', __('Please sign in to accept this invitation.'));
            }

            $user = $authUser;
        }

        try {
            app(InvitationService::class)->accept($token, $user);
        } catch (InvalidInvitationException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tenant.select');
    }
}
