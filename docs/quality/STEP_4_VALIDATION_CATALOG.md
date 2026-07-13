# Step 4 Validation Catalog

**Status:** PLANNING BASELINE — validation gates for Step 4 documentation. **Application implementation: NOT STARTED.**
**Rule:** `.claude/rules/21`–`27`. **Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. **Enforced by:**
`scripts/docs/check-step4-coverage.sh`, `scripts/docs/check-brand-tokens.sh`, and the shared documentation gates.

These `V4-*` gate ids are the Step 4 fitness/validation functions referenced from the AFR catalog
(`../architecture/APPLICATION_FOUNDATION_RULES.md`, AFR-073..104). They are **documentation gates** — no
application runtime is exercised.

| Gate | Asserts | Script | AFR |
|------|---------|--------|-----|
| V4-DOM-01 | Preferred primary + ≥2 fallbacks; registrar MFA/transfer-lock/DNSSEC/renewal governance recorded | check-step4-coverage.sh | AFR-075, AFR-078 |
| V4-DOM-02 | Domain availability is point-in-time evidence-based; no false ownership claim | check-step4-coverage.sh | AFR-076 |
| V4-DOM-03 | Canonical subdomain naming; non-production not confused with production | check-step4-coverage.sh | AFR-077 |
| V4-DOM-04 | Email domain enforces SPF/DKIM/DMARC; no-reply limits documented | check-step4-coverage.sh | AFR-080 |
| V4-BRAND-01 | Branded-house hierarchy; working tagline "APPROVED WORKING BASELINE" | check-step4-coverage.sh | AFR-082, AFR-083 |
| V4-BRAND-02 | Brand tokens versioned + planning-labelled + WCAG 2.2 AA target; JSON valid | check-brand-tokens.sh | AFR-084, AFR-085 |
| V4-ENV-01 | Six environments defined; local dev recommended + fallback | check-step4-coverage.sh | AFR-087, AFR-094 |
| V4-ENV-02 | Synthetic-default; no production data in local/test/CI/staging | check-step4-coverage.sh | AFR-088 |
| V4-ENV-03 | Promotion gating; no direct unreviewed pilot/production deploy | check-step4-coverage.sh | AFR-092 |
| V4-DEP-01 | Laravel 12 baseline; no package installed/no lock; framework-major requires ADR | check-step4-coverage.sh | AFR-095, AFR-096 |
| V4-DEP-02 | Supply-chain controls (official registry, typosquat, SBOM); approval vocabulary; pinning/emergency patch | check-step4-coverage.sh | AFR-097, AFR-098 |
| V4-SF-01 | Implementation sequence; EPIC-SF-01..16; sprint roadmap with GO/WATCH/NO-GO | check-step4-coverage.sh | AFR-099, AFR-100 |
| V4-SF-02 | Observability/tested-restore-before-pilot; dedicated isolated deployment-target class | check-step4-coverage.sh | AFR-101, AFR-102 |

## Shared gates also applied to Step 4
Markdown structure (no empty files), internal links, version consistency (Master Source v2.4.0 / PRD v1.3.0),
rule frontmatter (rules 21–27), ADR structure/sequence (0001–0041), AGENTS chain + drift, secret scan, hook
guard tests, Graphify build/query-smoke/drift. See `scripts/docs/validate.sh`.

## Evidence
`docs/evidence/validation/` (gate logs), `docs/evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`,
`docs/evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`.
