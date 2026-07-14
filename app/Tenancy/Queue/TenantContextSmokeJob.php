<?php

declare(strict_types=1);

namespace App\Tenancy\Queue;

use App\Tenancy\TenantCache;
use App\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Foundation-only fixture (NOT a business job): proves that a queued job carries and
 * rehydrates the tenant context it was dispatched under, and writes the observed tenant
 * id into a tenant-scoped cache key so a test can assert isolation (rule 30).
 */
final class TenantContextSmokeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TenantAware;

    public function __construct(public readonly string $cacheKey)
    {
        $this->captureTenantContext();
    }

    public function handle(TenantContext $context, TenantCache $cache): void
    {
        // Records the tenant observed *inside* the worker — must equal the dispatcher's.
        $cache->put($this->cacheKey, $context->tenantId(), 300);
    }
}
