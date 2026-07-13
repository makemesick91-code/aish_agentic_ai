# ADR 0042 — PR Lifecycle CI Architecture (Draft-Fast, Ready-Full, SHA-Bound)

- **Status:** Accepted (2026-07-13, Asia/Makassar) — CICD-CTRL-1; CI governance CONFIGURED, application NOT STARTED
- **Owner:** Principal CI/CD Architect
- **Rule:** `.claude/rules/28` (AFR-105..111, AFR-124, AFR-126) · **Canonical:** Master Source v2.5.0 §69; PRD v1.3.0

## Context
The single `documentation-foundation.yml` ran the full suite on every `pull_request` (including drafts) and again
on every `push` to `main` after merge. The baseline audit (`docs/evidence/cicd-ctrl-1/baseline/`) shows 0
duplicate-SHA runs but repeated full-suite re-runs post-merge and full CI on drafts — redundant work. CI evidence
must remain bound to the exact tested SHA.

## Decision
Adopt a unified `pr-ci.yml` keyed on `pull_request` (opened/synchronize/reopened/ready_for_review). A **draft** PR
runs fast CI only; a **ready** PR runs one full release CI on the final head. Per-PR `concurrency` with
`cancel-in-progress: true` cancels stale runs when a new head arrives. Any new commit after a full CI invalidates
the prior result and requires a new full CI. A run-budget targets one full run per final head but never converts a
failure into a success; reruns after failures/corrections are legitimate and reported truthfully.

## Alternatives
- **Keep push+PR full CI** — rejected: redundant post-merge full runs; no draft/ready differentiation.
- **Full CI on every draft push** — rejected: wastes runner minutes before review is complete.
- **Merge queue now** — rejected: `merge_group` workflow support and volume do not yet justify it (deferred, WATCH).

## Consequences
Fewer full runs; clearer signal; evidence is exact-SHA. Contributors must open drafts and mark ready deliberately.

## Impacts
- **Security:** unchanged gate coverage; security jobs still run on the full ready suite (see ADR 0045).
- **Privacy:** none (no data surface change).
- **Tenant isolation:** unaffected; tenant-isolation gates run in the full suite once the application exists.
- **Database:** none.
- **Operational:** run-budget observability; concurrency reduces wasted wall-clock.
- **Cost:** lower runner minutes from cancelled stale runs and no draft full CI.

## Verification / fitness function
`scripts/ci/validate-ci-topology.sh` (no feature push trigger; concurrency cancel-in-progress; stable gate) and
`scripts/ci/test-required-gate.sh` (draft fast-only passes, ready requires full doc). CI-TOPO-01/02, CI-GATE-01.

## Related
Requirement: Master Source v2.5.0 §69; PRD v1.3.0. Application rules: AFR-105..111, AFR-124, AFR-126. Rules: 28, 09,
13. ADRs: 0026 (planned CI/CD architecture, refined here), 0043, 0044, 0045, 0046.

## Evidence
`.github/workflows/pr-ci.yml`; `scripts/ci/*`; `docs/evidence/cicd-ctrl-1/baseline/*`; `docs/ci/CI_ARCHITECTURE.md`.

## Non-claims
This does not claim the application is implemented, deployed, pilot-ready, or production-ready. It does not claim CI
can remain valid after the tested SHA changes, nor that reruns are forbidden after a failure or corrective commit.

## Rollback
Revert to a single always-full workflow by restoring the preserved `documentation-foundation.yml` evidence text;
this is a recorded decision. Weakening security/release gates is prohibited without an owner-approved Master Source update.
