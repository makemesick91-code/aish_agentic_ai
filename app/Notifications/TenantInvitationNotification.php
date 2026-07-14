<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivers a tenant invitation. The one-time plaintext token travels ONLY inside the
 * signed acceptance URL of this message — it is never stored in the database or written
 * to application logs (rule 04, rule 30).
 */
final class TenantInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly TenantInvitation $invitation,
        private readonly string $plainToken,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invitations.accept.show', ['token' => $this->plainToken]);

        return (new MailMessage)
            ->subject(__('You have been invited to :tenant on Aish Agentic AI', ['tenant' => $this->tenant->name]))
            ->line(__('You have been invited to join the :tenant workspace.', ['tenant' => $this->tenant->name]))
            ->action(__('Accept invitation'), $url)
            ->line(__('This invitation expires on :date.', ['date' => $this->invitation->expires_at->toDayDateTimeString()]))
            ->line(__('If you were not expecting this invitation, you can ignore this email.'));
    }
}
