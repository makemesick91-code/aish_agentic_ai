<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Audit\AuditRecorder;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\BranchAccessGrant;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Services\Tenancy\Exceptions\InvalidInvitationException;
use App\Tenancy\TenantScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Issues and accepts tenant invitations. Only the SHA-256 hash of a 64-char random token
 * is stored; the plaintext exists only long enough to build the acceptance URL. Reads
 * bypass the TenantScope on purpose (invitations are resolved before any tenant context
 * exists) — this class is allowlisted infrastructure (rule 30).
 */
final class InvitationService
{
    private const DEFAULT_EXPIRY_DAYS = 7;

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @return array{invitation: TenantInvitation, plain_token: string}
     */
    public function invite(
        Tenant $tenant,
        string $email,
        string $role,
        bool $allBranches = true,
        ?Branch $branch = null,
        ?User $invitedBy = null,
    ): array {
        $email = Str::lower(trim($email));
        $plain = Str::random(64);
        $hash = hash('sha256', $plain);

        $invitation = DB::transaction(function () use ($tenant, $email, $role, $allBranches, $branch, $invitedBy, $hash): TenantInvitation {
            // Supersede any still-pending invitation for the same email in this tenant.
            TenantInvitation::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $invitation = new TenantInvitation([
                'email' => $email,
                'role' => $role,
                'all_branches' => $allBranches,
                'branch_id' => $branch?->id,
                'expires_at' => now()->addDays((int) config('tenancy.invitation_expiry_days', self::DEFAULT_EXPIRY_DAYS)),
            ]);
            $invitation->tenant_id = $tenant->id;
            $invitation->token_hash = $hash;
            $invitation->invited_by = $invitedBy?->id;
            $invitation->save();

            return $invitation;
        });

        Notification::route('mail', $email)->notify(
            new TenantInvitationNotification($tenant, $invitation, $plain)
        );

        $this->audit->record('tenant.invitation.created', [
            'tenant_id' => $tenant->id,
            'actor_id' => $invitedBy?->id,
            'subject' => $invitation,
            'metadata' => ['email' => $email, 'role' => $role, 'all_branches' => $allBranches],
        ]);

        return ['invitation' => $invitation, 'plain_token' => $plain];
    }

    /** Resolve a pending invitation by plaintext token, or fail. Does not consume it. */
    public function resolve(string $plainToken): TenantInvitation
    {
        $invitation = TenantInvitation::withoutGlobalScope(TenantScope::class)
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if ($invitation === null || ! $invitation->isPending()) {
            throw InvalidInvitationException::make();
        }

        return $invitation;
    }

    /**
     * Consume the invitation for the given user (whose email MUST match) and attach an
     * active membership + role. One-time and race-safe: the row is locked and re-checked
     * inside the transaction, so a concurrent second acceptance fails.
     */
    public function accept(string $plainToken, User $user): TenantMembership
    {
        $hash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($hash, $user): TenantMembership {
            $invitation = TenantInvitation::withoutGlobalScope(TenantScope::class)
                ->where('token_hash', $hash)
                ->lockForUpdate()
                ->first();

            if ($invitation === null || ! $invitation->isPending()) {
                throw InvalidInvitationException::make();
            }

            if (Str::lower($user->email) !== Str::lower($invitation->email)) {
                // The invitation cannot be transferred to a different email.
                throw InvalidInvitationException::make();
            }

            $membership = TenantMembership::withoutGlobalScope(TenantScope::class)
                ->firstOrNew(['tenant_id' => $invitation->tenant_id, 'user_id' => $user->id]);
            $membership->tenant_id = $invitation->tenant_id;
            $membership->status = MembershipStatus::Active;
            $membership->all_branches = $invitation->all_branches;
            $membership->accepted_at = now();
            $membership->save();

            if (! $invitation->all_branches && $invitation->branch_id !== null) {
                BranchAccessGrant::withoutGlobalScope(TenantScope::class)->firstOrCreate([
                    'tenant_membership_id' => $membership->id,
                    'branch_id' => $invitation->branch_id,
                ], ['tenant_id' => $invitation->tenant_id]);
            }

            // Tenant-scoped role assignment.
            app(PermissionRegistrar::class)->setPermissionsTeamId($invitation->tenant_id);
            $user->assignRole($invitation->role);

            // Acceptance proves control of the invited mailbox → email is verified.
            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $invitation->forceFill(['accepted_at' => now()])->save();

            $this->audit->record('tenant.invitation.accepted', [
                'tenant_id' => $invitation->tenant_id,
                'actor_id' => $user->id,
                'subject' => $membership,
                'metadata' => ['role' => $invitation->role],
            ]);

            return $membership;
        });
    }

    public function revoke(TenantInvitation $invitation, ?User $actor = null): void
    {
        if (! $invitation->isPending()) {
            return;
        }

        $invitation->forceFill(['revoked_at' => now()])->save();

        $this->audit->record('tenant.invitation.revoked', [
            'tenant_id' => $invitation->tenant_id,
            'actor_id' => $actor?->id,
            'subject' => $invitation,
        ]);
    }
}
