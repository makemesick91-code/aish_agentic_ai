<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Delivery channels supported by the notification foundation. WhatsApp, SMS, Slack, Teams,
 * mobile push, and webhook channels are intentionally out of scope for this sprint
 * (rule 31; SPRINT-SF-05 §8.1).
 */
enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::InApp => 'In-app',
            self::Email => 'Email',
        };
    }
}
