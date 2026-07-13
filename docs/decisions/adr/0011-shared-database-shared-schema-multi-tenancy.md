# ADR 0011 — Shared Database, Shared Schema Multi-Tenancy

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Multi-Tenant SaaS Architect
- **Rule:** `.claude/rules/03`, `20` (AFR-006, AFR-007) · **Canonical:** Master Source v2.3.0 §15.1, §17, §36, §37

## Context
Tenant isolation is a permanent, non-negotiable guarantee. The initial SaaS tiers need a tenancy model that is
secure, simple, and cost-effective, with a path to stronger isolation for enterprise.

## Decision
Use a **shared PostgreSQL database, shared schema, row-level tenant ownership**. Every tenant-owned record
carries `tenant_id`; branch-relevant records carry `branch_id`. Composite unique keys, FKs, and indexes are
tenant-leading. Dedicated enterprise environments are a future option; PostgreSQL RLS is a defense-in-depth
**evaluation** (OD-01), not a Step 3 claim. See [Tenancy Architecture](../../architecture/TENANCY_ARCHITECTURE.md).

## Alternatives
- **Database-per-tenant** — deferred: strongest isolation but heavy ops/cost; reserved for enterprise (future ADR).
- **Schema-per-tenant** — rejected for MVP: migration/ops complexity without proportional benefit at pilot scale.

## Consequences
Simple, low-cost multi-tenancy with app-layer scoping + DB constraints. Requires rigorous, tested isolation and
fitness functions to prevent leakage.

## Impacts
- **Security:** relies on enforced scoping; mitigated by constraints + tests + FF-TEN-01..14.
- **Privacy:** one data plane; redaction and classification centralized.
- **Tenant isolation:** the core subject — enforced on all surfaces (ADR 0012, 0015).
- **Database:** tenant-leading keys/indexes; cross-tenant collision impossible by constraint.
- **Operational:** one DB to back up/restore/observe.
- **Cost:** lowest infra cost for pilot/early tiers.

## Verification / fitness function
FF-TEN-01..14, FF-DATA-01..02. Step 3: [Tenant Isolation Control Matrix](../../security/TENANT_ISOLATION_CONTROL_MATRIX.md);
implementation: cross-tenant/IDOR/leakage tests.

## Related
Requirement: Master Source §15.1, §17, §36. Application rule: AFR-006, AFR-007. ADRs: 0012, 0015, 0014.

## Evidence
`docs/architecture/TENANCY_ARCHITECTURE.md`, `docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md`.

## Non-claims
No tenant data, schema, or RLS policy exists or runs in Step 3.

## Rollback / supersession
Isolation guarantees are permanent; a stronger model (per-tenant DB) is additive via a security ADR.
