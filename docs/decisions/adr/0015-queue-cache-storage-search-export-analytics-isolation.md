# ADR 0015 — Queue, Cache, Storage, Search, Export, and Analytics Isolation

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Multi-Tenant SaaS Architect
- **Rule:** `.claude/rules/03`, `07`, `20` (AFR-011..017) · **Canonical:** Master Source v2.3.0 §17, §37, §42

## Context
Tenant isolation must hold on non-database surfaces too. Historically these are where leakage hides
(shared cache keys, unscoped exports, cross-tenant analytics, AI retrieval).

## Decision
Enforce tenant/branch scoping on **every** surface: queue jobs (context in payload), cache (tenant-prefixed
keys), storage (tenant/branch paths), search (tenant-filtered index), exports (scoped + audited), analytics/
read-models (tenant-scoped projections), AI retrieval and knowledge retrieval (tenant-filtered, minimal context),
notifications (scoped), audit views, and tenant-facing logs (redacted). Reporting **MUST NOT** bypass isolation.
See [Tenant Isolation Control Matrix](../../security/TENANT_ISOLATION_CONTROL_MATRIX.md).

## Alternatives
- **DB-only isolation** — rejected: ignores cache/queue/storage/search/export leakage vectors.

## Consequences
Uniform isolation model across surfaces; each surface needs an explicit control + test.

## Impacts
- **Security:** closes cache-collision, storage-collision, export/search/AI-retrieval leakage paths.
- **Privacy:** exports and analytics never cross tenants; redaction on logs.
- **Tenant isolation:** the core subject — FF-TEN-01..14 map 1:1 to surfaces.
- **Database:** read-models are scoped projections, not raw cross-tenant reads.
- **Operational:** tenant-isolation anomaly alert (ADR 0024).
- **Cost:** low.

## Verification / fitness function
FF-TEN-01..14. Step 3: control matrix rows; implementation: per-surface leakage tests.

## Related
Requirement: Master Source §17, §37, §42. Application rule: AFR-011..017. ADRs: 0011, 0012, 0023, 0024.

## Evidence
`docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md`, `docs/architecture/TENANCY_ARCHITECTURE.md`.

## Non-claims
No queue, cache, storage, search, export, or analytics runtime exists in Step 3.

## Rollback / supersession
Per-surface isolation is permanent; superseded only by a security ADR + Master Source update.
