# Step 4 Requirements Traceability Matrix

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. **Application implementation: NOT STARTED.**
**Rule:** `.claude/rules/12`, `19`, `27`. **Canonical:** Master Source v2.4.0 §68; PRD v1.3.0 §31.

Computes the chain **Canonical decision → planning doc → ADR/decision record → AFR → Claude rule → validation →
evidence** for every material Step 4 decision. No permanent decision is orphan.

| # | Canonical decision | Planning doc | ADR / decision | AFR | Rule | Validation | Evidence |
|---|--------------------|--------------|----------------|-----|------|------------|----------|
| 1 | Domain strategy (preferred `aishagentic.ai` + fallbacks; not owned) | `docs/domain/DOMAIN_STRATEGY.md`, `DOMAIN_CANDIDATE_EVALUATION.md` | ADR 0033, DL-S4-01 | AFR-073..078 | 21 | V4-DOM-01/02 | `docs/evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md` |
| 2 | Subdomain/URL + DNS/TLS/email + OAuth redirect | `docs/domain/SUBDOMAIN_AND_URL_MATRIX.md`, `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md`, `OAUTH_REDIRECT_URI_PLAN.md` | ADR 0033 | AFR-077, 079, 080 | 21 | V4-DOM-03/04 | domain docs |
| 3 | Domain ownership/renewal governance | `docs/domain/DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md` | ADR 0033 | AFR-074, 075 | 21 | V4-DOM-01 | domain docs |
| 4 | Brand architecture + descriptor | `docs/brand/BRAND_FOUNDATION.md`, `BRAND_ARCHITECTURE.md` | ADR 0041, DL-S4-02 | AFR-081, 082 | 22 | V4-BRAND-01 | brand docs |
| 5 | Working tagline (APPROVED WORKING BASELINE) | `docs/brand/WORKING_TAGLINE_DECISION.md` | ADR 0041, DL-S4-03 | AFR-083 | 22 | V4-BRAND-01 | brand docs |
| 6 | Visual tokens + accessibility (WCAG 2.2 AA) | `docs/brand/VISUAL_IDENTITY_BASELINE.md`, `ACCESSIBILITY_BASELINE.md`, `tokens/brand-tokens.v1.json` | ADR 0041 | AFR-084, 085 | 22 | V4-BRAND-02 | brand token JSON |
| 7 | Brand voice + logo governance (no final claim) | `docs/brand/BRAND_VOICE.md`, `LOGO_AND_ASSET_GOVERNANCE.md` | ADR 0041 | AFR-086 | 22 | check-step4-coverage.sh | brand docs |
| 8 | Environment topology (six environments) | `docs/environments/ENVIRONMENT_STRATEGY.md`, `ENVIRONMENT_MATRIX.md`, `ENVIRONMENT_NAMING_STANDARD.md` | ADR 0034 | AFR-087, 089 | 23 | V4-ENV-01 | environment docs |
| 9 | Environment data policy (synthetic default) | `docs/environments/DATA_POLICY_BY_ENVIRONMENT.md` | ADR 0035 | AFR-088 | 23 | V4-ENV-02 | environment docs |
| 10 | Local development strategy | `docs/environments/LOCAL_DEVELOPMENT_STRATEGY.md` | ADR 0036 | AFR-094 | 23 | V4-ENV-01 | environment docs |
| 11 | Environment promotion policy | `docs/environments/ENVIRONMENT_PROMOTION_POLICY.md` | ADR 0034 | AFR-092 | 23 | V4-ENV-03 | environment docs |
| 12 | CI runtime plan (no fake runtime CI) | `docs/environments/CI_RUNTIME_PLAN.md` | ADR 0026, 0034 | AFR-093 | 23 | check-step4-coverage.sh | environment docs |
| 13 | Configuration & secret classification | `docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md` | ADR 0037 | AFR-090, 091 | 24 | secret-scan.sh | `docs/evidence/validation/secret-scan.log` |
| 14 | Dependency baseline (nothing installed) | `docs/dependencies/DEPENDENCY_BASELINE.md`, `DEPENDENCY_APPROVAL_MATRIX.md` | ADR 0038, D-025 | AFR-095, 096 | 25 | V4-DEP-01 | `docs/evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md` |
| 15 | Supply-chain + upgrade governance | `docs/dependencies/SUPPLY_CHAIN_GOVERNANCE.md`, `UPGRADE_AND_SECURITY_PATCH_POLICY.md` | ADR 0038 | AFR-097, 098 | 25 | V4-DEP-02 | dependency docs |
| 16 | SaaS Foundation implementation sequence | `docs/planning/SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md`, `SAAS_FOUNDATION_EPIC_CATALOG.md`, `SAAS_FOUNDATION_SPRINT_ROADMAP.md` | ADR 0039 | AFR-099, 100, 101 | 26 | V4-SF-01 | planning docs |
| 17 | First implementation sprint (SPRINT-SF-00) | `docs/planning/NEXT_IMPLEMENTATION_SPRINT.md` | ADR 0039, DL-S4-04 | AFR-099 | 26 | V4-SF-01 | planning docs |
| 18 | Deployment-target class (dedicated/isolated) | `docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md` | ADR 0040, D-024 | AFR-102 | 26 | V4-SF-02 | operations docs |
| 19 | Backup/restore/observability/rollback plans | `docs/operations/STEP_4_BACKUP_RESTORE_PLAN.md`, `STEP_4_OBSERVABILITY_PLAN.md`, `STEP_4_ROLLBACK_PLAN.md` | ADR 0040 | AFR-101 | 26, 11 | V4-SF-02 | operations docs |
| 20 | Truthful planning states + GO-tag scope | all Step 4 docs; `docs/quality/STEP_4_GO_WATCH_NO_GO.md` | ADRs 0033–0041, D-026 | AFR-103, 104 | 27 | check-step4-coverage.sh | this matrix |

**Orphan critical requirements: none.** Every material Step 4 decision traces to a planning doc, an ADR/decision
record, an AFR, a Claude rule, a validation gate, and evidence.
