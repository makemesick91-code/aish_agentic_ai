<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes the immutable tenant context for the request from the session-selected
 * tenant, but ONLY after verifying the authenticated user still has an active membership
 * and the tenant is active. It never falls back to "the first tenant" and never trusts a
 * tenant id from anywhere but a re-verified session selection (rule 03, rule 30).
 */
final class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $tenantId = $request->session()->get('current_tenant_id');

        if ($tenantId === null) {
            return redirect()->route('tenant.select');
        }

        $membership = $user->activeMembershipFor((int) $tenantId);

        if ($membership === null) {
            // Stale or revoked selection — clear and force reselection. Do not reveal
            // whether the tenant exists.
            $this->clearSelection($request);

            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::withoutGlobalScope(TenantScope::class)->find($tenantId);

        if ($tenant === null || ! $tenant->isActive()) {
            $this->clearSelection($request);

            return redirect()->route('tenant.select')->withErrors([
                'tenant' => __('That workspace is not available.'),
            ]);
        }

        $context = app(TenantContext::class);
        $context->establish($tenant, $membership);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        // Sanitised, tenant-aware log context for this request (no PII/secrets).
        Log::shareContext([
            'tenant_id' => $tenant->id,
            'actor_id' => $user->id,
        ]);

        return $next($request);
    }

    private function clearSelection(Request $request): void
    {
        $request->session()->forget(['current_tenant_id', 'current_branch_id']);
    }
}
