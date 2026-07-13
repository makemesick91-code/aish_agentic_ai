# CI Test Routing Matrix — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.3. Rule: `.claude/rules/28`. ADR 0043. AFR-114,119,125.
Maps change categories to the suites that run. Security, secret-scan, workflow-security, and release-integrity are
**never** routed away (AFR-119).

| Category | Suites run today | Suites when application runtime exists (NOT-YET-AVAILABLE) |
|----------|------------------|------------------------------------------------------------|
| documentation / governance | full documentation-as-code gates (`validate.sh`), secret scan | same |
| workflow | full docs + `validate-workflow-security.sh` + `validate-ci-topology.sh` + classifier/gate tests + ShellCheck | same |
| security / tenancy / config | full safe suite (docs + workflow security + secret scan) | + tenant-isolation, permission, cross-tenant, IDOR/SSRF tests |
| backend | full safe suite | + composer validate, static analysis, unit/feature/integration/architecture/migration tests |
| frontend | full safe suite | + package validate, build, lint, unit, accessibility, asset-size |
| database | full safe suite | + migration up/rollback, fresh install, schema + tenant-ownership + index/constraint checks |
| dependency | full safe suite | + dependency audit, lock-file review, SBOM, vulnerability scan |
| integration / infrastructure | full safe suite | + integration + deployment-config checks |
| ai | full docs (AI docs gates) | + AI-evaluation dataset, PII-leak, prompt-injection, structured-output validity |
| unknown / mixed | **full safe suite (fail closed)** | full safe suite |

## Never routed away
- `scripts/docs/secret-scan.sh` (CI-SEC-02)
- `scripts/ci/validate-workflow-security.sh` (CI-SEC-01/03/04/05) when workflows change or on any full route
- tenant-isolation tests when tenancy-sensitive files change (once the application exists)
- migration gate when schema changes; dependency audit when a manifest/lock changes

## Truthful limitation
The application does not exist yet, so the "runtime" columns are **planned routes**, not executed suites. CI records
them as NOT-YET-AVAILABLE (`docs/ci/CI_CHANGE_CLASSIFICATION.md`); no green status is claimed for a suite that did
not run (AFR-125, rule 23 AFR-093).
