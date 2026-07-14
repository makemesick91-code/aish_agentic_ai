<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Audit\AuditRecorder;
use App\Enums\NotificationChannel;
use App\Enums\NotificationState;
use App\Jobs\Notifications\DeliverNotificationJob;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\Exceptions\CrossTenantRecipientException;
use App\Tenancy\TenantScope;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

/**
 * The single entry point for emitting a notification. It enforces tenant-safe recipient
 * resolution, evaluates preferences/quiet-hours, records a delivery row per channel with a
 * globally-unique dedup key (so a duplicate dispatch or a retry yields exactly one logical
 * delivery), and enqueues delivery. Suppressed deliveries are still recorded truthfully.
 * No notification is sent directly from a controller — everything routes through here
 * (rule 03, rule 31).
 */
final class NotificationDispatcher
{
    public function __construct(
        private readonly PreferenceResolver $resolver,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  array<string, scalar|null>  $data
     * @return list<NotificationDelivery>
     */
    public function dispatch(
        NotificationType $type,
        User $recipient,
        string $idempotencyKey,
        string $subject,
        ?Tenant $tenant = null,
        ?string $body = null,
        array $data = [],
        ?int $branchId = null,
        ?int $actorId = null,
    ): array {
        if ($tenant !== null) {
            $this->assertRecipientBelongsToTenant($tenant, $recipient);
        }

        $preference = $tenant === null ? null : $this->preferenceFor($tenant, $recipient);
        $now = Carbon::now();

        $deliveries = [];

        foreach ($type->defaultChannels() as $channel) {
            $decision = $this->resolver->evaluate($type, $channel, $preference, $now);
            $dedupKey = $idempotencyKey.':'.$channel->value;

            [$delivery, $created] = $this->createOrGet(
                type: $type,
                channel: $channel,
                recipient: $recipient,
                tenant: $tenant,
                branchId: $branchId,
                idempotencyKey: $idempotencyKey,
                dedupKey: $dedupKey,
                subject: $subject,
                body: $body,
                data: $data,
            );

            $deliveries[] = $delivery;

            if (! $created) {
                // Already dispatched for this logical event + channel: no duplicate.
                continue;
            }

            if (! $decision['deliver']) {
                $delivery->forceFill([
                    'state' => NotificationState::Suppressed,
                    'suppressed_reason' => $decision['reason'],
                ])->save();

                $this->audit->record('notification.suppressed', [
                    'tenant_id' => $tenant?->id,
                    'actor_id' => $actorId,
                    'subject' => $delivery,
                    'metadata' => ['type' => $type->value, 'channel' => $channel->value, 'reason' => $decision['reason']],
                ]);

                continue;
            }

            $delivery->forceFill([
                'state' => NotificationState::Queued,
                'queued_at' => $now,
            ])->save();

            $this->audit->record('notification.dispatched', [
                'tenant_id' => $tenant?->id,
                'actor_id' => $actorId,
                'subject' => $delivery,
                'metadata' => ['type' => $type->value, 'channel' => $channel->value],
            ]);

            DeliverNotificationJob::dispatch($delivery->id);
        }

        return $deliveries;
    }

    private function assertRecipientBelongsToTenant(Tenant $tenant, User $recipient): void
    {
        $isMember = TenantMembership::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $recipient->id)
            ->exists();

        if (! $isMember) {
            throw CrossTenantRecipientException::make($tenant->id, (int) $recipient->id);
        }
    }

    private function preferenceFor(Tenant $tenant, User $recipient): ?NotificationPreference
    {
        return NotificationPreference::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $recipient->id)
            ->first();
    }

    /**
     * Create the delivery, or return the existing one for this dedup key (race-safe via the
     * unique constraint). The boolean indicates whether this call created the row.
     *
     * @param  array<string, scalar|null>  $data
     * @return array{0: NotificationDelivery, 1: bool}
     */
    private function createOrGet(
        NotificationType $type,
        NotificationChannel $channel,
        User $recipient,
        ?Tenant $tenant,
        ?int $branchId,
        string $idempotencyKey,
        string $dedupKey,
        string $subject,
        ?string $body,
        array $data,
    ): array {
        try {
            $delivery = NotificationDelivery::create([
                'tenant_id' => $tenant?->id,
                'branch_id' => $branchId,
                'recipient_id' => $recipient->id,
                'type' => $type->value,
                'category' => $type->category()->value,
                'channel' => $channel->value,
                'state' => NotificationState::Pending->value,
                'critical' => $type->isCritical(),
                'idempotency_key' => $idempotencyKey,
                'dedup_key' => $dedupKey,
                'subject' => $subject,
                'body' => $body,
                'data' => $data === [] ? null : $data,
                'attempts' => 0,
                'max_attempts' => 3,
            ]);

            return [$delivery, true];
        } catch (UniqueConstraintViolationException) {
            $existing = NotificationDelivery::query()->where('dedup_key', $dedupKey)->firstOrFail();

            return [$existing, false];
        }
    }
}
