# CI Run Budget — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`. ADR 0046. AFR-124.
The budget is **observability only**. It MUST NOT turn a failure into a success, hide a failure, or forbid a
legitimate rerun after a failure or corrective commit.

## Targets
| Situation | Target active runs | Full release suite? |
|-----------|--------------------|---------------------|
| Draft PR head | ≤ 1 fast CI run | No |
| Ready final PR head | ≤ 1 full CI run | Yes |
| New head arrives | old run cancelled; 1 new run allowed | per draft/ready |
| Merge to `main` | 1 lightweight verification run | No |
| Tag | 0 full CI runs | No |
| Post-tag evidence | 0 full CI runs (GitHub Release artifact) | No |

## Metrics (reviewed monthly against real run evidence)
- Duplicate full-run target for a final head: **zero**.
- Stale completed-run target: minimized via concurrency cancellation.
- Cancellation effectiveness: cancelled/(cancelled+superseded) from run history.
- Fast CI target duration and full documentation CI target duration (set from the baseline audit).
- Post-merge target duration: substantially below the full release CI.
- Future backend/runtime CI targets and cache-hit monitoring: deferred until the application exists (WATCH).

## Baseline (from `docs/evidence/cicd-ctrl-1/baseline/`)
17 runs analysed; 0 duplicate-SHA runs; 0 push+PR duplicate SHAs; durations are **APPROXIMATE FROM RUN DURATION**
(GitHub does not expose billed runner minutes on the runs API). Targets are set from this baseline, not from
unrealistic numbers.

## Non-negotiable
A run that exceeds its target is a signal to investigate, never a reason to mark a failing run green. Reruns after a
real failure or a reviewer-requested change are expected and MUST be reported truthfully (no "one run forever"
claim) — AFR-124, AFR-126.
