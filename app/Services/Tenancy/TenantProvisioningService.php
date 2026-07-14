<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Audit\AuditRecorder;
use App\Authorization\Roles;
use App\Authorization\TenantRoleProvisioner;
use App\Enums\BranchStatus;
use App\Enums\MembershipStatus;
use App\Enums\TenantStatus;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Securely provisions a new tenant with its first Business Owner. Transactional and
 * idempotency-conscious: a duplicate slug fails safely, an existing suspended identity is
 * never silently reactivated, and no plaintext password is ever created or printed — the
 * owner sets their own password by accepting the invitation (rule 30; ADR 0013).
 */
final class TenantProvisioningService
{
    public function __construct(
        private readonly TenantRoleProvisioner $roleProvisioner,
        private readonly InvitationService $invitations,
        private readonly AuditRecorder $audit,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function provision(
        string $tenantName,
        string $ownerEmail,
        string $ownerName,
        ?string $slug = null,
        ?string $branchName = null,
        ?string $branchCode = null,
        string $timezone = 'Asia/Makassar',
        string $locale = 'en',
    ): ProvisioningResult {
        $email = Str::lower(trim($ownerEmail));
        $slug = Str::slug($slug ?? $tenantName);

        if ($slug === '') {
            throw new RuntimeException('A non-empty tenant slug is required.');
        }

        if (Tenant::where('slug', $slug)->exists()) {
            throw new RuntimeException("A tenant with slug [{$slug}] already exists.");
        }

        $existing = User::where('email', $email)->first();
        if ($existing !== null && ! $existing->isActive()) {
            throw new RuntimeException('An existing account for that email is suspended; refusing to provision silently.');
        }

        return DB::transaction(function () use ($tenantName, $slug, $email, $ownerName, $branchName, $branchCode, $timezone, $locale, $existing): ProvisioningResult {
            $tenant = new Tenant([
                'name' => $tenantName,
                'slug' => $slug,
                'status' => TenantStatus::Active,
                'timezone' => $timezone,
                'locale' => $locale,
            ]);
            $tenant->save();

            $tenant->settings()->create([
                'timezone' => $timezone,
                'locale' => $locale,
            ]);

            $branch = null;
            if ($branchName !== null && $branchName !== '') {
                $branch = new Branch([
                    'name' => $branchName,
                    'code' => $branchCode ?? Str::upper(Str::random(6)),
                    'status' => BranchStatus::Active,
                ]);
                $branch->tenant_id = $tenant->id;
                $branch->save();
            }

            $this->roleProvisioner->provision($tenant);

            // Create the owner identity if new — with an unusable random password the
            // owner will replace when accepting the invitation. Never printed or stored
            // in plaintext.
            $owner = $existing ?? tap(new User([
                'name' => $ownerName,
                'email' => $email,
            ]), function (User $user): void {
                $user->password = Hash::make(Str::random(64));
                $user->save();
            });

            // Invited owner membership; the invitation acceptance activates it.
            $membership = new TenantMembership([
                'status' => MembershipStatus::Invited,
                'all_branches' => true,
            ]);
            $membership->tenant_id = $tenant->id;
            $membership->user_id = $owner->id;
            $membership->save();

            $this->registrar->setPermissionsTeamId($tenant->id);
            $owner->assignRole(Roles::BUSINESS_OWNER);
            $this->registrar->forgetCachedPermissions();

            $invite = $this->invitations->invite(
                tenant: $tenant,
                email: $email,
                role: Roles::BUSINESS_OWNER,
                allBranches: true,
            );

            $this->audit->record('tenant.provisioned', [
                'tenant_id' => $tenant->id,
                'subject' => $tenant,
                'metadata' => ['slug' => $slug, 'owner_email' => $email, 'branch' => $branch?->code],
            ]);

            return new ProvisioningResult(
                tenant: $tenant,
                owner: $owner,
                ownerMembership: $membership,
                ownerInvitation: $invite['invitation'],
                branch: $branch,
            );
        });
    }
}
