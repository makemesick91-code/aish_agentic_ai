<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Enums\NotificationChannel;
use App\Models\NotificationDelivery;

/**
 * In-app channel. The persisted delivery row IS the in-app notification (surfaced in the
 * inbox), so there is nothing external to hand off — acceptance is immediate.
 */
final class InAppChannel implements ChannelAdapter
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::InApp;
    }

    public function send(NotificationDelivery $delivery): ChannelResult
    {
        return ChannelResult::accepted();
    }
}
