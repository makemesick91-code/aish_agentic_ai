# ADR 0050 — Backend Runtime CI Under CICD-CTRL-1

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 5 Runtime & Repository Bootstrap; runtime CI IMPLEMENTED, product features NOT STARTED
- **Owner:** DevOps / Git Release Manager
- **Rule:** `.claude/rules/29`, `.claude/rules/28` (AFR-130) · **Canonical:** Master Source v2.6.0 §70, §69; rules 28, 09

## Context
CICD-CTRL-1 (ADRs 0042–0046) routed runtime suites but recorded them NOT-YET-AVAILABLE because no application
existed (AFR-125, rule 23 AFR-093). Step 5 introduces the Laravel application, so a **real** runtime gate must now
exist — without a fake Laravel gate and without weakening the single-final-head release model or any security gate.

## Decision
`pr-ci.yml` gains a **`backend-runtime-ci`** job that runs on every ready (non-draft) PR against real
`postgres:17-alpine` + `redis:7-alpine` service containers: `composer validate`, install, Pint, PHPStan/Larastan,
`php artisan test`, `composer audit`, `npm ci` + build, `npm audit`, and the end-to-end
`scripts/runtime/verify-runtime.sh` (migrate, `/live`, `/ready` positive + negative, queue dispatch+processing,
scheduler, asset build). It is wired into the stable `pr-ci / Required Gate` via `required-gate-decision.sh`, so a
`skipped` or failed runtime job cannot pass the gate. All actions are pinned to immutable 40-hex SHAs; the workflow
keeps top-level least-privilege `permissions`; drafts still run fast CI only; one authoritative full run targets the
final head. Tag creation and post-merge still run no full CI.

## Alternatives
- **Keep runtime suites NOT-YET-AVAILABLE** — rejected: the application now exists; that would be an untruthful gap
  and leave runtime unguarded.
- **Run runtime CI only when backend files change** — rejected for the required gate: a `skipped` conclusion counts
  as passing under branch protection; running on every ready PR closes that hole (as the doc gate already does).
- **A mock/placeholder Laravel gate** — prohibited (AFR-125): no fake runtime gate.

## Consequences
Every ready PR proves the runtime boots and passes health/queue/scheduler checks against real Postgres + Redis. The
required gate now depends on a genuine runtime result bound to the exact tested SHA (AFR-105/109). Draft-fast and
single-final-head behavior are preserved.

## Impacts
- **Security:** SHA-pinned actions, least-privilege token, secret scan and workflow-security gates unchanged; no
  `pull_request_target`.
- **Privacy:** ephemeral CI service credentials only; no real tenant data in CI.
- **Tenant isolation:** unaffected now; the job is where future cross-tenant tests will run.
- **Database:** ephemeral PostgreSQL 17 service container; migrations run per job.
- **Operational:** one authoritative full run on the final head; stale runs cancelled; post-merge stays lightweight.
- **Cost:** bounded runner minutes; the run budget must not turn a failure into a success (AFR-124).

## Verification / fitness function
`scripts/ci/validate-ci-topology.sh`, `scripts/ci/validate-workflow-security.sh`,
`scripts/ci/test-required-gate.sh` (backend result now required on ready PRs), and the live CI run on the final head.
RT-04, CI-GATE-01.

## Related
Requirement: Master Source v2.6.0 §70, §69; PRD v1.3.0. Application rules: AFR-130, AFR-125. Rules: 29, 28, 09.
ADRs: 0042, 0043, 0046.

## Evidence
`.github/workflows/pr-ci.yml`, `scripts/ci/required-gate-decision.sh`, `scripts/ci/test-required-gate.sh`,
`scripts/runtime/verify-runtime.sh`; `docs/evidence/step-5/`.

## Non-claims
A green backend runtime gate does not claim the product is feature-complete, deployed, pilot-ready, or
production-ready. It attests the runtime foundation boots and its health/queue/scheduler contracts hold on the
tested SHA.

## Rollback
The `backend-runtime-ci` job and its required-gate wiring can be reverted together (job + decision script + tests)
to the CICD-CTRL-1 baseline. Removing it without a verified replacement, or weakening a security gate for speed, is
prohibited without an owner-approved Master Source update.
