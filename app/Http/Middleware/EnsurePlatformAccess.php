<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Platform\PlatformAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the entire platform-admin surface: the actor must be an authenticated, active user
 * holding at least one platform role. It deliberately establishes NO tenant context — platform
 * access never implies tenant-data access (rule 31 §10.4). A specific platform permission is
 * additionally required per route.
 */
final class EnsurePlatformAccess
{
    public function __construct(private readonly PlatformAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isActive() || ! $this->access->hasAnyRole($user)) {
            abort(403);
        }

        return $next($request);
    }
}
