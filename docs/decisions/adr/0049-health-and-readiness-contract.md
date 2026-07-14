# ADR 0049 — Health and Readiness Contract

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 5 Runtime & Repository Bootstrap; probes IMPLEMENTED, business surfaces NOT STARTED
- **Owner:** Principal Architect / SRE
- **Rule:** `.claude/rules/29` (AFR-129) · **Canonical:** Master Source v2.6.0 §70; rules 10, 11

## Context
Operations and future load balancers need a truthful way to distinguish "the process is alive" from "the process
is ready to serve traffic". Health responses must never leak sensitive detail and must never report a false-ready
state (rule 10 truthful states, Master Source §53).

## Decision
The application exposes two probes registered **outside** the web middleware group (no session/cookies/CSRF):
`GET /live` returns HTTP 200 whenever the process can serve a request and depends on **no** external dependency;
`GET /ready` returns HTTP 200 only when every configured readiness check passes (database `select 1`, cache
round-trip, mandatory configuration), otherwise HTTP 503 with a coarse, non-sensitive per-check breakdown. The
readiness check list lives in `config/health.php` so it is environment- and test-overridable. Responses never
contain credentials, connection strings, stack traces, queries, or internal paths; diagnostic detail is logged
server-side only.

## Alternatives
- **Single `/health` endpoint** — rejected: cannot distinguish liveness from readiness, causing restart storms.
- **Readiness depending on all subsystems including optional ones** — rejected: readiness checks only mandatory
  dependencies; optional integrations must not fail readiness.
- **Returning exception detail for debuggability** — rejected: violates no-leak rule 04/10.

## Consequences
Orchestrators can restart on liveness and gate traffic on readiness. A degraded dependency yields a truthful 503,
never a false 200. The contract is covered by tests for both the success and failure paths.

## Impacts
- **Security:** no sensitive detail in responses; probes set no session cookies; server-side-only diagnostics.
- **Privacy:** no PII or medical data in any health output.
- **Tenant isolation:** probes are tenant-agnostic infrastructure endpoints; expose no tenant data.
- **Database:** readiness performs a trivial `select 1`; no schema dependency.
- **Operational:** liveness vs readiness separation enables safe restarts and traffic gating.
- **Cost:** negligible; probes are cheap and create no sessions.

## Verification / fitness function
`tests/Feature/Health/*` assert 200-ready, 503-not-ready, cookie-free liveness, and no-leak bodies;
`scripts/runtime/verify-runtime.sh` exercises the positive AND negative (Redis-down → 503) paths over HTTP. RT-03.

## Related
Requirement: Master Source v2.6.0 §70; PRD v1.3.0. Application rules: AFR-129, AFR-131. Rules: 29, 10, 11. ADRs:
0047, 0050.

## Evidence
`app/Http/Controllers/Health/*`, `app/Support/Health/*`, `config/health.php`,
`docs/operations/runtime-verification.md`; `docs/evidence/step-5/runtime/live.json`, `ready.json`.

## Non-claims
Truthful health probes do not claim the application is feature-complete, deployed, pilot-ready, or production-ready.
A 200 `/ready` means dependencies are reachable, not that business capabilities exist.

## Supersession
The probe contract is permanent for Step 5+. New readiness checks may be added; removing the no-leak or
truthful-state guarantees requires an owner-approved Master Source update.
