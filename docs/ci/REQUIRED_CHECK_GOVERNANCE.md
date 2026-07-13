# Required Check Governance — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.5. Rule: `.claude/rules/28`, `13`. ADR 0046. AFR-112,123.

## The stable required check
The single required status context is **`pr-ci / Required Gate`** (workflow `pr-ci` / job `Required Gate`). The
name is kept **stable** so branch protection does not need frequent updates. The gate uses `if: always()`, inspects
the results of the routed jobs, and:
- is **RED on a draft PR** (a draft runs fast CI only; its pass must never satisfy branch protection on the same
  SHA once the PR is marked ready). The job is deliberately **run-and-fail**, not `if:`-skipped — a *skipped*
  required check is treated as **passing** by branch protection, which would reopen the stale-green hole;
- on a **ready** PR: fails on any job `failure`, an unexpected `cancelled` required input, a missing classification
  (fail closed), or unless BOTH the full-documentation job AND the workflow-security job succeeded;
- passes only on a ready PR with all required jobs green (intentional `skipped` of the draft-fast job on a ready PR
  is fine).

Decision logic lives in `scripts/ci/required-gate-decision.sh` and is unit-tested by
`scripts/ci/test-required-gate.sh`.

## Enforcement (`main` ruleset)
`main` enforces a repository ruleset that:
- requires a pull request;
- requires the status check `pr-ci / Required Gate`;
- blocks force-push and branch deletion;
- does **not** use admin bypass.

The exact required-check context is confirmed from a real successful run **before** the ruleset is applied. The
before/after ruleset JSON and a rollback payload are stored under `docs/evidence/cicd-ctrl-1/ruleset/`.

## Solo-maintainer note
The repository is effectively solo-maintainer. Requiring a second human approval would create a self-approval
deadlock, so PR + the stable required status check are enforced where the platform permits; the approval-count
decision is recorded (D-031). This is not an admin bypass and does not weaken the required CI gate.

## Migration safety
When replacing a workflow that provided a required check, the new check name is observed on a live PR first, the
ruleset is updated to the new stable name, and only then is the obsolete workflow removed — never leaving two full
workflows active for the same event (see [Rollback Plan](CI_ROLLBACK_PLAN.md)).
