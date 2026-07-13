# CI Event & Trigger Matrix — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.2. Rule: `.claude/rules/28`. AFR-107,108,110,111,115,116,117.

| Event | Workflow | Jobs that run | Full release suite? | Notes |
|-------|----------|---------------|---------------------|-------|
| PR opened/reopened as **draft** | `pr-ci.yml` | classify-changes → draft-fast-ci → Required Gate | No | Fast CI only (AFR-107) |
| PR `synchronize` while **draft** | `pr-ci.yml` | classify-changes → draft-fast-ci → Required Gate | No | Stale draft run cancelled (AFR-111) |
| PR `ready_for_review` | `pr-ci.yml` | classify-changes → full-documentation-ci (+ workflow-security-ci if routed) → Required Gate | **Yes** | One full CI on the final head (AFR-108) |
| PR `synchronize` while **ready** | `pr-ci.yml` | full suite on new head | **Yes** | New SHA ⇒ new full CI (AFR-109); prior run cancelled |
| `push: main` (merge) | `main-post-merge.yml` | post-merge-verify | No | Lightweight integrity only (AFR-115) |
| Tag push | — | (none) | No | No full CI on tags; `verify-immutable-tag.sh` runs locally/at release (AFR-116) |
| Post-tag evidence | — | (none) | No | GitHub Release artifact, not a full-CI PR (AFR-117) |
| `workflow_dispatch` | `full-ci-manual.yml` | full-ci | Yes (manual) | On-demand; does not replace the required PR check |

## Anti-duplication
There is **no** feature-branch `push` trigger on `pr-ci.yml`, so a feature SHA never runs full CI twice (once for
`push`, once for `pull_request`) — AFR-110. `push` is scoped to `main` and only drives the lightweight post-merge
workflow. Baseline audit (`docs/evidence/cicd-ctrl-1/baseline/duplicate-sha-report.md`) confirms 0 duplicate-SHA
runs before this change; the change additionally removes redundant full-suite re-runs on `push: main` and on drafts.
