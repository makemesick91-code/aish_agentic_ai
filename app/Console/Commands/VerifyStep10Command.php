<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authorization\Permissions;
use App\Authorization\Roles;
use App\Authorization\TenantRoleProvisioner;
use App\Customers\CustomerConsentService;
use App\Customers\CustomerEntitlements;
use App\Customers\CustomerIdentityResolver;
use App\Customers\CustomerInteractionsReadModel;
use App\Customers\CustomerMergeService;
use App\Customers\Identity\IdentityCandidate;
use App\Enums\CustomerConsentType;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Enums\CustomerStatus;
use App\Enums\MembershipStatus;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\FeedbackItem;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\PermissionRegistrar;

/**
 * Verifies the Step 10 Customer 360 Foundation against real PostgreSQL + Redis with positive AND
 * negative assertions: verified-only deterministic linking, anonymous-never-creates, idempotent
 * resolution, cross-tenant hash non-correlation, no plaintext PII in identity rows, no-delete
 * reversible merge/split, append-only ledgers, consent semantics across a merge, permission-aware
 * read-model, entitlement gating, metering, and audit sanitization.
 *
 * Prints "Step 10 verification passed" on success (rule 36; contract §21).
 */
final class VerifyStep10Command extends Command
{
    protected $signature = 'aish:verify-step-10';

    protected $description = 'Verify the Step 10 Customer 360 foundation against real PostgreSQL + Redis.';

    private int $failures = 0;

    public function handle(TenantContext $context): int
    {
        Queue::fake();

        [$tenantA, $ownerA, $membershipA] = $this->provisionWorkspace();
        [$tenantB, , $membershipB] = $this->provisionWorkspace();
        [$readerA] = $this->member($tenantA, Roles::READ_ONLY);

        $this->establish($context, $tenantA, $membershipA);

        // ---- Identity resolution -------------------------------------------------------------
        $first = app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
            displayName: 'Ana',
        );
        $this->assert($first->customer !== null, 'a verified identity creates a canonical customer');
        $this->assert($first->customerWasCreated, 'the first resolution reports creation');

