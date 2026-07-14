<?php

declare(strict_types=1);

namespace App\Support\Health\Checks;

use App\Support\Health\HealthCheck;
use App\Support\Health\HealthResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifies the default database connection can execute a trivial query.
 * Proves real connectivity (not just an open socket) — Step 5 acceptance.
 */
final class DatabaseHealthCheck implements HealthCheck
{
    public function name(): string
    {
        return 'database';
    }

    public function run(): HealthResult
    {
        try {
            DB::connection()->select('select 1');

            return new HealthResult($this->name(), true, 'ok');
        } catch (Throwable $e) {
            // Log server-side (may contain driver detail); never return it to the client.
            Log::warning('readiness.database.unavailable', ['exception' => $e->getMessage()]);

            return new HealthResult($this->name(), false, 'unavailable', 'database connectivity failed');
        }
    }
}
