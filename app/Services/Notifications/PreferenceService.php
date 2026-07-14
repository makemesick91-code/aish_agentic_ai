<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Audit\AuditRecorder;
use App\Models\NotificationPreference;
use App\Models\Tenant;
use App\Models\User;

/**
 * Reads and updates a tenant user's notification preferences. Every change is audited and
 * scoped to the acting tenant; critical notifications are unaffected by these settings
 * (rule 31 §8.8).
 */
final class PreferenceService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function forUser(Tenant $tenant, User $user): NotificationPreference
    {
        $preference = NotificationPreference::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if ($preference !== null) {
            return $preference;
        }

        $new = new NotificationPreference([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);
        $new->exists = false;

        return $new;
    }

    /**
     * @param  array{
     *     in_app_enabled: bool,
     *     email_enabled: bool,
     *     quiet_hours_start: ?string,
     *     quiet_hours_end: ?string,
     *     timezone: string,
     *     category_overrides: array<string, array<string, bool>>
     * }  $data
     */
    public function update(Tenant $tenant, User $user, array $data, ?int $actorId = null): NotificationPreference
    {
        $preference = NotificationPreference::query()
            ->firstOrNew([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ]);

        $preference->fill([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'in_app_enabled' => $data['in_app_enabled'],
            'email_enabled' => $data['email_enabled'],
            'quiet_hours_start' => $data['quiet_hours_start'],
            'quiet_hours_end' => $data['quiet_hours_end'],
            'timezone' => $data['timezone'],
            'category_overrides' => $data['category_overrides'] === [] ? null : $data['category_overrides'],
        ])->save();

        $this->audit->record('notification.preference.changed', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actorId,
            'subject' => $preference,
            'metadata' => [
                'in_app_enabled' => $data['in_app_enabled'],
                'email_enabled' => $data['email_enabled'],
                'has_quiet_hours' => $data['quiet_hours_start'] !== null && $data['quiet_hours_end'] !== null,
            ],
        ]);

        return $preference;
    }
}
