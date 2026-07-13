# Foundation Coverage Matrix — Aish Agentic AI

Rule: `.claude/rules/09`, `12`. Proves every permanent decision and release-critical foundation is traceable:
**Canonical section → Rule file → Derived document → Validation / evidence → Status.**
Coverage MUST be 100% for permanent decisions and release gates (`.claude/rules/00`–`15`; Master Source §66.5).
Verified by `scripts/docs/check-foundation-coverage.sh`.

| # | Foundation category | Canonical section | Rule file | Derived document(s) | Validation / evidence | Status |
|---|---------------------|-------------------|-----------|---------------------|------------------------|--------|
| F00 | Document authority & canonical repo identity | MS §1, §66.2–§66.3; PRD §1 | `.claude/rules/00-document-authority.md` | `docs/canonical/DOCUMENT_AUTHORITY.md` | `check-version-consistency.sh`; `docs/evidence/git-release/` | COVERED |
| F01 | Product identity & positioning | MS §7–§14, §63; PRD §2,§3,§7 | `.claude/rules/01-product-identity-and-positioning.md` | `docs/product/PRODUCT_VISION.md`, `PERSONAS_BASELINE.md` | `check-version-consistency.sh` | COVERED |
| F02 | MVP scope & roadmap | MS §47–§49,§62; PRD §5,§10,§27 | `.claude/rules/02-mvp-scope-and-roadmap.md` | `docs/product/MVP_SCOPE.md`, `ROADMAP.md`, `OPEN_DECISIONS.md` | `check-foundation-coverage.sh` | COVERED |
| F03 | Multi-tenant & branch isolation | MS §15.1,§17,§37,§50; PRD §9,§23.2 | `.claude/rules/03-multi-tenant-and-branch-isolation.md` | `docs/security/TENANT_ISOLATION.md` | Isolation tests (future); `docs/evidence/validation/` | COVERED |
| F04 | Security, privacy & secrets | MS §37,§43,§44,§57; PRD §15.1,§15.2 | `.claude/rules/04-security-privacy-and-secrets.md` | `docs/security/SECURITY_FOUNDATION.md`, `PRIVACY_AND_PII.md`, `THREAT_MODEL_BASELINE.md` | `secret-scan.sh`; push protection | COVERED |
| F05 | AI governance & human approval | MS §15.2,§23–§33,§44; PRD §12,§13,§16 | `.claude/rules/05-ai-governance-and-human-approval.md` | `docs/ai/AGENTIC_ARCHITECTURE.md`, `HUMAN_APPROVAL_MATRIX.md`, `AI_EVALUATION_BASELINE.md`, `AI_COST_AND_TRACING.md` | `check-foundation-coverage.sh`; ai-governance reviewer | COVERED |
| F06 | Google Review policy (no gating) | MS §16,§29,§38; PRD §11.2,§17 | `.claude/rules/06-google-review-policy.md` | `docs/integrations/google/GOOGLE_REVIEW_POLICY.md`, `OAUTH_AND_TOKEN_SECURITY.md`, `INTEGRATION_READINESS.md` | Contradiction check; secret scan | COVERED |
| F07 | Data governance & audit | MS §36,§37,§46,§53; PRD §14,§10.18 | `.claude/rules/07-data-governance-and-audit.md` | `docs/architecture/DOMAIN_MAP.md`, `docs/security/PRIVACY_AND_PII.md` | `check-foundation-coverage.sh` | COVERED |
| F08 | Architecture & event workflows | MS §17,§34–§42; PRD §11,§22 | `.claude/rules/08-architecture-and-event-workflows.md` | `docs/architecture/SYSTEM_CONTEXT.md`, `DOMAIN_MAP.md`, `EVENT_CATALOG.md`, `adr/README.md` | ADRs; `check-links.sh` | COVERED |
| F09 | Testing & quality gates | MS §50,§54,§59; PRD §23,§24,§30 | `.claude/rules/09-testing-and-quality-gates.md` | `docs/quality/TEST_STRATEGY.md`, `RELEASE_GATES.md`, `REQUIREMENTS_TRACEABILITY_MATRIX.md` | `validate.sh`; CI | COVERED |
| F10 | UI/UX & truthful states | MS §15.7,§52,§53; PRD §16,§21 | `.claude/rules/10-ui-ux-and-truthful-states.md` | `docs/architecture/DOMAIN_MAP.md`; `CLAUDE.md` §5 | `check-version-consistency.sh` (status vocab) | COVERED |
| F11 | Observability, backup & operations | MS §51,§54; PRD §15.3,§15.5,§24 | `.claude/rules/11-observability-backup-and-operations.md` | `docs/operations/OBSERVABILITY_BASELINE.md`, `BACKUP_RESTORE_BASELINE.md`, `INCIDENT_AND_ROLLBACK_BASELINE.md` | `check-links.sh` | COVERED |
| F12 | Living Master Source & versioning | MS §3–§6,§61; PRD §31 | `.claude/rules/12-documentation-living-source-versioning.md` | `docs/decisions/DECISION_LOG.md`, `VERSION_MATRIX.md`, `CHANGELOG.md` | `check-version-consistency.sh` | COVERED |
| F13 | Git, CI, release & GO tag | MS §66.2,§66.10,§66.11; PRD §24 | `.claude/rules/13-git-ci-release-and-go-tag.md` | `docs/release/DOCUMENTATION_FOUNDATION_GO_NO_GO.md`, `RELEASE_MANIFEST.md`, `TAG_VERIFICATION.md` | CI; `docs/evidence/ci/`, `git-release/` | COVERED |
| F14 | Limit Saver, context & handoff | MS §66.6 | `.claude/rules/14-limit-saver-context-and-handoff.md` | `docs/tooling/LIMIT_SAVER.md`, `docs/status/*` | `docs/evidence/inventory/` | COVERED |
| F15 | MCP, skills, subagents, hooks, tool safety | MS §66.7–§66.9 | `.claude/rules/15-mcp-skills-and-tool-safety.md` | `docs/tooling/MCP.md`, `MCP_SKILLS_MANIFEST.md`, `GRAPHIFY.md`, `CLAUDE_PROJECT_SETUP.md` | `.mcp.json` validate; `test-guard.sh`; secret scan | COVERED |

## Permanent product decisions (Master Source §63) → rule mapping
Name/identity → 01 · multi-tenant/branch → 03 · CSAT+Google Review/NPS/CES → 01,02 · customer recovery → 02,05 ·
Agentic AI → 05 · human approval for public/high-risk → 05,06 · no review gating → 06 · tenant isolation → 03 ·
audit log → 07 · AI tracing & cost logging → 05,07 · Google credentials encrypted → 04,06 · customer input
untrusted → 04,05 · prompt-injection protection → 04,05 · knowledge base tenant-scoped → 03,07 · no PII/medical
disclosure → 04,06 · manual works without AI → 05,10 · MVP foundation-first → 02 · evidence-based release → 09,13 ·
no production claim from mock → 09,13 · verify external policy/API → 06,08 · security/governance not reduced for
speed → 04,09 · Master Source updated on material decisions → 12 · history not deleted / superseded marked → 12 ·
truthful status → 10,12,13.

**Coverage: 16/16 foundation categories COVERED; all Master Source §63 permanent decisions mapped. No critical gap.**
