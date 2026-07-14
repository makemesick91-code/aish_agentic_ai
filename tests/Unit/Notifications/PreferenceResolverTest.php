<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Enums\NotificationChannel;
use App\Models\NotificationPreference;
use App\Notifications\NotificationType;
use App\Services\Notifications\PreferenceResolver;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Preference evaluation: critical bypass, category overrides, and timezone-aware quiet hours
 * (rule 31 §8.8).
 */
final class PreferenceResolverTest extends TestCase
{
    private PreferenceResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new PreferenceResolver;
    }

    public function test_null_preference_defaults_to_deliver(): void
    {
        $decision = $this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::Email, null);

        $this->assertTrue($decision['deliver']);
    }

    public function test_critical_notifications_always_deliver(): void
    {
        $preference = new NotificationPreference([
            'in_app_enabled' => false,
            'email_enabled' => false,
            'timezone' => 'Asia/Makassar',
        ]);

        $decision = $this->resolver->evaluate(NotificationType::SecurityAuthenticationAlert, NotificationChannel::Email, $preference);

        $this->assertTrue($decision['deliver']);
    }

    public function test_a_category_override_suppresses_a_channel(): void
    {
        $preference = new NotificationPreference([
            'in_app_enabled' => true,
            'email_enabled' => true,
            'timezone' => 'Asia/Makassar',
            'category_overrides' => ['membership' => ['email' => false]],
        ]);

        $decision = $this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::Email, $preference);

        $this->assertFalse($decision['deliver']);
        $this->assertSame('preference', $decision['reason']);
    }

    public function test_quiet_hours_suppress_email_but_not_in_app_and_are_timezone_aware(): void
    {
        $preference = new NotificationPreference([
            'in_app_enabled' => true,
            'email_enabled' => true,
            'timezone' => 'Asia/Makassar', // UTC+8
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ]);

        // 15:00 UTC -> 23:00 WITA, inside the 22:00–07:00 window.
        $insideWindow = Carbon::create(2026, 7, 14, 15, 0, 0, 'UTC');
        // 06:00 UTC -> 14:00 WITA, outside the window.
        $outsideWindow = Carbon::create(2026, 7, 14, 6, 0, 0, 'UTC');

        $this->assertFalse($this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::Email, $preference, $insideWindow)['deliver']);
        $this->assertSame('quiet_hours', $this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::Email, $preference, $insideWindow)['reason']);
        $this->assertTrue($this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::Email, $preference, $outsideWindow)['deliver']);

        // In-app is passive: quiet hours never suppress it.
        $this->assertTrue($this->resolver->evaluate(NotificationType::MembershipActivated, NotificationChannel::InApp, $preference, $insideWindow)['deliver']);
    }
}
