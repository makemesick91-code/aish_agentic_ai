<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Enums\NotificationChannel;
use App\Models\NotificationDelivery;

/**
 * A delivery channel. Adapters translate a persisted delivery into an actual send and
 * report a truthful result; they never mark a delivery `sent` themselves — the job owns
 * the state transition (rule 31).
 */
interface ChannelAdapter
{
    public function channel(): NotificationChannel;

    public function send(NotificationDelivery $delivery): ChannelResult;
}
