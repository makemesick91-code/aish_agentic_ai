# CICD-CTRL-1 — Security & Secret-Hygiene Review (report-only subagent)

Verdict: no committed secrets; actions SHA-pinned + least-privilege. Conditional GO — fix HIGH + MEDIUMs.

## Findings + resolution
- HIGH: workflow-security gate skippable via attacker-controlled classification (fail-open). A PR editing
  `.github/workflows/*` + `classify-changes.sh` to self-classify docs-only skips workflow-security-ci.
  → FIXED: workflow-security-ci runs UNCONDITIONALLY on every ready PR (security never routed away, AFR-119);
    required gate now requires wf-sec success on ready.
- MEDIUM: script injection via `${{ inputs.reason }}` in full-ci-manual.yml run shell. → FIXED (env var + quoted).
- MEDIUM: validate-workflow-security injection check WARN-only + misses inputs.*/head_ref/comment/commit-message.
  → FIXED: injection is now a FAIL, broadened sources, env-assignment allowed.
- MEDIUM: guard skip-ci guard bypassable ([no ci], [skip actions], ***NO_CI***). → FIXED (broadened) + tests.
- LOW: validator gaps — pull_request_target inline-array form; top-level permissions anchoring. → FIXED (both).
- Clean: no secrets; SHA-pinned checkout; contents:read; no pull_request_target; per-job timeouts; audit script never prints tokens.
