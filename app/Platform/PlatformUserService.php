<?php

declare(strict_types=1);

namespace App\Platform;

use App\Audit\AuditRecorder;
use App\Enums\PlatformRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Provisions platform operators securely: no fixed password, nothing secret logged, onboarding
 * via a password-reset link. Re-invoking for an existing email is safe (idempotent role
 * assignment), and role assignment goes through PlatformRoleService so its invariants hold
 * (rule 31 §10.5).
 */
final class PlatformUserService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly PlatformRoleService $roles,
    ) {}

    public function provision(string $name, string $email, PlatformRole $role, ?User $actor = null): User
    {
        return DB::transaction(function () use ($name, $email, $role, $actor): User {
            $user = User::where('email', $email)->first();
            $created = false;

            if ($user === null) {
                // A strong random password the operator never sees; they set their own via reset.
                $user = new User([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::password(40)),
                ]);
                $user->status = UserStatus::Active;
                $user->save();
                $created = true;
            }

            $this->roles->assign($user, $role, $actor);

            $this->audit->record('platform.user.invited', [
                'tenant_id' => null,
                'actor_id' => $actor?->id,
                'subject' => $user,
                'metadata' => ['email' => $email, 'role' => $role->value, 'created' => $created],
            ]);

            if ($created) {
                // Secure onboarding: a reset link is the only way to set a password.
                Password::sendResetLink(['email' => $email]);
            }

            return $user;
        });
    }
}
