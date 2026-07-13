# ADR 0012 — Tenant and Branch Context Propagation

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Multi-Tenant SaaS Architect
- **Rule:** `.claude/rules/03`, `20` (AFR-008) · **Canonical:** Master Source v2.3.0 §17, §35, §37

## Context
Row-level tenancy (ADR 0011) is only safe if tenant/branch context is present and correct on **every** path,
including background jobs, schedulers, and event consumers — not just HTTP requests.

## Decision
Resolve an **immutable tenant+branch context** once at each entrypoint and propagate it explicitly through
service → query → job → cache → storage → event. Queue jobs and event envelopes **carry** `tenant_id` and
**rehydrate** context before any data access. A missing/mismatched context is a hard failure — never a silent
"all tenants" default. See [Tenancy Architecture](../../architecture/TENANCY_ARCHITECTURE.md) §2.

## Alternatives
- **Ambient/global current-tenant singleton** — rejected: unsafe across async boundaries; leakage risk.
- **Per-query manual filters only** — rejected alone: error-prone; kept as defense-in-depth *with* context.

## Consequences
Consistent isolation across sync and async work; small boilerplate to thread context (mitigated by Shared Kernel
primitives).

## Impacts
- **Security:** eliminates ambient-context leakage; async jobs cannot run tenant-less.
- **Privacy:** context gates all reads; redaction knows the tenant.
- **Tenant isolation:** foundational mechanism for FF-TEN-01..14.
- **Database:** query scope applied from context; belt-and-suspenders with constraints.
- **Operational:** correlation-id travels with context for tracing.
- **Cost:** negligible.

## Verification / fitness function
FF-TEN-03 (queue context), FF-TEN-01 (query scope). Implementation: job-context and cross-tenant tests.

## Related
Requirement: Master Source §17, §35. Application rule: AFR-008. ADRs: 0011, 0015, 0016.

## Evidence
`docs/architecture/TENANCY_ARCHITECTURE.md`, `docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md`.

## Non-claims
No context resolver or job runs in Step 3.

## Rollback / supersession
Permanent; superseded only by a stronger isolation ADR + Master Source update.
