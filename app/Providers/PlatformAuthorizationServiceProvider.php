<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Platform\PlatformAccess;
use App\Platform\PlatformPermissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers a Gate ability per platform permission, resolved solely from PlatformAccess. There
 * is intentionally NO Gate::before — a platform role never becomes a universal bypass, and it
 * never grants tenant-data access (rule 31 §10.1).
 */
class PlatformAuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (PlatformPermissions::all() as $permission) {
            Gate::define(
                $permission,
                fn (User $user): bool => app(PlatformAccess::class)->has($user, $permission),
            );
        }
    }
}
