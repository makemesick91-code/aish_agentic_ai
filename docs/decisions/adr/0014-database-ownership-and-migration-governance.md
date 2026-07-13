# ADR 0014 — Database Ownership and Migration Governance

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Data / Platform Architect
- **Rule:** `.claude/rules/03`, `07`, `20` (AFR-009, AFR-010) · **Canonical:** Master Source v2.3.0 §36, §37

## Context
Shared-schema tenancy (ADR 0011) requires strict table ownership and safe migrations to avoid cross-module
coupling, tenant-key mistakes, and unsafe schema changes.

## Decision
Each table is owned by exactly one module, which alone writes it and provides its migrations. Adopt
**expand/contract** migrations for zero-downtime; every change is reversible or paired with a documented
forward-fix; destructive production migrations are prohibited without an authorized runbook. See
[Database Architecture](../../architecture/DATABASE_ARCHITECTURE.md) and [Data Ownership Matrix](../../architecture/DATA_OWNERSHIP_MATRIX.md).

## Alternatives
- **Shared migrations folder** — rejected: blurs ownership.
- **Destructive in-place migrations** — rejected: downtime + data-loss risk.

## Consequences
Clear ownership and safe evolution; slightly more migration steps (expand→backfill→switch→contract).

## Impacts
- **Security:** ownership prevents unauthorized cross-module writes.
- **Privacy:** classification per table drives retention/redaction (ADR 0029).
- **Tenant isolation:** tenant-leading keys/constraints enforced in schema.
- **Database:** the core subject; deterministic migration order.
- **Operational:** predictable, reversible deploys; supports rollback (ADR 0027).
- **Cost:** low.

## Verification / fitness function
FF-DATA-01 (one writer), FF-DATA-02 (tenant/branch columns). Implementation: migration-owner + schema tests.

## Related
Requirement: Master Source §36, §37. Application rule: AFR-009, AFR-010. ADRs: 0011, 0027, 0029.

## Evidence
`docs/architecture/DATABASE_ARCHITECTURE.md`, `docs/architecture/DATA_OWNERSHIP_MATRIX.md`.

## Non-claims
No schema or migration exists or runs in Step 3.

## Rollback / supersession
Ownership and migration-safety rules are permanent; superseded only by a data ADR + Master Source update.
