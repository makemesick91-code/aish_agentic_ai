# ADR 0046 — Required Check, Repository Ruleset, and CI Run Budget

- **Status:** Accepted (2026-07-13, Asia/Makassar) — CICD-CTRL-1; CI governance CONFIGURED, application NOT STARTED
- **Owner:** Git Release Manager
- **Rule:** `.claude/rules/28` (AFR-112, AFR-123, AFR-124) · **Canonical:** Master Source v2.5.0 §69; rule 13

## Context
`main` currently has no branch protection and no rulesets. A single-final-head release gate is only meaningful if a
stable required check is actually enforced. The repository is effectively solo-maintainer, so an enforcement model
must not create a self-approval deadlock while still requiring a PR and the stable status check.

## Decision
The stable required check is the status context **`pr-ci / Required Gate`** — a name kept intentionally stable so
branch protection does not need frequent updates. A repository ruleset on `main` requires that check, blocks
force-push and deletion, and requires a pull request; admin bypass is not used and the before/after ruleset JSON plus
a rollback payload are recorded. The exact required-check context is confirmed from a real successful run before the
ruleset is applied. A **CI run budget** targets one active fast run per draft head and one active full run per ready
final head, one lightweight run per main merge, and zero full runs for tags/evidence; the budget is observability
only and MUST NOT turn a failure into a success.

## Alternatives
- **No enforcement** — rejected: a required gate that is not enforced does not gate anything.
- **Require human review approval (solo maintainer)** — rejected for this phase: creates a self-approval deadlock;
  PR + required status check are enforced where the platform permits, recorded as a governance decision.
- **Frequently-renamed check** — rejected: breaks branch protection on every rename.

## Consequences
`main` is protected by a stable required gate; releases are gated on real green CI for the exact head. Renaming the
gate is a deliberate, managed transition. The run budget is reviewed monthly against actual run evidence.

## Impacts
- **Security:** force-push/deletion blocked; required green CI before merge; no admin bypass.
- **Privacy:** ruleset JSON contains no secrets.
- **Tenant isolation:** unaffected (governance-level).
- **Database:** none.
- **Operational:** rollback payload stored; before/after ruleset evidence recorded; run-budget metrics tracked.
- **Cost:** the run budget targets minimal runner minutes without hiding failures.

## Verification / fitness function
`scripts/ci/validate-ci-topology.sh` asserts the stable `Required Gate` with `if: always()`; ruleset before/after
JSON under `docs/evidence/cicd-ctrl-1/ruleset/`; a live PR confirms the enforced check name. CI-GATE-01, CI-RULE-01,
CI-BUD-01.

## Related
Requirement: Master Source v2.5.0 §69; PRD v1.3.0. Application rules: AFR-112, AFR-123, AFR-124. Rules: 28, 13, 09.
ADRs: 0042, 0044.

## Evidence
`docs/ci/REQUIRED_CHECK_GOVERNANCE.md`, `docs/ci/CI_RUN_BUDGET.md`, `docs/ci/CI_ROLLBACK_PLAN.md`;
`docs/evidence/cicd-ctrl-1/ruleset/*`.

## Non-claims
Enforcement of a required CI gate does not claim the application is implemented, deployed, pilot-ready, or
production-ready. The run budget does not guarantee exactly one run; legitimate reruns after failures are expected.

## Rollback
The ruleset can be reverted using the recorded before-state JSON and rollback payload. Reducing required checks
without a verified replacement, or using admin bypass, is prohibited without an owner-approved Master Source update.
