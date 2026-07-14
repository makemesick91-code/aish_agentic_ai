<?php

declare(strict_types=1);

namespace App\Support\Runtime;

/**
 * Deterministic preflight: verifies mandatory runtime configuration is present
 * and safe before the application is considered bootable. Returns a list of
 * human-readable problems (empty list == pass). Reveals no configuration values.
 */
final class RuntimePreflight
{
    /**
     * @return array<int, string> problems (empty = ready)
     */
    public function problems(): array
    {
        $problems = [];

        if (empty(config('app.key'))) {
            $problems[] = 'APP_KEY is not set (run: php artisan key:generate)';
        }

        foreach (['app.name', 'app.env'] as $key) {
            if (config($key) === null || config($key) === '') {
                $problems[] = "missing required config: {$key}";
            }
        }

        $connection = config('database.default');
        if (empty($connection)) {
            $problems[] = 'DB_CONNECTION is not set';
        } elseif (config("database.connections.{$connection}") === null) {
            $problems[] = "database connection '{$connection}' is not configured";
        }

        if (config('app.env') === 'production' && config('app.debug') === true) {
            $problems[] = 'APP_DEBUG MUST be false in production';
        }

        return $problems;
    }

    public function passes(): bool
    {
        return $this->problems() === [];
    }
}
