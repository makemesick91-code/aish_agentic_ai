<?php

declare(strict_types=1);

namespace App\Tenancy\Queue;

use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;

/**
 * Applied to every tenant-scoped queued job. The job captures the current tenant/branch/
 * actor at construction time (dispatch) into serialised properties, and re-attaches the
 * RestoreTenantContext middleware so the worker rehydrates and validates that context
 * before handling — never an ambient default (rule 03, rule 30; AFR queue isolation).
 *
 * Sensitive payloads must NOT be added to these properties; only identifiers travel.
 */
trait TenantAware
{
    public ?int $tenantId = null;

    public ?int $branchId = null;

    public ?int $actorId = null;

    public function captureTenantContext(): void
    {
        $context = app(TenantContext::class);

        if ($context->hasTenant()) {
            $this->tenantId = $context->tenantId();
            $this->branchId = $context->branchId();
            $this->actorId = Auth::id();
        }
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RestoreTenantContext];
    }
}
