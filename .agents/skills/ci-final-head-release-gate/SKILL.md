# Skill: ci-final-head-release-gate

Drive and verify the single full release CI on the final ready PR head. Rule: `.claude/rules/28`. ADR 0042, 0046.

## Trigger
- When a draft PR has completed review and all planned changes are in, ready to mark ready-for-review.

## Non-trigger
- Do not use on a draft still under review, or to bypass a failing gate.

## Workflow (read-only inspection + one state change)
1. Confirm `scripts/ci/full-local.sh` is green and the head SHA is final.
2. `gh pr ready <PR>`; record the timestamp and final head SHA.
3. Watch `gh pr checks <PR> --watch`; confirm one full CI on the final head, no duplicate feature push+PR run,
   the `pr-ci / Required Gate` conclusion, and the exact tested SHA.
4. If full CI fails: fix the root cause, commit, and re-run full CI on the new head. Record the rerun reason —
   never claim "one run" if a rerun happened (AFR-126).

## Safety boundaries
No admin bypass. No merge here (that is the merge step). Never reuse a CI result after the head changes.

## Required output
PR number, final head SHA, full-run ID, required-gate conclusion, and the actual number of full runs (with rerun
reasons if any).

## Failure behavior
On failure, report the failing job and stop; do not merge on red or on a stale result.
