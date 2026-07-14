<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Http\Controllers\Controller;
use App\Services\Notifications\PreferenceService;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Self-service notification preferences for the acting user within the current tenant.
 * A user manages only their own preferences; the values never cross tenants (rule 31 §8.8).
 */
final class NotificationPreferenceController extends Controller
{
    use AuthorizesRequests;

    public function edit(Request $request, TenantContext $context, PreferenceService $preferences): View
    {
        $preference = $preferences->forUser($context->tenant(), $request->user());
        $categories = NotificationCategory::tunable();
        $channels = NotificationChannel::cases();

        return view('notifications.preferences', compact('preference', 'categories', 'channels'));
    }

    public function update(Request $request, TenantContext $context, PreferenceService $preferences): RedirectResponse
    {
        $validated = $request->validate([
            'quiet_hours_start' => ['nullable', 'date_format:H:i', 'required_with:quiet_hours_end'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i', 'required_with:quiet_hours_start'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        $overrides = [];
        foreach (NotificationCategory::tunable() as $category) {
            foreach (NotificationChannel::cases() as $channel) {
                $enabled = $request->boolean("categories.{$category->value}.{$channel->value}");
                if (! $enabled) {
                    $overrides[$category->value][$channel->value] = false;
                }
            }
        }

        $preferences->update(
            $context->tenant(),
            $request->user(),
            [
                'in_app_enabled' => $request->boolean('in_app_enabled'),
                'email_enabled' => $request->boolean('email_enabled'),
                'quiet_hours_start' => $validated['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $validated['quiet_hours_end'] ?? null,
                'timezone' => $validated['timezone'],
                'category_overrides' => $overrides,
            ],
            (int) $request->user()->id,
        );

        return redirect()->route('notifications.preferences.edit')->with('status', __('Notification preferences saved.'));
    }
}
