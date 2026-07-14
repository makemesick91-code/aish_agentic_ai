<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PlatformRole;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Models\NotificationDelivery;
use App\Models\PlatformRoleAssignment;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Platform\Exceptions\LastPlatformSuperAdminException;
use App\Platform\PlatformAccess;
use App\Platform\PlatformPermissions;
use App\Platform\PlatformRoleService;
use App\Platform\TenantAdministrationService;
use App\Services\Notifications\Exceptions\CrossTenantRecipientException;
use App\Services\Notifications\NotificationDispatcher;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\PlanService;
use App\Subscriptions\SubscriptionService;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Exercises the SPRINT-SF-05 foundations against REAL infrastructure (PostgreSQL + Redis cache
 * + Redis queue) with positive and negative checks — an open socket is not proof (rule 29,
 * rule 31). Intended to run from a clean checkout by scripts/runtime/verify-sf-05.sh. Uses only
 * generated, non-sensitive data and cleans up what it creates.
 */
final class VerifySf05Command extends Command
{
    protected $signature = 'aish:verify-sf-05';

    protected $description = 'Verify SF-05 notification/subscription/platform foundations against real PostgreSQL + Redis.';

    private int $failures = 0;

    public function handle(): int
    {
        $suffix = Str::random(6);
        $a = Tenant::factory()->create(['name' => "SF05 A {$suffix}"]);
        $b = Tenant::factory()->create(['name' => "SF05 B {$suffix}"]);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        TenantMembership::factory()->create(['tenant_id' => $a->id, 'user_id' => $userA->id]);
        TenantMembership::factory()->create(['tenant_id' => $b->id, 'user_id' => $userB->id]);

        $plans = app(PlanService::class);
        $resolver = app(EntitlementResolver::class);
        $meter = app(UsageMeter::class);
        $dispatcher = app(NotificationDispatcher::class);
        $access = app(PlatformAccess::class);

        // --- Subscription + entitlement -------------------------------------------------
        $plan = $plans->create(['code' => "verify_{$suffix}", 'version' => 1, 'name' => 'Verify Plan']);
        $plans->activate($plan);
        $plans->setFeature($plan, EntitlementKeys::API_ENABLED, true);
        $plans->setFeature($plan, EntitlementKeys::BRANCHES_MAX, 3);
        app(SubscriptionService::class)->start($a, $plan, SubscriptionStatus::Active, null, 30);
        TenantSubscription::factory()->expired()->create(['tenant_id' => $b->id, 'plan_id' => $plan->id]);

        $this->assert($resolver->resolve($a, EntitlementKeys::API_ENABLED)->allowed, 'active subscription grants a boolean entitlement');
        $this->assert(! $resolver->resolve($a, 'made.up.key')->allowed, 'unknown entitlement fails closed');
        $this->assert(! $resolver->resolve($b, EntitlementKeys::API_ENABLED)->allowed, 'expired subscription denies entitlements');

        // --- Usage metering: idempotent + tenant-isolated -------------------------------
        $meter->record($a, MeterKeys::FOUNDATION_VERIFICATION, 2, 'verify-op');
        $meter->record($a, MeterKeys::FOUNDATION_VERIFICATION, 2, 'verify-op');
        $this->assert($meter->total($a, MeterKeys::FOUNDATION_VERIFICATION) === 2, 'usage recording is idempotent');
        $meter->record($b, MeterKeys::FOUNDATION_VERIFICATION, 5, 'verify-op');
        $this->assert($meter->total($b, MeterKeys::FOUNDATION_VERIFICATION) === 5, 'usage is isolated across tenants');

        // --- Notification delivery over the REAL Redis queue ----------------------------
        // Per-run unique keys: dedup_key is global, so fixed keys would collide across runs.
        $dispatcher->dispatch(NotificationType::MembershipActivated, $userA, "verify-notif-{$suffix}", 'Verify', tenant: $a, body: 'hello');
        $this->call('queue:work', ['--stop-when-empty' => true]);
        $inApp = NotificationDelivery::where('recipient_id', $userA->id)->where('channel', 'in_app')->first();
        $this->assert($inApp !== null && $inApp->fresh()->state->value === 'sent', 'notification delivered via real Redis queue (sent)');

        $dispatcher->dispatch(NotificationType::MembershipActivated, $userA, "verify-notif-{$suffix}", 'Verify', tenant: $a);
        $this->assert(
            NotificationDelivery::where('recipient_id', $userA->id)->where('channel', 'in_app')->count() === 1,
            'duplicate dispatch yields exactly one logical delivery',
        );

        try {
            $dispatcher->dispatch(NotificationType::MembershipActivated, $userB, "verify-x-{$suffix}", 'x', tenant: $a);
            $this->bad('a tenant cannot notify a non-member');
        } catch (CrossTenantRecipientException) {
            $this->ok('a tenant cannot notify a non-member');
        }

        $dispatcher->dispatch(NotificationType::MembershipActivated, $userB, "verify-b-{$suffix}", 'B', tenant: $b);
        $this->call('queue:work', ['--stop-when-empty' => true]);
        $this->assert(
            NotificationDelivery::query()->forTenant($a->id)->where('recipient_id', $userB->id)->count() === 0,
            'tenant A cannot see tenant B notifications',
        );

        // --- Security precedence: commercial state never overrides security -------------
        app(TenantAdministrationService::class)->suspend($a, 'verification', null);
        $subA = TenantSubscription::withoutGlobalScope(TenantScope::class)->where('tenant_id', $a->id)->first();
        $this->assert($a->fresh()->status === TenantStatus::Suspended, 'tenant suspension applies');
        $this->assert($subA !== null && $subA->status === SubscriptionStatus::Active, 'commercial state unchanged by security suspension');

        // --- Platform plane separation --------------------------------------------------
        $super = User::factory()->create();
        PlatformRoleAssignment::factory()->role(PlatformRole::SuperAdmin)->create(['user_id' => $super->id]);
        $support = User::factory()->create();
        PlatformRoleAssignment::factory()->role(PlatformRole::Support)->create(['user_id' => $support->id]);

        $this->assert($access->has($super, PlatformPermissions::USERS_MANAGE), 'super admin can manage platform users');
        $this->assert(! $access->has($support, PlatformPermissions::PLANS_MANAGE), 'support cannot manage plans');
        $this->assert($super->activeMembershipFor($a->id) === null, 'a platform role grants no tenant access');
        $this->assert(! $access->hasAnyRole($userA->fresh()), 'a tenant user has no platform access');

        try {
            app(PlatformRoleService::class)->remove($super, PlatformRole::SuperAdmin, $userA);
            $this->bad('last super admin is protected');
        } catch (LastPlatformSuperAdminException) {
            $this->ok('last super admin is protected');
        }

        // --- No impersonation surface ---------------------------------------------------
        $hasImpersonation = false;
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (str_contains(Str::lower($route->uri().'|'.(string) $route->getName()), 'impersonat')) {
                $hasImpersonation = true;
                break;
            }
        }
        $this->assert(! $hasImpersonation, 'no impersonation route exists');

        $this->cleanup([$a, $b], [$userA, $userB, $super, $support]);

        if ($this->failures > 0) {
            $this->error("SF-05 verification FAILED with {$this->failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('SF-05 verification passed against real PostgreSQL + Redis.');

        return self::SUCCESS;
    }

    /**
     * @param  list<Tenant>  $tenants
     * @param  list<User>  $users
     */
    private function cleanup(array $tenants, array $users): void
    {
        try {
            // Deleting users cascades their notification deliveries, memberships, and platform
            // assignments; deleting tenants cascades their owned rows.
            foreach ($users as $user) {
                User::whereKey($user->id)->delete();
            }
            foreach ($tenants as $tenant) {
                Tenant::whereKey($tenant->id)->get()->each->delete();
            }
        } catch (\Throwable $e) {
            $this->line('  <comment>cleanup best-effort: '.$e->getMessage().'</comment>');
        }
    }

    private function assert(bool $condition, string $label): void
    {
        $condition ? $this->ok($label) : $this->bad($label);
    }

    private function ok(string $label): void
    {
        $this->line("  <info>✓</info> {$label}");
    }

    private function bad(string $label): void
    {
        $this->failures++;
        $this->line("  <error>✗</error> {$label}");
    }
}
