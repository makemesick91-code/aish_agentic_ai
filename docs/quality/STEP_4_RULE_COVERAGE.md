# Step 4 Rule Coverage

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. **Application implementation: NOT STARTED.**
**Rule:** `.claude/rules/12`. **Canonical:** Master Source v2.4.0 §68; PRD v1.3.0.

Maps each Step 4 Claude rule to its ADRs, AFRs, planning docs, and validation gate. Coverage is COVERED for every
row; **No critical gap**.

| Rule | Domain | ADRs | AFRs | Planning docs | Validation | Coverage |
|------|--------|------|------|---------------|------------|----------|
| `21-domain-and-dns-governance` | Domain/DNS/email | 0033 | AFR-073..080 | `docs/domain/*` | V4-DOM-01..04; check-step4-coverage.sh | COVERED |
| `22-brand-governance` | Brand/visual | 0041 | AFR-081..086 | `docs/brand/*` | V4-BRAND-01..02; check-brand-tokens.sh | COVERED |
| `23-environment-separation` | Environments | 0034, 0035, 0036 | AFR-087..089, 092, 093, 094 | `docs/environments/*` | V4-ENV-01..03 | COVERED |
| `24-configuration-and-secrets` | Config/secrets | 0037 | AFR-090, 091 | `docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md` | secret-scan.sh; check-step4-coverage.sh | COVERED |
| `25-dependency-and-supply-chain` | Dependencies | 0038 | AFR-095..098 | `docs/dependencies/*` | V4-DEP-01..02 | COVERED |
| `26-saas-foundation-implementation-planning` | SaaS Foundation | 0039, 0040 | AFR-099..102 | `docs/planning/*`, `docs/operations/STEP_4_*` | V4-SF-01..02 | COVERED |
| `27-truthful-planning-states` | Governance | — (0033–0041) | AFR-103, 104 | all Step 4 docs | check-step4-coverage.sh (truthful-state assertions) | COVERED |

## AGENTS / Codex sync
Root `AGENTS.md` carries the Step 4 planning summary (AFR-073..104) and the active v2.4.0 pointer; `scripts/codex/
check-agents.sh` asserts no CLAUDE/AGENTS drift. One source of truth (AFR-069).

## Assertion
All Step 4 permanent decisions map to a rule, an ADR, an AFR, a planning doc, and a validation gate. **No critical gap.**
