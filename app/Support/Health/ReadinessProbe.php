<?php

declare(strict_types=1);

namespace App\Support\Health;

/**
 * Aggregates the configured readiness checks into a single verdict.
 *
 * The probe is truthful (rule 10, Master Source §53): readiness is reported
 * only when every mandatory dependency check passes. A single failing check
 * makes the whole probe not-ready.
 */
final class ReadinessProbe
{
    /**
     * @param  iterable<HealthCheck>  $checks
     */
    public function __construct(private readonly iterable $checks) {}

    /**
     * @return array{0: bool, 1: array<int, HealthResult>}
     */
    public function evaluate(): array
    {
        $ready = true;
        $results = [];

        foreach ($this->checks as $check) {
            $result = $check->run();
            $results[] = $result;

            if (! $result->ok) {
                $ready = false;
            }
        }

        return [$ready, $results];
    }
}
