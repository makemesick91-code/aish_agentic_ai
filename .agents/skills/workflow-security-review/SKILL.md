# Skill: workflow-security-review

Statically review GitHub Actions workflows for the CICD-CTRL-1 security baseline. Rule: `.claude/rules/28`, `04`.
ADR 0045. Read-only.

## Trigger
- When any file under `.github/workflows/` or `scripts/ci/` changes, or before a release.

## Non-trigger
- Do not use to modify workflows or repository settings (report only).

## Workflow (read-only)
1. `scripts/ci/validate-workflow-security.sh` — actions pinned to a 40-hex SHA, top-level `permissions:` present,
   no `write-all`, no `pull_request_target`, no `curl|sh`, per-job `timeout-minutes`, no `[skip ci]` enablement.
2. `scripts/ci/validate-ci-topology.sh` — single stable required gate, no feature push trigger, concurrency
   cancel-in-progress, lightweight post-merge.
3. Report each control as PASS/FAIL with the offending file/line.

## Safety boundaries
No mutation. Never approve an unpinned action, a broad token, or `pull_request_target` privileged execution.

## Required output
Per-control PASS/FAIL and a list of any BLOCKER/HIGH findings with file references.

## Failure behavior
On any FAIL, surface it as a BLOCKER and stop the release/merge until fixed.
