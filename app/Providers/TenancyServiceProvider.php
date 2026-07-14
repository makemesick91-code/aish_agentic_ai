<?php

declare(strict_types=1);

namespace App\Providers;

use App\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the tenancy layer. TenantContext is bound as a *scoped* instance so the
 * framework resets it between HTTP requests and between queue jobs — the primary defence
 * against tenant-context leaking across a long-running worker (rule 03, rule 30).
 */
class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
    }
}
