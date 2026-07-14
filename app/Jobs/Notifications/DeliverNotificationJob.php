<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Audit\AuditRecorder;
use App\Enums\NotificationState;
use App\Models\NotificationDelivery;
use App\Services\Notifications\Channels\ChannelManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Delivers a single notification delivery through its channel and records a truthful state.
 *
 * This job intentionally does NOT use the tenant-context envelope: a delivery must still be
 * sent when its tenant has just been suspended (e.g. the `tenant.suspended` notice), and the
 * strict envelope refuses suspended tenants. The delivery row is self-contained — it carries
 * its own tenant_id/recipient and the job performs no tenant-scoped query — so there is no
 * cross-tenant leakage risk. Retries are bounded and never duplicate a logical delivery: a
 * delivery already in a terminal state is a no-op on re-run (rule 03, rule 31 §8.5/§8.11).
 */
final class DeliverNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Laravel-level auto-retry is disabled; retry is managed explicitly and bounded. */
    public int $tries = 1;

    public function __construct(public readonly int $deliveryId) {}

    public function handle(ChannelManager $channels, AuditRecorder $audit): void
    {
        $delivery = NotificationDelivery::find($this->deliveryId);

        if ($delivery === null || $delivery->state->isTerminal()) {
            // Nothing to do — already resolved (idempotent on retry) or removed.
            return;
        }

        if (! in_array($delivery->state, [NotificationState::Queued, NotificationState::Sending], true)) {
            return;
        }

        $delivery->forceFill([
            'state' => NotificationState::Sending,
            'attempts' => $delivery->attempts + 1,
        ])->save();

        Log::withContext(['tenant_id' => $delivery->tenant_id]);

        $result = $channels->for($delivery->channel)->send($delivery);

        if ($result->accepted) {
            $delivery->forceFill([
                'state' => NotificationState::Sent,
                'sent_at' => now(),
            ])->save();

            $audit->record('notification.sent', [
                'tenant_id' => $delivery->tenant_id,
                'actor_id' => null,
                'subject' => $delivery,
                'metadata' => ['type' => $delivery->type, 'channel' => $delivery->channel->value],
            ]);

            return;
        }

        $canRetry = ! $result->permanent && $delivery->attempts < $delivery->max_attempts;

        if ($canRetry) {
            $delivery->forceFill(['state' => NotificationState::Queued])->save();

            self::dispatch($delivery->id)->delay(now()->addSeconds($this->backoffSeconds($delivery->attempts)));

            return;
        }

        $delivery->forceFill([
            'state' => NotificationState::Failed,
            'failed_at' => now(),
            'failure_code' => $result->failureCode,
        ])->save();

        $audit->record('notification.failed', [
            'tenant_id' => $delivery->tenant_id,
            'actor_id' => null,
            'subject' => $delivery,
            'metadata' => [
                'type' => $delivery->type,
                'channel' => $delivery->channel->value,
                'failure_code' => $result->failureCode,
            ],
        ]);
    }

    private function backoffSeconds(int $attempt): int
    {
        // 10s, 20s, 40s … capped at 5 minutes.
        return (int) min(300, 10 * (2 ** max(0, $attempt - 1)));
    }
}
