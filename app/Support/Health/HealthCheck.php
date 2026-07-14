<?php

declare(strict_types=1);

namespace App\Support\Health;

/**
 * A single readiness/liveness dependency probe.
 *
 * Implementations MUST be side-effect-light, fail closed (return an unhealthy
 * result instead of throwing), and never leak sensitive detail into the result.
 */
interface HealthCheck
{
    public function name(): string;

    public function run(): HealthResult;
}
