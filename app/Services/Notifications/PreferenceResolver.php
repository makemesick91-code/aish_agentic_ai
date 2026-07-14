<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Models\NotificationPreference;
use App\Notifications\NotificationType;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Decides whether a given (type, channel) should be delivered to a recipient given their
 * preferences and the current time. Critical notifications always deliver — a preference
 * MUST NOT silence them (rule 31 §8.8). Quiet hours suppress email only; the in-app inbox is
 * passive and always receives.
 */
final class PreferenceResolver
{
    /**
     * @return array{deliver: bool, reason: ?string}
     */
    public function evaluate(
        NotificationType $type,
        NotificationChannel $channel,
        ?NotificationPreference $preference,
        ?CarbonInterface $now = null,
    ): array {
        if ($type->isCritical()) {
            return ['deliver' => true, 'reason' => null];
        }

        if ($preference === null) {
            // Default-on: absence of a preference row means the tenant default applies.
            return ['deliver' => true, 'reason' => null];
        }

        // Base channel toggle.
        if ($channel === NotificationChannel::InApp && ! $preference->in_app_enabled) {
            return ['deliver' => false, 'reason' => 'preference'];
        }

        if ($channel === NotificationChannel::Email && ! $preference->email_enabled) {
            return ['deliver' => false, 'reason' => 'preference'];
        }

        // Category-level override (validated shape: category => [channel => bool]).
        $overrides = $preference->category_overrides[$type->category()->value] ?? null;
        if (is_array($overrides)
            && array_key_exists($channel->value, $overrides)
            && $overrides[$channel->value] === false
        ) {
            return ['deliver' => false, 'reason' => 'preference'];
        }

        // Quiet hours suppress email only.
        if ($channel === NotificationChannel::Email && $this->inQuietHours($preference, $now ?? Carbon::now())) {
            return ['deliver' => false, 'reason' => 'quiet_hours'];
        }

        return ['deliver' => true, 'reason' => null];
    }

    private function inQuietHours(NotificationPreference $preference, CarbonInterface $now): bool
    {
        $start = $preference->quiet_hours_start;
        $end = $preference->quiet_hours_end;

        if ($start === null || $end === null) {
            return false;
        }

        // Evaluate against the preference's own timezone (rule 31 §8.8 timezone-aware).
        $local = Carbon::instance($now->toDateTimeImmutable())->setTimezone($preference->timezone);
        $current = $local->format('H:i');

        if ($start === $end) {
            return false;
        }

        if ($start < $end) {
            return $current >= $start && $current < $end;
        }

        // Window wraps midnight (e.g. 22:00 -> 07:00).
        return $current >= $start || $current < $end;
    }
}
