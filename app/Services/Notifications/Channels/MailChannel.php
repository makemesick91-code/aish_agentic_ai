<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Enums\NotificationChannel;
use App\Mail\FoundationNotificationMail;
use App\Models\NotificationDelivery;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Email channel. Hands the message to the configured mail transport. Acceptance means the
 * transport accepted the message for delivery — provider delivery receipts are out of scope,
 * so this is recorded as `sent` = accepted_by_transport, never a proven end-user receipt
 * (rule 31 §8.5, §8.10).
 */
final class MailChannel implements ChannelAdapter
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Email;
    }

    public function send(NotificationDelivery $delivery): ChannelResult
    {
        $recipient = $delivery->recipient;

        if ($recipient === null || $recipient->email === '') {
            // A missing address is permanent: retrying will not help.
            return ChannelResult::failed('missing_recipient_email', permanent: true);
        }

        try {
            Mail::to($recipient->email)->send(
                new FoundationNotificationMail($delivery->subject, $delivery->body ?? ''),
            );

            return ChannelResult::accepted();
        } catch (Throwable) {
            // No secret/PII from the exception is surfaced; only a coarse, safe code.
            return ChannelResult::failed('transport_error');
        }
    }
}
