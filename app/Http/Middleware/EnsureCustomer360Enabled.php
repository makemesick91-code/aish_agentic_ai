<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Customers\CustomerEntitlements;
use App\Customers\Exceptions\CustomerEntitlementDeniedException;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every Customer 360 surface on the authoritative CUSTOMER_360_ENABLED entitlement
 * (fail-closed). This is the single capability guard for the domain, consistent with the merge
 * sub-guard (rule 36; contract §10).
 */
final class EnsureCustomer360Enabled
{
    public function __construct(
        private readonly CustomerEntitlements $entitlements,
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->entitlements->assertCustomer360Enabled($this->context->tenant());
        } catch (CustomerEntitlementDeniedException $e) {
            abort(403, $e->getMessage());
        }

        return $next($request);
    }
}
