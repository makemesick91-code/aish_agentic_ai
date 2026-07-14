---
id: "29"
title: Runtime Bootstrap and Operations
domain: runtime-bootstrap-operations
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.6.0 §70 (Step 5 Runtime & Repository Bootstrap); §34, §51, §52, §53"
  - "PRD v1.3.0 (unchanged)"
  - "ADRs 0047–0050; AFR-127..133; rules 04, 08, 09, 10, 11, 23, 24, 28"
supersede: "Permanent for Step 5+. Reproducibility, truthful health states, no-secret handling, and runtime-evidence-before-claims cannot be weakened; superseded only by a higher-version Master Source update preserving these guarantees."
---

# Rule 29 — Runtime Bootstrap and Operations

## Purpose
Keep the Laravel 12 application runtime reproducible, truthful, and safe from a clean checkout onward, without
weakening any security, tenant-isolation, privacy, documentation, or release gate.

## Scope
Runtime versions, environment contract, local bootstrap, health/readiness, queue/scheduler foundation, security
baseline, and runtime verification. Applies to `app/`, `bootstrap/`, `config/`, `routes/`, `database/`,
`resources/`, `tests/`, `scripts/runtime/`, `docker-compose.yml`, `composer.json`, `package.json`.

## Rules
- The runtime baseline **MUST** be Laravel 12 on PHP 8.4 (min `^8.3`), PostgreSQL 17, Redis 7, Node.js 22, with the
  composer platform pinned; versions **MUST** be identical across local, CI, and documentation (ADR 0047; rule 25).
- A clean checkout **MUST** bootstrap reproducibly via `scripts/runtime/bootstrap-local.sh`; bootstrap **MUST** be
  idempotent, fail-fast, **MUST NOT** run as root, **MUST NOT** overwrite an existing `.env`, **MUST NOT** drop the
  database without an explicit flag, and **MUST NOT** print secrets (ADR 0048).
- `.env` files, secrets, credentials, and tokens **MUST NOT** be committed; `.env.example` carries safe placeholders
  only; `APP_DEBUG` **MUST NOT** be true in production (rules 04, 24).
- Health endpoints **MUST** be truthful: `/live` reflects process liveness and depends on no external dependency;
  `/ready` returns 200 only when every mandatory dependency is ready, else 503, and **MUST NOT** leak credentials,
  connection strings, stack traces, queries, or internal paths (ADR 0049; rules 10, 11).
- Connectivity **MUST** be proven, not assumed: database (`select 1`), cache round-trip, and queue
  dispatch+processing **MUST** be exercised by `scripts/runtime/verify-runtime.sh` and tests — an open socket is not
  proof.
- The queue and scheduler foundation **MUST** remain foundation-only: no business/agent jobs or fabricated scheduled
  tasks (rules 02, 05). Retry **MUST NOT** create duplicate side effects; a failed-job path **MUST** exist.
- The application **MUST** apply a security baseline (security headers, trust-none proxy default, production-safe
  errors, no debug in production) and **MUST NOT** introduce a fake runtime CI gate; the backend runtime CI gate
  **MUST** run against real PostgreSQL + Redis and be required on every ready PR (ADR 0050; rule 28).
- Runtime evidence **MUST** precede any runtime/deployment claim; a clean-checkout verification on the exact merged
  SHA **MUST** pass before a Step 5 GO tag (rules 13, 27).

## Required checks
- `scripts/runtime/preflight.sh`, `scripts/runtime/verify-runtime.sh`; `php artisan test`; `vendor/bin/pint --test`;
  `vendor/bin/phpstan analyse`; `composer audit`; `npm audit`; the `backend-runtime-ci` job in `.github/workflows/pr-ci.yml`.

## Evidence
- `docs/architecture/runtime-bootstrap.md`, `docs/getting-started/local-development.md`,
  `docs/operations/runtime-verification.md`, `docs/evidence/step-5/` and `docs/evidence/step-5/runtime/`.

## Related canonical sections
- Master Source v2.6.0 §70; §34 (stack), §51 (observability), §52 (UI), §53 (truthful states); ADRs 0047–0050;
  AFR-127..133; rules 04, 08, 09, 10, 11, 23, 24, 28.

## Supersession
Permanent for Step 5+. Reproducibility, truthful health states, no-secret handling, and
runtime-evidence-before-claims are permanent; superseded only by a higher-version Master Source update that
preserves these guarantees.
