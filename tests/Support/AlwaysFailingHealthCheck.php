<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Support\Health\HealthCheck;
use App\Support\Health\HealthResult;

/**
 * Test double: a readiness check that always reports unhealthy, used to exercise
 * the HTTP 503 not-ready path of GET /ready.
 */
final class AlwaysFailingHealthCheck implements HealthCheck
{
    public function name(): string
    {
        return 'always-failing';
    }

    public function run(): HealthResult
    {
        return new HealthResult($this->name(), false, 'unavailable', 'forced failure for test');
    }
}
