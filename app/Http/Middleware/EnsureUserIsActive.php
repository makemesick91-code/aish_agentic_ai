<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Audit\AuditRecorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fails closed for a globally suspended account: an existing session cannot continue to
 * access protected surfaces after the account is suspended (rule 30 state enforcement).
 */
final class EnsureUserIsActive
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isActive()) {
            $this->audit->record('auth.session.rejected_suspended_user', [
                'actor_id' => $user->id,
                'metadata' => ['reason' => 'user_suspended'],
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('This account is not available.'),
            ]);
        }

        return $next($request);
    }
}
