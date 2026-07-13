# CI Runtime Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/09`, `13`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No application runtime CI exists. No secret is
> stored or committed. Provider not selected (WATCH). This is a plan for **future** CI jobs.

## 1. Current CI reality (Step 4)

At Step 4, the only CI that runs is **documentation and planning validation** (markdown, links, version
consistency, coverage, rule validation, secret scan, Graphify smoke, shell lint — Rules 09, 13). There is
**no application**, so there is **no** application runtime CI yet.

> **Truthful-CI rule.** Step 4 MUST NOT add a fake Laravel runtime CI job that always passes without an
> application present. No fake green checks: no job may claim to run unit/feature/integration/security tests
> against code that does not exist. Restated: we MUST NOT create fake passing jobs; a job MUST either exercise
> real code or not exist yet.

## 2. Future runtime CI jobs (PLANNED — add only when the corresponding code exists)

Each job below is added **only** when the application code it validates exists, and each **MUST** genuinely
fail when its target is broken.

| # | Job | Purpose | Services needed | Added when |
|---|-----|---------|-----------------|-----------|
| 1 | `composer validate` | Validate `composer.json`/lock integrity | none | Composer manifest exists |
| 2 | `composer audit` | Dependency vulnerability audit | none | Dependencies exist |
| 3 | Static analysis (PHPStan/Larastan) | Type/logic defects | none | App code exists |
| 4 | Code style (Pint/PHP-CS-Fixer) | Style conformance | none | App code exists |
| 5 | Unit tests | Isolated unit behavior | none | Unit tests exist |
| 6 | Feature tests | HTTP/feature behavior | PostgreSQL, Redis | Feature tests exist |
| 7 | Integration tests | Cross-module + external adapters (mocked) | PostgreSQL, Redis | Integration tests exist |
| 8 | Architecture tests | Module-boundary/dependency fitness (Rule 20) | none | Modules exist |
| 9 | Tenant-isolation tests | Cross-tenant/branch leakage checks (Rule 03) | PostgreSQL, Redis | Tenancy code exists |
| 10 | Security tests | Access control, IDOR, CSRF/XSS/SQLi, webhook forgery, prompt injection | PostgreSQL, Redis | Security surfaces exist |
| 11 | Frontend build | Compile assets | Node LTS | Frontend exists |
| 12 | Frontend tests | Component/e2e where applicable | Node LTS | Frontend tests exist |
| 13 | Migration test | Run migrations up/down on real PostgreSQL + Redis services | PostgreSQL, Redis | Migrations exist |
| 14 | Queue test | Job dispatch/handle with tenant context | Redis | Queue jobs exist |
| 15 | Secret scan | No secrets committed | none | Always (already active) |
| 16 | Dependency review | Review new dependencies on PR | none | Dependencies exist |
| 17 | SBOM generation | Software bill of materials | none | Build exists |
| 18 | Documentation gates | Docs-as-code validation | none | Always (already active) |
| 19 | Release gates | Aggregate release readiness | none | Release process active |

## 3. CI environment rules (MUST)

- The `CI` environment holds only **synthetic** fixtures; real PII/production data is prohibited
  (see [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md)).
- CI secrets **MUST** be CI-scoped, **environment-specific**, and stored in the CI secret store — never
  committed (see [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)).
- CI service containers (PostgreSQL, Redis) **MUST** use the `ci` names/prefixes from
  [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md) and be ephemeral.
- External providers (email, WhatsApp, Google, AI) **MUST** be mocked/stubbed in CI; no real external side
  effects.
- Existing CI gates **MUST NOT** be removed or weakened (Rule 13); new gates may be added.
- A job **MUST NOT** be marked required until it genuinely exercises real code and can fail.

## 4. Service matrix for runtime jobs (PLANNED)

| Job class | PostgreSQL | Redis | Node | Notes |
|-----------|-----------|-------|------|-------|
| Static/style/unit | no | no | no | Fast, no services |
| Feature/integration | yes | yes | no | Ephemeral service containers |
| Migration/queue | yes | yes | no | Real services, synthetic data |
| Frontend | no | no | yes | Build + component tests |

## 5. Gate-to-rule mapping

| Rule | CI enforcement |
|------|----------------|
| Rule 03 (tenant isolation) | Tenant-isolation tests (job 9) |
| Rule 04 (security/secrets) | Secret scan (15), security tests (10), dependency audit (2) |
| Rule 05 (AI governance) | AI evaluation job (added with AI code) |
| Rule 09 (testing/gates) | Jobs 3–14, 18, 19 |
| Rule 20 (architecture) | Architecture tests (8) |

## 6. Progression

CI runtime jobs are added incrementally as implementation proceeds along
[ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md). Until then, only documentation/planning CI
runs, and the truthful CI status is **PLANNING BASELINE — NOT IMPLEMENTED**.
