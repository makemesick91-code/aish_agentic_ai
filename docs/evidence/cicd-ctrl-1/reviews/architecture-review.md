# CICD-CTRL-1 — Architecture Review (report-only subagent)

Verdict: architecture sound + fail-closed; REQUEST CHANGES for CICD-1 (HIGH) + CICD-2/3 (MEDIUM).

## Findings + resolution
- CICD-1 (HIGH): a draft's green `pr-ci / Required Gate` on SHA X can satisfy branch protection if the PR is
  marked ready on the same unchanged SHA (stale-green window). → FIXED: required-gate posts ONLY on ready PRs
  (`if: always() && draft == false`); drafts leave `Required Gate` pending/expected (drafts are unmergeable).
- CICD-2 (MEDIUM): classifier fail-opens on renames (rename emits only new path). → FIXED: `--no-renames` on the
  diff (rename → delete old + add new, old sensitive path re-classifies + fails closed); rename test added.
- CICD-3 (MEDIUM): ADR 0045 claims injection is enforced but validator only WARNed. → FIXED: injection is now a
  FAIL with broadened untrusted sources (env-assignment remediation allowed).
- CICD-4 (LOW): timeout check was a whole-file line count. → FIXED: per-job timeout-minutes scan.
- CICD-5 (LOW): topology push-trigger awk kept state for whole file. → FIXED: scoped to the on: block.
- Confirmed sound: BASE/HEAD passing, fail-closed defaults, gate fail-closed logic, run_workflow_security consumption, ADR-vs-code match.
