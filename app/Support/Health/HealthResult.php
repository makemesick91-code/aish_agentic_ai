<?php

declare(strict_types=1);

namespace App\Support\Health;

/**
 * Immutable result of a single health check.
 *
 * `detail` is intentionally coarse and MUST NOT carry secrets, credentials,
 * stack traces, queries, connection strings, or internal paths (rule 04, rule 10;
 * AFR-131 truthful-health-response). Diagnostic detail is logged server-side only.
 */
final class HealthResult
{
    public function __construct(
        public readonly string $name,
        public readonly bool $ok,
        public readonly string $status,
        public readonly ?string $detail = null,
    ) {}

    /**
     * @return array{name: string, ok: bool, status: string, detail?: string}
     */
    public function toArray(): array
    {
        $out = [
            'name' => $this->name,
            'ok' => $this->ok,
            'status' => $this->status,
        ];

        if ($this->detail !== null) {
            $out['detail'] = $this->detail;
        }

        return $out;
    }
}
