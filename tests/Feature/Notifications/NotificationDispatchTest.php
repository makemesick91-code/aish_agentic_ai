<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Audit\AuditRecorder;
use App\Enums\NotificationChannel;
use App\Enums\NotificationState;
use App\Jobs\Notifications\DeliverNotificationJob;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\Channels\ChannelManager;
use App\Services\Notifications\Exceptions\CrossTenantRecipientException;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The notification dispatcher: tenant-safe recipient resolution, per-channel delivery with a
 * unique dedup key, truthful states, preference/critical handling, and bounded retry
 * (rule 31 §8).
 */
final class NotificationDispatchTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    private function dispatcher(): NotificationDispatcher
    {
        return app(NotificationDispatcher::class);
    }

    public function test_it_delivers_in_app_and_email_and_records_truthful_sent_state(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);

        $deliveries = $this->dispatcher()->dispatch(
            type: NotificationType::MembershipActivated,
            recipient: $user,
            idempotencyKey: 'evt-1',
            subject: 'Membership activated',
            tenant: $tenant,
            body: 'Your membership is active.',
        );

        $this->assertCount(2, $deliveries);

        $inApp = NotificationDelivery::where('recipient_id', $user->id)->where('channel', NotificationChannel::InApp->value)->firstOrFail();
        $email = NotificationDelivery::where('recipient_id', $user->id)->where('channel', NotificationChannel::Email->value)->firstOrFail();

        $this->assertSame(NotificationState::Sent, $inApp->state);
        $this->assertSame(NotificationState::Sent, $email->state);
        $this->assertNotNull($inApp->sent_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'notification.dispatched']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'notification.sent']);
    }

    public function test_queued_is_not_sent(): void
    {
        Queue::fake();
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);

        $this->dispatcher()->dispatch(
            type: NotificationType::MembershipActivated,
            recipient: $user,
            idempotencyKey: 'evt-queued',
            subject: 'Queued',
            tenant: $tenant,
        );

        // With the queue faked the delivery job never runs: state must remain queued, not sent.
        $delivery = NotificationDelivery::where('channel', NotificationChannel::InApp->value)->firstOrFail();
        $this->assertSame(NotificationState::Queued, $delivery->state);
        $this->assertNull($delivery->sent_at);
        Queue::assertPushed(DeliverNotificationJob::class);
    }

    public function test_a_duplicate_dispatch_creates_exactly_one_logical_delivery_per_channel(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);

        $this->dispatcher()->dispatch(NotificationType::MembershipActivated, $user, 'same-key', 'A', tenant: $tenant);
        $this->dispatcher()->dispatch(NotificationType::MembershipActivated, $user, 'same-key', 'A', tenant: $tenant);

        $this->assertSame(1, NotificationDelivery::where('channel', NotificationChannel::InApp->value)->count());
        $this->assertSame(1, NotificationDelivery::where('channel', NotificationChannel::Email->value)->count());
    }

    public function test_a_tenant_cannot_notify_a_non_member(): void
    {
        $tenant = $this->provisionTenant();
        $stranger = User::factory()->create();

        $this->expectException(CrossTenantRecipientException::class);

        $this->dispatcher()->dispatch(NotificationType::MembershipActivated, $stranger, 'evt-x', 'Nope', tenant: $tenant);
    }

    public function test_a_disabled_channel_preference_suppresses_a_non_critical_notification(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);
        NotificationPreference::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'email_enabled' => false,
        ]);

        $this->dispatcher()->dispatch(NotificationType::MembershipActivated, $user, 'evt-pref', 'Pref', tenant: $tenant);

        $email = NotificationDelivery::where('channel', NotificationChannel::Email->value)->firstOrFail();
        $inApp = NotificationDelivery::where('channel', NotificationChannel::InApp->value)->firstOrFail();

        $this->assertSame(NotificationState::Suppressed, $email->state);
        $this->assertSame('preference', $email->suppressed_reason);
        $this->assertSame(NotificationState::Sent, $inApp->state);
        Mail::assertNothingSent();
        $this->assertDatabaseHas('audit_logs', ['event' => 'notification.suppressed']);
    }

    public function test_a_critical_notification_ignores_a_disabled_preference(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);
        NotificationPreference::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'in_app_enabled' => false,
            'email_enabled' => false,
        ]);

        // MembershipSuspended is critical → must deliver regardless of the preference.
        $this->dispatcher()->dispatch(NotificationType::MembershipSuspended, $user, 'evt-crit', 'Suspended', tenant: $tenant);

        $email = NotificationDelivery::where('channel', NotificationChannel::Email->value)->firstOrFail();
        $this->assertSame(NotificationState::Sent, $email->state);
        $this->assertTrue($email->critical);
    }

    public function test_a_permanent_failure_fails_without_retry(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);
        $delivery = NotificationDelivery::factory()->email()->create([
            'tenant_id' => $tenant->id,
            'recipient_id' => $user->id,
            'state' => NotificationState::Queued,
            'attempts' => 0,
        ]);

        // An unresolvable recipient address is a permanent failure (no retry helps).
        User::where('id', $user->id)->update(['email' => '']);

        (new DeliverNotificationJob($delivery->id))->handle(app(ChannelManager::class), app(AuditRecorder::class));

        $delivery->refresh();
        $this->assertSame(NotificationState::Failed, $delivery->state);
        $this->assertSame('missing_recipient_email', $delivery->failure_code);
        $this->assertSame(1, $delivery->attempts);
        $this->assertDatabaseHas('audit_logs', ['event' => 'notification.failed']);
    }

    public function test_a_transient_failure_retries_to_the_bound_and_then_fails(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);
        $delivery = NotificationDelivery::factory()->email()->create([
            'tenant_id' => $tenant->id,
            'recipient_id' => $user->id,
            'state' => NotificationState::Queued,
            'attempts' => 0,
            'max_attempts' => 3,
        ]);

        // Force the mail transport to fail transiently on every attempt.
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('transport down'));

        (new DeliverNotificationJob($delivery->id))->handle(app(ChannelManager::class), app(AuditRecorder::class));

        $delivery->refresh();
        $this->assertSame(NotificationState::Failed, $delivery->state);
        $this->assertSame('transport_error', $delivery->failure_code);
        $this->assertSame(3, $delivery->attempts, 'Retry must stop at max_attempts.');
    }

    public function test_reprocessing_a_terminal_delivery_is_a_no_op(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithoutRole($tenant);
        $delivery = NotificationDelivery::factory()->create([
            'tenant_id' => $tenant->id,
            'recipient_id' => $user->id,
            'state' => NotificationState::Sent,
            'attempts' => 1,
        ]);

        (new DeliverNotificationJob($delivery->id))->handle(app(ChannelManager::class), app(AuditRecorder::class));

        $delivery->refresh();
        $this->assertSame(1, $delivery->attempts, 'A terminal delivery must not be re-attempted (no duplicate side effect).');
    }
}
