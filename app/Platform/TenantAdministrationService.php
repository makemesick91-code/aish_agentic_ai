<?php

declare(strict_types=1);

namespace App\Platform;

use App\Audit\AuditRecorder;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Tenancy\TenantOwnerLocator;
use InvalidArgumentException;

/**
 * Platform-side tenant operational status: suspend, reactivate, mark deletion-pending. Every
 * action requires a reason (except reactivation), is audited, and notifies the tenant's owners.
 * There is no hard delete and no silent status change. This is a security/operational state and
 * takes precedence over any commercial (subscription) state (rule 31 §10.8, §9.5).
 */
final class TenantAdministrationService
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly NotificationDispatcher $dispatcher,
        private readonly TenantOwnerLocator $owners,
    ) {}

    public function suspend(Tenant $tenant, string $reason, ?User $actor = null): void
    {
        $this->requireReason($reason);

        $tenant->forceFill(['status' => TenantStatus::Suspended, 'suspended_at' => now()])->save();

        $this->audit->record('platform.tenant.suspended', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actor?->id,
            'subject' => $tenant,
            'metadata' => ['reason' => $reason],
        ]);

        $this->notifyOwners(
            $tenant,
            NotificationType::TenantSuspended,
            __('Workspace suspended'),
            __('Your workspace has been suspended by the platform operator.'),
            "tenant.suspended.{$tenant->id}",
            $actor?->id,
        );
    }

    public function reactivate(Tenant $tenant, ?User $actor = null): void
    {
        $tenant->forceFill(['status' => TenantStatus::Active, 'suspended_at' => null])->save();

        $this->audit->record('platform.tenant.reactivated', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actor?->id,
            'subject' => $tenant,
        ]);

        $this->notifyOwners(
            $tenant,
            NotificationType::TenantReactivated,
            __('Workspace reactivated'),
            __('Your workspace has been reactivated.'),
            "tenant.reactivated.{$tenant->id}",
            $actor?->id,
        );
    }

    public function markDeletionPending(Tenant $tenant, string $reason, ?User $actor = null): void
    {
        $this->requireReason($reason);

        $tenant->forceFill(['status' => TenantStatus::DeletionPending, 'deletion_requested_at' => now()])->save();

        $this->audit->record('platform.tenant.deletion_pending', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actor?->id,
            'subject' => $tenant,
            'metadata' => ['reason' => $reason],
        ]);

        $this->notifyOwners(
            $tenant,
            NotificationType::TenantSuspended,
            __('Workspace marked for deletion'),
            __('Your workspace has been marked for deletion by the platform operator.'),
            "tenant.deletion-pending.{$tenant->id}",
            $actor?->id,
        );
    }

    private function requireReason(string $reason): void
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A reason is required for this tenant status change.');
        }
    }

    private function notifyOwners(
        Tenant $tenant,
        NotificationType $type,
        string $subject,
        string $body,
        string $idempotencyKey,
        ?int $actorId,
    ): void {
        foreach ($this->owners->activeOwners($tenant) as $owner) {
            $this->dispatcher->dispatch(
                type: $type,
                recipient: $owner,
                idempotencyKey: $idempotencyKey.':'.$owner->id,
                subject: $subject,
                tenant: $tenant,
                body: $body,
                actorId: $actorId,
            );
        }
    }
}
