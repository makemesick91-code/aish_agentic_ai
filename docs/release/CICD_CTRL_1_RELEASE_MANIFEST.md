# CICD-CTRL-1 — Release Manifest

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`, `13`.

## Identity
- Repository: `makemesick91-code/aish_agentic_ai`
- Base branch: `main`
- Feature branch: `chore/cicd-ctrl-1-safe-ci-runtime-control`
- Target GO tag: `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go` (annotated, immutable)

## Canonical sources
- Master Source **v2.5.0** (§69; NFR-CI-001..006 in §6). PRD **v1.3.0** (unchanged).
- ADRs **0042–0046**. AFR **105..126**. Claude rule **28**.

## Artifacts added / changed
- Workflows: `.github/workflows/pr-ci.yml`, `main-post-merge.yml`, `full-ci-manual.yml`
  (retired `documentation-foundation.yml`, preserved as evidence).
- Scripts: `scripts/ci/{classify-changes,test-change-classifier,required-gate-decision,test-required-gate,
  validate-ci-topology,validate-workflow-security,fast-local,full-local,audit-ci-runs}.sh`,
  `scripts/release/verify-immutable-tag.sh`.
- Checker updates: `check-version-consistency.sh`, `check-agents.sh`, `check-adr.sh`.
- Docs: `docs/ci/*` (12), `docs/quality/CICD_CTRL_1_*` (3), `docs/release/CICD_CTRL_1_*` (this set).
- Governance: Master Source, AFR catalog, rule 28, ADRs, DECISION_LOG, VERSION_MATRIX, CHANGELOG, DOCUMENT_AUTHORITY,
  AGENTS chain, `.codex`, hooks, skills, MCP manifest.

## Prior immutable tags (MUST remain unchanged)
- `aish-agentic-ai-docs-foundation-v1.0.0-go`
- `aish-agentic-ai-step-2-persona-pilot-v1.0.0-go`
- `aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go`
- `aish-agentic-ai-step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go`

## Release-time fields (filled with real values during execution)
- PR number, draft fast-run IDs, ready full-run ID, final head SHA, merge commit SHA, tag object + peeled commit,
  ruleset before/after — recorded in [Tag Verification](CICD_CTRL_1_TAG_VERIFICATION.md) and
  [Release Report](CICD_CTRL_1_RELEASE_REPORT.md).

## Scope claim
CI/release-process governance only. Application implementation, deployment, pilot readiness, pilot runtime, and
production readiness remain **NOT STARTED**. No domain owned; no package installed; nothing deployed.
