<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

/**
 * Server-side authorization for Customer 360. Every ability requires the specific permission AND
 * that the customer belongs to the current tenant AND that the actor may reach its provenance
 * branch. UI hiding is never sufficient; a platform role grants no tenant customer access
 * (rule 36; contract §10).
 */
class CustomerPolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::CUSTOMER_VIEW);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can(Permissions::CUSTOMER_VIEW) && $this->reachable($customer);
    }

    /** Reading contact PII is a separate decision from seeing the customer exists. */
    public function viewContact(User $user, Customer $customer): bool
    {
        return $user->can(Permissions::CUSTOMER_VIEW_CONTACT) && $this->reachable($customer);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::CUSTOMER_MANAGE);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can(Permissions::CUSTOMER_MANAGE) && $this->reachable($customer);
    }

    public function recordConsent(User $user, Customer $customer): bool
    {
        return $user->can(Permissions::CUSTOMER_MANAGE) && $this->reachable($customer);
    }

    /**
     * Merge authority is deliberately narrow AND requires reaching BOTH customers — merging a
     * customer you cannot see would let a branch-restricted operator reshape tenant-wide identity
     * (ADR 0072).
     */
    public function merge(User $user, Customer $survivor, ?Customer $merged = null): bool
    {
        if (! $user->can(Permissions::CUSTOMER_MERGE) || ! $this->reachable($survivor)) {
            return false;
        }

        return $merged === null || $this->reachable($merged);
    }

    /**
     * Reversing a merge mutates BOTH customers — it restores identities and feedback onto the
     * previously-merged one — so it requires reaching both, exactly like merge. Checking only the
     * survivor would let a branch-restricted actor rewrite identity state for a customer outside
     * their scope (rule 36; ADR 0072).
     */
    public function split(User $user, Customer $survivor, ?Customer $merged = null): bool
    {
        if (! $user->can(Permissions::CUSTOMER_MERGE) || ! $this->reachable($survivor)) {
            return false;
        }

        return $merged === null || $this->reachable($merged);
    }

    private function reachable(Customer $customer): bool
    {
        return $this->inCurrentTenant($customer) && $this->canAccessBranch($customer->primary_branch_id);
    }
}
