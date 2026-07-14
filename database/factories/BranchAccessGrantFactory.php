<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\Tenant;
use App\Models\TenantMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchAccessGrant>
 */
class BranchAccessGrantFactory extends Factory
{
    protected $model = BranchAccessGrant::class;

    public function definition(): array
    {
        // Callers should pass explicit tenant_id / membership / branch so all three
        // belong to the same tenant; the defaults exist only for standalone use.
        return [
            'tenant_id' => Tenant::factory(),
            'tenant_membership_id' => TenantMembership::factory(),
            'branch_id' => Branch::factory(),
        ];
    }
}
