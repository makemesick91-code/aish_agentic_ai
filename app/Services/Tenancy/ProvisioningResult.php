<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Models\User;

final readonly class ProvisioningResult
{
    public function __construct(
        public Tenant $tenant,
        public User $owner,
        public TenantMembership $ownerMembership,
        public TenantInvitation $ownerInvitation,
        public ?Branch $branch = null,
    ) {}
}
