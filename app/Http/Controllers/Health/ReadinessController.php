<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Support\Health\HealthCheck;
use App\Support\Health\ReadinessProbe;
use Illuminate\Http\JsonResponse;

/**
 * Readiness probe: are all mandatory dependencies ready to serve traffic?
 *
 * Returns HTTP 200 only when every configured check passes; otherwise HTTP 503
 * with a truthful, non-sensitive per-check breakdown. The check list comes from
 * config('health.readiness') so it is environment- and test-overridable.
 */
final class ReadinessController
{
    public function __invoke(): JsonResponse
    {
        /** @var array<int, class-string<HealthCheck>> $checkClasses */
        $checkClasses = config('health.readiness', []);

        $checks = array_map(static fn (string $class): HealthCheck => app($class), $checkClasses);

        [$ready, $results] = (new ReadinessProbe($checks))->evaluate();

        return response()->json([
            'status' => $ready ? 'ready' : 'unavailable',
            'checks' => array_map(static fn ($r) => $r->toArray(), $results),
        ], $ready ? 200 : 503);
    }
}
