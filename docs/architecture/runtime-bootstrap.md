# Runtime Bootstrap Architecture (Step 5)

Status: runtime foundation **IMPLEMENTED** and **RUNTIME VERIFIED** locally. Business/module implementation,
deployment, pilot, and production remain **NOT STARTED** — this document describes the runtime foundation only, not
a built product and not deployed infrastructure.

Canonical: Master Source v2.6.0 §70; rule [29](../../.claude/rules/29-runtime-bootstrap-and-operations.md); ADRs
[0047](../decisions/adr/0047-runtime-version-and-support-policy.md),
[0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md),
[0049](../decisions/adr/0049-health-and-readiness-contract.md),
[0050](../decisions/adr/0050-backend-runtime-ci-under-cicd-ctrl-1.md).

## Stack
Laravel 12 (PHP 8.4, min `^8.3`), PostgreSQL 17, Redis 7 (predis client), Node.js 22 + Vite 7 + Tailwind 4,
Composer 2, PHPUnit 11, Pint, PHPStan/Larastan. See [REPOSITORY_LAYOUT](REPOSITORY_LAYOUT.md) and
[APPLICATION_ARCHITECTURE_BASELINE](APPLICATION_ARCHITECTURE_BASELINE.md).

## Realized layout
- `bootstrap/app.php` — routing (web + health `then:` closure), middleware (security headers), exceptions.
- `app/Http/Controllers/Health/` — `LivenessController`, `ReadinessController`.
- `app/Http/Middleware/SecurityHeaders.php` — conservative security headers; CSP outside local.
- `app/Support/Health/` — `HealthCheck` contract, `HealthResult`, `ReadinessProbe`, and checks (database, cache,
  configuration).
- `app/Support/Runtime/` — `RuntimePreflight`, `Jobs/RuntimeSmokeJob`.
- `app/Console/Commands/` — `aish:preflight`, `aish:heartbeat`, `aish:queue-smoke`.
- `config/health.php`, `config/security.php`.
- `app/Modules/`, `app/Shared/` — reserved; **NOT STARTED**.

## Health contract
`/live` and `/ready` are registered outside the web middleware group (no session/cookies/CSRF). `/ready`
aggregates the `config('health.readiness')` checks and returns 503 if any mandatory dependency is down, with no
sensitive detail. See [operations/runtime-verification](../operations/runtime-verification.md) and ADR 0049.

## Boundaries
The runtime foundation adds no business module. Module boundaries and tenant isolation
([MODULE_BOUNDARIES](MODULE_BOUNDARIES.md), rule 03) are enforced as modules land; the foundation architecture
test `tests/Architecture/FoundationBoundariesTest` locks in Shared-Kernel independence today.

## Non-claims
This runtime foundation is not a feature-complete application, is not deployed, and is not pilot- or
production-ready. No domain is owned; no infrastructure is provisioned.
