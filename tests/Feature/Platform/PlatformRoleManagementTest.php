<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Enums\PlatformRole;
use App\Models\User;
use App\Platform\Exceptions\LastPlatformSuperAdminException;
use App\Platform\Exceptions\PlatformSelfEscalationException;
use App\Platform\PlatformRoleService;
use App\Platform\PlatformUserService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithPlatform;
use Tests\TestCase;

/**
 * Platform role invariants hold regardless of caller: no self-escalation, only a Super Admin
 * grants Super Admin, and the last Super Admin is protected. Provisioning is secure
 * (rule 31 §10.3, §10.5).
 */
final class PlatformRoleManagementTest extends TestCase
{
    use InteractsWithPlatform;
    use RefreshDatabase;

    private function roleService(): PlatformRoleService
    {
        return app(PlatformRoleService::class);
    }

    public function test_a_user_cannot_modify_their_own_platform_roles(): void
    {
        $super = $this->platformUser(PlatformRole::SuperAdmin);

        $this->expectException(PlatformSelfEscalationException::class);
        $this->roleService()->assign($super, PlatformRole::Admin, $super);
    }

    public function test_a_non_super_admin_cannot_grant_super_admin(): void
    {
        $admin = $this->platformUser(PlatformRole::Admin);
        $target = User::factory()->create();

        $this->expectException(PlatformSelfEscalationException::class);
        $this->roleService()->assign($target, PlatformRole::SuperAdmin, $admin);
    }

    public function test_a_super_admin_can_grant_super_admin(): void
    {
        $super = $this->platformUser(PlatformRole::SuperAdmin);
        $target = User::factory()->create();

        $this->roleService()->assign($target, PlatformRole::SuperAdmin, $super);

        $this->assertDatabaseHas('platform_role_assignments', [
            'user_id' => $target->id,
            'role' => PlatformRole::SuperAdmin->value,
        ]);
    }

    public function test_the_last_super_admin_cannot_be_removed(): void
    {
        $onlySuper = $this->platformUser(PlatformRole::SuperAdmin);
        $actor = User::factory()->create();

        $this->expectException(LastPlatformSuperAdminException::class);
        $this->roleService()->remove($onlySuper, PlatformRole::SuperAdmin, $actor);
    }

    public function test_a_super_admin_can_be_removed_when_another_exists(): void
    {
        $first = $this->platformUser(PlatformRole::SuperAdmin);
        $second = $this->platformUser(PlatformRole::SuperAdmin);

        $this->roleService()->remove($second, PlatformRole::SuperAdmin, $first);

        $this->assertDatabaseMissing('platform_role_assignments', [
            'user_id' => $second->id,
            'role' => PlatformRole::SuperAdmin->value,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform.role.removed']);
    }

    public function test_provisioning_is_secure_and_idempotent(): void
    {
        Notification::fake();
        $super = $this->platformUser(PlatformRole::SuperAdmin);
        $users = app(PlatformUserService::class);

        $user = $users->provision('New Operator', 'op@example.test', PlatformRole::Support, $super);

        $this->assertDatabaseHas('users', ['email' => 'op@example.test']);
        $this->assertDatabaseHas('platform_role_assignments', ['user_id' => $user->id, 'role' => PlatformRole::Support->value]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform.user.invited']);
        Notification::assertSentTo($user, ResetPassword::class);

        // Re-provisioning the same email does not create a duplicate user.
        $again = $users->provision('New Operator', 'op@example.test', PlatformRole::Support, $super);
        $this->assertSame($user->id, $again->id);
        $this->assertSame(1, User::where('email', 'op@example.test')->count());
    }
}
