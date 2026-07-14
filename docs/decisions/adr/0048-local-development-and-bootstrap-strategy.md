# ADR 0048 — Local Development and Bootstrap Strategy

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 5 Runtime & Repository Bootstrap; local bootstrap IMPLEMENTED, deployment NOT STARTED
- **Owner:** DevOps Engineer
- **Rule:** `.claude/rules/29` (AFR-128) · **Canonical:** Master Source v2.6.0 §70; rules 23, 24

## Context
A new developer must be able to bring the runtime up reproducibly from a clean checkout without hidden knowledge,
and the same path must be safe to run repeatedly. Local PostgreSQL/Redis must be reproducible and must not depend
on a developer's pre-existing system services or credentials.

## Decision
Local services are provided by **Docker Compose** (`postgres:17-alpine`, `redis:7-alpine`) bound to non-standard
host ports (55432/63790) so they never collide with native services. The developer entrypoint is a **Makefile**
wrapping idempotent scripts under `scripts/runtime/`: `preflight.sh` (host + config checks), `bootstrap-local.sh`
(deps → `.env` → key → services → migrate → assets), and `verify-runtime.sh` (end-to-end proof). Scripts are
fail-fast, never run as root, never overwrite an existing `.env`, never drop the database without an explicit
`--fresh` flag, and never print secrets. Compose is a local/verification convenience only — **not** a deployment
topology; pilot/production use a dedicated isolated class (AFR-102).

## Alternatives
- **Laravel Sail** — kept as an available dev dependency but not the mandated path; a thin Compose file plus a
  Makefile is lighter and clearer for CI parity.
- **Native system services** — rejected as the default: not reproducible; requires pre-provisioned credentials.
- **Devcontainer only** — deferred: Compose + Makefile covers local and CI without editor lock-in.

## Consequences
`make bootstrap` and `make verify` are the single UX. The same scripts run in CI (with `VERIFY_COMPOSE=0` against
service containers), so local success predicts CI success. Re-running bootstrap is safe.

## Impacts
- **Security:** no root, no secret printing, no destructive default; `.env` never overwritten or committed.
- **Privacy:** local dev credentials are non-secret defaults; real secrets are per-environment (rule 24).
- **Tenant isolation:** unaffected; no tenant data exists yet.
- **Database:** ephemeral local PostgreSQL volume; `--fresh` gated behind an explicit flag.
- **Operational:** idempotent, fail-fast, actionable errors; identical scripts local and CI.
- **Cost:** local containers only; no cloud cost.

## Verification / fitness function
`scripts/runtime/verify-runtime.sh` runs bootstrap paths and proves migrate + health + queue + scheduler; the CI
backend job runs the same script against service containers. RT-02, RT-06.

## Related
Requirement: Master Source v2.6.0 §70; PRD v1.3.0. Application rules: AFR-128, AFR-102. Rules: 29, 23, 24. ADRs:
0036, 0047, 0050.

## Evidence
`docker-compose.yml`, `Makefile`, `scripts/runtime/*.sh`, `docs/getting-started/local-development.md`;
`docs/evidence/step-5/runtime/`.

## Non-claims
A reproducible local bootstrap does not claim a deployment, staging, pilot, or production environment exists. Docker
Compose here is not a production topology.

## Rollback
The Compose file and scripts can be removed without affecting committed application code; reverting to native
services is a documented local choice. Weakening the no-root / no-secret / no-destructive-default guarantees is
prohibited without an owner-approved Master Source update.
