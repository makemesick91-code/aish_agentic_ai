<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Enums\NotificationChannel;

/**
 * Resolves a channel adapter for a delivery. The allowlist is closed: only in-app and email
 * exist in this sprint; other channels are out of scope (rule 31 §8.1).
 */
final class ChannelManager
{
    public function __construct(
        private readonly InAppChannel $inApp,
        private readonly MailChannel $mail,
    ) {}

    public function for(NotificationChannel $channel): ChannelAdapter
    {
        return match ($channel) {
            NotificationChannel::InApp => $this->inApp,
            NotificationChannel::Email => $this->mail,
        };
    }
}
