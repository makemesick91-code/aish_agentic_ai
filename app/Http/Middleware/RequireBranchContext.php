<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards routes that require an active branch context. Fails closed to the branch
 * selector rather than proceeding without a branch (rule 30).
 */
final class RequireBranchContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(TenantContext::class)->hasBranch()) {
            return redirect()->route('branch.select');
        }

        return $next($request);
    }
}
