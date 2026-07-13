# Draft → Release Workflow — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`. ADR 0042. AFR-106,107,108,109.

## The flow
```
Implement locally
  → run scripts/ci/fast-local.sh repeatedly
  → create branch commits
  → push branch
  → open DRAFT PR                (pr-ci.yml runs FAST CI only)
  → run reviews while draft
  → fix findings
  → run scripts/ci/full-local.sh (mirrors the ready full CI)
  → push final draft commit; wait for fast CI
  → confirm no further planned change
  → gh pr ready <PR>             (pr-ci.yml runs ONE full CI on the final head)
  → merge if green
```

## Rules
- Do all substantive work locally; CI is confirmation, not discovery (AFR-106).
- Feature PRs open as **drafts** — a draft runs fast CI only (AFR-107).
- Mark **ready** only when every planned change is in; one full release CI targets the final head (AFR-108).
- Any commit after a full CI invalidates the old result and requires a **new** full CI (AFR-109). Do not reuse an
  old CI result after the head changes.
- If full CI fails: fix the root cause, commit, and let full CI re-run. Record the rerun reason; do **not** claim
  "one run" in the final report if a rerun happened (AFR-126).
- If a reviewer requests changes after full CI: the new commit gets a new full CI. Old results are not reused.

## Local commands
| When | Command |
|------|---------|
| During development (loop) | `scripts/ci/fast-local.sh` |
| Before marking ready | `scripts/ci/full-local.sh` |
| Audit CI runs | `scripts/ci/audit-ci-runs.sh [LIMIT]` |
| Verify a tag at release | `scripts/release/verify-immutable-tag.sh <TAG>` |
