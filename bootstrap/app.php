<?php

use App\Http\Controllers\Health\LivenessController;
use App\Http\Controllers\Health\ReadinessController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireBranchContext;
use App\Http\Middleware\ResolveBranchContext;
use App\Http\Middleware\ResolveTenantContext;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Infra health probes are registered OUTSIDE the web middleware group:
            // no session, cookies, or CSRF — a load balancer polling /ready must not
            // create sessions or receive Set-Cookie (rule 10 truthful states, rule 11).
            Route::get('/live', LivenessController::class)->name('health.live');
            Route::get('/ready', ReadinessController::class)->name('health.ready');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Foundation security posture (rule 04).
        $middleware->append(SecurityHeaders::class);

        // Step 6 SaaS core: tenant/branch context + state enforcement (rule 03, rule 30).
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'tenant.context' => ResolveTenantContext::class,
            'branch.context' => ResolveBranchContext::class,
            'branch.required' => RequireBranchContext::class,
        ]);

        // The `tenant` group is the standard stack for any tenant-scoped surface:
        // authenticated + email-verified + active account, then a re-verified tenant
        // context and (optional) branch context. Order matters — context resolves only
        // after the actor is proven active and verified.
        $middleware->group('tenant', [
            'auth',
            'verified',
            'active',
            'tenant.context',
            'branch.context',
        ]);

        // The tenant context MUST be established before route-model binding runs, so the
        // fail-closed TenantScope has a context when resolving a tenant-owned bound model
        // (e.g. /branches/{branch}); otherwise every bound tenant route fails closed with
        // a missing-context error instead of a tenant-scoped 404. Raising these into the
        // priority list (in pipeline order, before SubstituteBindings) guarantees
        // auth → verified → active → tenant.context → branch.context → bindings (rule 03,
        // rule 30). Only tenant routes carry these middleware, so global ordering is safe.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: EnsureEmailIsVerified::class);
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: EnsureUserIsActive::class);
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: ResolveTenantContext::class);
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: ResolveBranchContext::class);

        // Default proxy posture is trust-none (the safe default; rule 04). Trusted
        // proxy CIDRs (config/security.php: security.trusted_proxies) are wired in at
        // the deployment step when a load balancer/ingress is introduced (rule 23).
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // With APP_DEBUG=false Laravel renders production-safe error pages and never
        // leaks stack traces (rule 04, rule 10). No sensitive detail is added here.
    })->create();
