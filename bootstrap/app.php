<?php

use App\Http\Controllers\Health\LivenessController;
use App\Http\Controllers\Health\ReadinessController;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
        // Foundation security posture. Tenant/branch context middleware is introduced
        // in the SaaS Foundation implementation step (rule 03, rule 20; AFR-099).
        $middleware->append(SecurityHeaders::class);

        // Default proxy posture is trust-none (the safe default; rule 04). Trusted
        // proxy CIDRs (config/security.php: security.trusted_proxies) are wired in at
        // the deployment step when a load balancer/ingress is introduced (rule 23).
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // With APP_DEBUG=false Laravel renders production-safe error pages and never
        // leaks stack traces (rule 04, rule 10). No sensitive detail is added here.
    })->create();
