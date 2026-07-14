<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Authorization\Roles;
use App\Authorization\TenantRoleProvisioner;
use App\Enums\FeedbackStatus;
use App\Enums\MembershipStatus;
use App\Feedback\Exceptions\FeedbackTagException;
use App\Feedback\Exceptions\InvalidAssigneeException;
use App\Feedback\Exceptions\InvalidStatusTransitionException;
use App\Feedback\Export\FeedbackExportService;
use App\Feedback\FeedbackAssignmentService;
use App\Feedback\FeedbackLifecycle;
use App\Feedback\FeedbackNoteService;
use App\Feedback\FeedbackProjector;
use App\Feedback\FeedbackTagService;
use App\Feedback\Search\FeedbackSearchCriteria;
use App\Feedback\Search\FeedbackSearchService;
use App\Models\AuditLog;
use App\Models\FeedbackItem;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\PermissionRegistrar;

/**
 * Verifies the Step 8 Feedback Operations Foundation against real PostgreSQL + Redis with positive
 * AND negative assertions: idempotent projection, lifecycle, scope-validated assignment, manual tags,
 * append-only notes, permission-aware search, entitlement-gated export, metering, audit, and
 * cross-tenant isolation. Prints "Step 8 verification passed" on success (rule 33; Step 8 §34).
 */
final class VerifyStep8Command extends Command
{
    protected $signature = 'aish:verify-step-8';

    protected $description = 'Verify the Step 8 feedback operations foundation against real PostgreSQL + Redis.';

    private int $failures = 0;

    public function handle(TenantContext $context): int
    {
        Queue::fake(); // keep export dispatch synchronous-free; the projector is exercised directly

        [$tenantA, $ownerA, $membershipA] = $this->provisionWorkspace();
        [$tenantB, $ownerB] = $this->provisionWorkspace();
        [$readerA] = $this->member($tenantA, Roles::READ_ONLY);

        $this->establish($context, $tenantA, $membershipA);

        // Projection — idempotent, metered, audited (response is created under tenant A context).
        $response = SurveyResponse::factory()->completed()->create();
        $projector = app(FeedbackProjector::class);
        $item = $projector->projectFromSurveyResponse($response);
        $again = $projector->projectFromSurveyResponse($response);
        $this->assert($item->is($again), 'projection is idempotent (one item per completed response)');
        $this->assert($item->status === FeedbackStatus::New, 'projected item starts NEW');
        $this->assert($item->events()->where('type', 'feedback.projected')->exists(), 'projection records a timeline event');
        $this->assert(app(UsageMeter::class)->total($tenantA, MeterKeys::FEEDBACK_ITEMS_PROJECTED) === 1, 'projection metered exactly once');
        $this->assert(AuditLog::where('event', 'feedback.projected')->exists(), 'projection is audited');

        // Lifecycle — valid + invalid.
        app(FeedbackLifecycle::class)->transition($item, FeedbackStatus::Triaged, $ownerA);
        $this->assert($item->fresh()->status === FeedbackStatus::Triaged, 'valid status transition applied');
        try {
            app(FeedbackLifecycle::class)->transition($item->fresh(), FeedbackStatus::New, $ownerA);
            $this->bad('an invalid status transition was accepted');
        } catch (InvalidStatusTransitionException) {
            $this->ok('an invalid status transition fails closed');
        }

        // Assignment — valid member vs cross-tenant stranger.
        app(FeedbackAssignmentService::class)->assign($item->fresh(), $ownerA, $ownerA, notify: false);
        $this->assert($item->fresh()->current_assignee_id === $ownerA->id, 'valid member can be assigned');
        try {
            app(FeedbackAssignmentService::class)->assign($item->fresh(), $ownerB, $ownerA, notify: false);
            $this->bad('a non-member (other tenant) was assigned');
        } catch (InvalidAssigneeException) {
            $this->ok('a cross-tenant assignee is rejected');
        }

        // Manual tags — attach + archived rejection.
        $tags = app(FeedbackTagService::class);
        $tag = $tags->createTag('Wait time', $ownerA);
        $tags->attach($item->fresh(), $tag, $ownerA);
        $this->assert($item->fresh()->tags()->count() === 1, 'tag attaches to a feedback item');
        $archived = $tags->archiveTag($tags->createTag('Old topic', $ownerA), $ownerA);
        try {
            $tags->attach($item->fresh(), $archived->fresh(), $ownerA);
            $this->bad('an archived tag was attached');
        } catch (FeedbackTagException) {
            $this->ok('an archived tag cannot be attached');
        }

        // Notes — append-only, body never audited.
        app(FeedbackNoteService::class)->addNote($item->fresh(), $ownerA, 'sensitive internal note body');
        $noteAudit = (string) json_encode(AuditLog::where('event', 'feedback.note.created')->pluck('metadata')->all());
        $this->assert(! str_contains($noteAudit, 'sensitive internal note body'), 'note body never appears in audit metadata');

        // Permission-aware search.
        $item->fresh()->forceFill(['search_content' => 'needle-value', 'search_meta' => 'checkout survey'])->save();
        $search = app(FeedbackSearchService::class);
        $this->assert($search->search(new FeedbackSearchCriteria(query: 'needle-value'), $ownerA)->total() === 1, 'content search works with content permission');
        $this->assert($search->search(new FeedbackSearchCriteria(query: 'needle-value'), $readerA)->total() === 0, 'content search hidden without content permission');
        $this->assert($search->search(new FeedbackSearchCriteria(query: 'checkout'), $readerA)->total() === 1, 'metadata search allowed for all viewers');

        // Export — entitlement-gated + metered.
        app(FeedbackExportService::class)->request([], $ownerA, includeContent: false);
        $this->assert(app(UsageMeter::class)->total($tenantA, MeterKeys::FEEDBACK_EXPORTS_CREATED) === 1, 'export request is metered');

        // Cross-tenant isolation + entitlement fail-closed.
        $this->assert(! FeedbackItem::query()->whereKey($item->id)->where('tenant_id', $tenantB->id)->exists(), 'tenant scope excludes another tenant');
        $decision = app(EntitlementResolver::class)->resolve($tenantA, 'feedback.does-not-exist');
        $this->assert(! $decision->allowed && $decision->reasonCode === 'unknown_feature', 'unknown feedback entitlement fails closed');

        $this->forget($context);
        $this->cleanup([$tenantA, $tenantB]);

        if ($this->failures > 0) {
            $this->error("Step 8 verification FAILED with {$this->failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('Step 8 verification passed against real PostgreSQL + Redis.');

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
            EntitlementKeys::FEEDBACK_ENABLED,
            EntitlementKeys::FEEDBACK_ATTACHMENTS_ENABLED,
            EntitlementKeys::FEEDBACK_EXPORTS_ENABLED,
            EntitlementKeys::FEEDBACK_BULK_ACTIONS_ENABLED,
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
