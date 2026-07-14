<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use Illuminate\Http\JsonResponse;

/**
 * Liveness probe: is the application process up and able to serve a request?
 *
 * MUST NOT depend on external dependencies (database, cache, queue). A live but
 * not-ready process still returns 200 here and is surfaced as not-ready by
 * ReadinessController (Step 5 acceptance; rule 10 truthful states).
 */
final class LivenessController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'service' => config('app.name'),
        ], 200);
    }
}
