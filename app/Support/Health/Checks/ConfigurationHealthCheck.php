<?php

declare(strict_types=1);

namespace App\Support\Health\Checks;

use App\Support\Health\HealthCheck;
use App\Support\Health\HealthResult;

/**
 * Verifies mandatory runtime configuration is present. This does NOT reveal any
 * configuration value — only whether required keys are set (rule 04, rule 24).
 */
final class ConfigurationHealthCheck implements HealthCheck
{
    public function name(): string
    {
        return 'configuration';
    }

    public function run(): HealthResult
    {
        $missing = [];

        if (empty(config('app.key'))) {
            $missing[] = 'app.key';
        }

        $connection = config('database.default');
        if (empty($connection) || config("database.connections.$connection") === null) {
            $missing[] = 'database.connection';
        }

        if (config('app.env') === 'production' && config('app.debug') === true) {
            // Debug must never be on in production (rule 04, rule 10).
            return new HealthResult($this->name(), false, 'misconfigured', 'debug must be disabled in production');
        }

        if ($missing !== []) {
            return new HealthResult($this->name(), false, 'incomplete', 'missing required configuration keys');
        }

        return new HealthResult($this->name(), true, 'ok');
    }
}
