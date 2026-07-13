# ADR 0010 — Repository Layout and Module Boundaries

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Principal Software Architect
- **Rule:** `.claude/rules/08`, `20` (AFR-004, AFR-005) · **Canonical:** Master Source v2.3.0 §34, §36; PRD v1.2.0 §22

## Context
A modular monolith (ADR 0009) needs a fixed layout and boundary rules so modules stay decoupled and tenant-safe,
and so future scaffold does not drift.

## Decision
Adopt the layout in [Repository Layout](../../architecture/REPOSITORY_LAYOUT.md) with 17 modules under
`app/Modules/*`, a minimal `app/Shared` kernel, and `tests/{Architecture,Unit,Feature,Integration,Security,
Performance}`. Boundaries and allowed dependencies are fixed in [Module Boundaries](../../architecture/MODULE_BOUNDARIES.md)
and the [Module Dependency Matrix](../../architecture/MODULE_DEPENDENCY_MATRIX.md). Any scaffold created is empty
and marked `FUTURE IMPLEMENTATION SCAFFOLD — NO RUNTIME IMPLEMENTATION`.

## Alternatives
- **Flat Laravel default layout** — rejected: no enforced boundaries.
- **Package-per-module (separate composer packages)** — deferred: heavier; revisit if extraction (ADR 0020) needs it.

## Consequences
Clear ownership and dependency direction; enforceable via fitness functions. Slightly more structure up front.

## Impacts
- **Security:** boundaries limit blast radius; Integration is the single external-effect choke point.
- **Privacy:** data ownership fixed per module (ADR 0014, 0029).
- **Tenant isolation:** every module inherits tenant/branch scoping (ADR 0012, 0015).
- **Database:** each module owns only its tables ([Data Ownership Matrix](../../architecture/DATA_OWNERSHIP_MATRIX.md)).
- **Operational:** predictable structure for CI, tests, and reviews.
- **Cost:** negligible; avoids costly re-layout later.

## Verification / fitness function
FF-MOD-01..05, FF-DATA-01. Step 3: dependency/ownership matrices + coverage check.

## Related
Requirement: Master Source §34, §36. Application rule: AFR-004, AFR-005. ADRs: 0009, 0011, 0016.

## Evidence
`docs/architecture/REPOSITORY_LAYOUT.md`, `MODULE_BOUNDARIES.md`, `MODULE_DEPENDENCY_MATRIX.md`.

## Non-claims
No production PHP, routes, controllers, models, migrations, or seeders are created in Step 3.

## Rollback / supersession
Layout changes require an ADR + Master Source update; the one-writer-per-table rule is permanent.
