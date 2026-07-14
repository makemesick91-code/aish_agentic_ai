<?php

declare(strict_types=1);

namespace App\Support\Health\Checks;

use App\Support\Health\HealthCheck;
use App\Support\Health\HealthResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Round-trips the active cache store (Redis in staging/pilot/production, array
 * in the test suite). Proves the cache subsystem is reachable and writable.
 */
final class CacheHealthCheck implements HealthCheck
{
    private const KEY = 'aish:health:cache-probe';

    public function name(): string
    {
        return 'cache';
    }

    public function run(): HealthResult
    {
        try {
            Cache::put(self::KEY, 'ok', 5);
            $ok = Cache::get(self::KEY) === 'ok';

            return new HealthResult(
                $this->name(),
                $ok,
                $ok ? 'ok' : 'degraded',
                $ok ? null : 'cache round-trip mismatch',
            );
        } catch (Throwable $e) {
            Log::warning('readiness.cache.unavailable', ['exception' => $e->getMessage()]);

            return new HealthResult($this->name(), false, 'unavailable', 'cache connectivity failed');
        }
    }
}
