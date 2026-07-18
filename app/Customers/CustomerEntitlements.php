<?php

declare(strict_types=1);

namespace App\Customers;

use App\Customers\Exceptions\CustomerEntitlementDeniedException;
use App\Models\Tenant;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;

/**
 * The single place Customer 360 entitlement decisions are made. It delegates entirely to the
 * authoritative EntitlementResolver — no duplicated plan logic — and every check fails closed: an
 * ungranted or unknown key denies. A commercial restriction never overrides a security suspension
 * (the resolver already encodes suspension precedence) (rule 36; contract §10).
 */
final class CustomerEntitlements
{
    public function __construct(private readonly EntitlementResolver $resolver) {}

    public function assertCustomer360Enabled(Tenant $tenant): void
    {
        $this->assertBooleanGranted($tenant, EntitlementKeys::CUSTOMER_360_ENABLED);
    }

    /**
     * Merge requires the base 360 entitlement too — a plan cannot grant the destructive-looking
     * capability while withholding the view it operates on.
     */
    public function assertMergeEnabled(Tenant $tenant): void
    {
        $this->assertCustomer360Enabled($tenant);
        $this->assertBooleanGranted($tenant, EntitlementKeys::CUSTOMER_360_MERGE_ENABLED);
    }

    public function customer360Enabled(Tenant $tenant): bool
    {
        return $this->resolver->resolve($tenant, EntitlementKeys::CUSTOMER_360_ENABLED)->allowed;
    }

    public function mergeEnabled(Tenant $tenant): bool
    {
        return $this->customer360Enabled($tenant)
            && $this->resolver->resolve($tenant, EntitlementKeys::CUSTOMER_360_MERGE_ENABLED)->allowed;
    }

    private function assertBooleanGranted(Tenant $tenant, string $key): void
    {
        $decision = $this->resolver->resolve($tenant, $key);

        if (! $decision->allowed) {
            throw CustomerEntitlementDeniedException::notGranted($key, $decision->reasonCode);
        }
    }
}