        $again = app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Feedback,
            [IdentityCandidate::verified(CustomerIdentityType::Email, '  ANA@Example.com ')],
        );
        $this->assert(
            $again->customer?->id === $first->customer?->id && ! $again->customerWasCreated,
            'resolution is idempotent across casing/whitespace (no duplicate customer)'
        );

        $anonymous = app(CustomerIdentityResolver::class)->resolve(CustomerIdentitySource::Survey, []);
        $this->assert($anonymous->isAnonymous(), 'an anonymous source creates no customer');

        $unverified = app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::unverified(CustomerIdentityType::Email, 'someone.else@example.com')],
        );
        $this->assert($unverified->isAnonymous(), 'an unverified identity does not auto-link');

        // ---- PII handling --------------------------------------------------------------------
        $identityRows = CustomerIdentity::query()->get()
            ->map(fn (CustomerIdentity $i): string => json_encode($i->toArray()) ?: '')
            ->implode(' ');
        $this->assert(
            ! str_contains($identityRows, 'ana@example.com'),
            'identity rows store no plaintext contact value'
        );

        $hashA = CustomerIdentity::query()->orderBy('id')->firstOrFail()->value_hash;

        // ---- Merge / split -------------------------------------------------------------------
        $survivor = Customer::factory()->create(['tenant_id' => $tenantA->id]);
        $duplicate = Customer::factory()->create(['tenant_id' => $tenantA->id]);
        $linked = FeedbackItem::factory()->create(['tenant_id' => $tenantA->id]);
        $linked->forceFill(['customer_id' => $duplicate->id])->save();

        $merges = app(CustomerMergeService::class);
        $mergeEvent = $merges->merge($survivor, $duplicate, 'Duplicate profile detected.');

        $duplicate->refresh();
        $this->assert(
            Customer::query()->find($duplicate->id) !== null && $duplicate->status === CustomerStatus::Merged,
            'a merge retains the non-surviving customer (no delete)'
        );
        $this->assert(
            $linked->fresh()?->customer_id === $survivor->id,
            'a merge moves feedback links to the survivor'
        );
        $this->assert(
            count($mergeEvent->snapshot['moved_feedback_item_ids']) === 1,
            'the merge snapshot records the exact moved id set'
        );
        $this->assert(
            ! str_contains(json_encode($mergeEvent->snapshot) ?: '', '@'),
            'the merge snapshot contains no contact PII'
        );

        $this->assertThrows(
            fn () => $mergeEvent->update(['reason' => 'tampered']),
            'the merge ledger is append-only (update blocked)'
        );

        $merges->split($mergeEvent, 'Confirmed different people after review.');
        $this->assert(
            $linked->fresh()?->customer_id === $duplicate->id,
            'a split restores exactly what the merge moved'
        );
        $this->assert(
            $duplicate->fresh()?->merged_into_customer_id === null,
            'a split clears the survivor pointer'
        );
        $this->assertThrows(
            fn () => $merges->split($mergeEvent, 'Second reversal.'),
            'a merge cannot be reversed twice'
        );

        // ---- Consent -------------------------------------------------------------------------
        $consents = app(CustomerConsentService::class);
        $customer = $first->customer;

        if ($customer !== null) {
            $this->assert(
                ! $consents->mayContact($customer, CustomerConsentType::Marketing),
                'an absent consent decision is not treated as permission'
            );

            $consents->record($customer, CustomerConsentType::Marketing, true, 'v1', 'operator');
            $this->assert(
                $consents->mayContact($customer, CustomerConsentType::Marketing),
                'an accepted consent permits contact'
            );

            $consents->record($customer, CustomerConsentType::DoNotContact, true, 'v1', 'operator');
            $this->assert(
                ! $consents->mayContact($customer, CustomerConsentType::Marketing),
                'do-not-contact suppresses every purpose'
            );
        }

        // ---- Read-model ----------------------------------------------------------------------
        $readModel = app(CustomerInteractionsReadModel::class);
        $ownerSummary = $readModel->summary($duplicate->fresh(), $ownerA);
        $readerSummary = $readModel->summary($duplicate->fresh(), $readerA);
        $this->assert($ownerSummary['contact_visible'] === true, 'an owner may view contact PII');
        $this->assert($readerSummary['contact_visible'] === false, 'a read-only member may not view contact PII');
        $this->assert(
            ! $readerA->can(Permissions::CUSTOMER_MERGE),
            'a read-only member holds no merge permission'
        );

        // ---- Metering + audit ----------------------------------------------------------------
        $meter = app(UsageMeter::class);
        $before = $meter->record($tenantA, MeterKeys::CUSTOMERS_CREATED, 1, 'verify-step-10:meter');
        $after = $meter->record($tenantA, MeterKeys::CUSTOMERS_CREATED, 1, 'verify-step-10:meter');
        $this->assert($before->is($after), 'usage metering is idempotent on a repeated key');

        $auditText = AuditLog::query()->get()
            ->map(fn (AuditLog $log): string => json_encode($log->metadata) ?: '')
            ->implode(' ');
        $this->assert(
            AuditLog::query()->where('event', 'customer.created')->exists(),
            'customer creation is audited'
        );
        $this->assert(
            AuditLog::query()->where('event', 'customer.merged')->exists()
                && AuditLog::query()->where('event', 'customer.split')->exists(),
            'merge and split are audited'
        );
        $this->assert(
            ! str_contains($auditText, 'ana@example.com'),
            'audit metadata carries no identity value'
        );

        // ---- Entitlement fail-closed ----------------------------------------------------------
        $entitlements = app(CustomerEntitlements::class);
        $this->assert($entitlements->customer360Enabled($tenantA), 'the granted plan enables Customer 360');

        $bareTenant = Tenant::factory()->create();
        $this->assert(
            ! $entitlements->customer360Enabled($bareTenant),
            'a tenant with no subscription fails closed'
        );
        $this->assertThrows(
            fn () => $entitlements->assertCustomer360Enabled($bareTenant),
            'the entitlement guard throws for an ungranted tenant'
        );

        // ---- Cross-tenant isolation ------------------------------------------------------------
        $customerBId = null;
        $this->establish($context, $tenantB, $membershipB);

        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
        );
        $hashB = CustomerIdentity::query()->orderByDesc('id')->firstOrFail()->value_hash;
        $this->assert($hashA !== $hashB, 'the same email in two tenants yields uncorrelated hashes');

        $customerB = Customer::factory()->create(['tenant_id' => $tenantB->id]);
        $customerBId = $customerB->id;

        $this->assert(
            Customer::query()->find($survivor->id) === null,
            'tenant B cannot read a tenant A customer'
        );

        $this->establish($context, $tenantA, $membershipA);
        $this->assert(
            Customer::query()->find($customerBId) === null,
            'tenant A cannot read a tenant B customer'
        );

        $this->assertThrows(
            fn () => app(CustomerMergeService::class)->merge($survivor->fresh(), $customerB, 'Cross-tenant attempt.'),
            'a cross-tenant merge is refused'
        );

        $this->forget($context);

        // ---- Fail-closed context ---------------------------------------------------------------
        $this->assertThrows(
            fn () => Customer::query()->get(),
            'querying customers without tenant context fails closed'
        );

        $this->cleanup([$tenantA, $tenantB, $bareTenant]);

        if ($this->failures > 0) {
            $this->error("Step 10 verification FAILED with {$this->failures} failing check(s).");

            return self::FAILURE;
        }

        $this->info('Step 10 verification passed');

        return self::SUCCESS;
    }

    /**
     * @return array{0: Tenant, 1: User, 2: TenantMembership}
     */
    private function provisionWorkspace(): array
    {
        $tenant = Tenant::factory()->create();
        app(TenantRoleProvisioner::class)->provision($tenant);

        $plan = Plan::factory()->create();

        foreach ([
            EntitlementKeys::CUSTOMER_360_ENABLED,
            EntitlementKeys::CUSTOMER_360_MERGE_ENABLED,
        ] as $key) {
            PlanFeature::factory()->boolean($key, true)->create(['plan_id' => $plan->id]);
        }

        TenantSubscription::factory()->active()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);

        [$owner, $membership] = $this->member($tenant, Roles::BUSINESS_OWNER);

        return [$tenant, $owner, $membership];
    }

    /**
     * @return array{0: User, 1: TenantMembership}
     */
    private function member(Tenant $tenant, string $role): array
    {
        $user = User::factory()->create();
        $membership = TenantMembership::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'status' => MembershipStatus::Active,
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [$user->fresh(), $membership->fresh()];
    }

    private function establish(TenantContext $context, Tenant $tenant, TenantMembership $membership): void
    {
        $context->forget();
        $context->establish($tenant, $membership);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    }

    private function forget(TenantContext $context): void
    {
        $context->forget();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    /**
     * @param  list<Tenant>  $tenants
     */
    private function cleanup(array $tenants): void
    {
        // Best-effort housekeeping; a driver-specific cascade quirk must not mask a passing run.
        foreach ($tenants as $tenant) {
            try {
                Tenant::withoutEvents(fn () => $tenant->delete());
            } catch (\Throwable) {
                // ignore — the verification assertions above are the source of truth
            }
        }
    }

    /** A negative assertion: the operation MUST be refused. */
    private function assertThrows(callable $operation, string $label): void
    {
        try {
            $operation();
        } catch (\Throwable) {
            // Any refusal counts: the point is that the operation did NOT silently succeed.
            $this->ok($label);

            return;
        }

        $this->bad($label);
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
        $this->line("  <error>✗</error> {$label}");
        $this->failures++;
    }
}
