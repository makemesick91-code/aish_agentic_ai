# Database Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §36 · **Rules:** `.claude/rules/03`, `07`, `20` ·
**ADR:** [0011](../decisions/adr/0011-shared-database-shared-schema-multi-tenancy.md), [0014](../decisions/adr/0014-database-ownership-and-migration-governance.md).

## 1. Engine & tenancy
PostgreSQL, single shared database and schema, row-level tenant ownership (ADR 0011). Table ownership is fixed
in the [Data Ownership Matrix](DATA_OWNERSHIP_MATRIX.md): exactly one writing module per table.

## 2. Tenant-safe schema rules
- Every tenant-owned table has a non-null `tenant_id`; branch-relevant tables add `branch_id`.
- Composite **unique constraints** and **foreign keys** include `tenant_id` so a value can never collide or
  reference across tenants (e.g. `UNIQUE (tenant_id, external_ref)`; FK `(tenant_id, customer_id)`).
- Indexes are tenant-leading (`(tenant_id, …)`) to keep queries scoped and performant.
- No global-unique natural keys on tenant data; ULIDs/UUIDs for public identifiers (no enumerable IDs → IDOR
  resistance, see [Threat Model](../security/STEP_3_THREAT_MODEL.md)).

## 3. Migration governance (ADR 0014)
- Migrations live in each module's `Database/` and create **only that module's tables**.
- **Expand/contract** for zero-downtime: additive change → backfill → switch reads → contract; never a
  destructive rename in one step.
- Every migration is reversible or paired with a documented forward-fix; destructive production migrations are
  prohibited without an explicit authorized runbook (`.claude/rules/13`, `15`).
- Migration order across modules is deterministic (provider registration order + timestamps).

## 4. Data classification & lifecycle
Classification per table is in the ownership matrix; handling rules in
[Data Classification & Handling](../security/DATA_CLASSIFICATION_AND_HANDLING.md). Retention is configurable;
export and deletion are first-class (Audit module, ADR 0029). Audit tables are **append-only** and non-deletable.

## 5. Prohibited medical data
`MED`-class fields (diagnosis, clinical notes, MRN, prescription/medication, odontogram, clinical imagery,
treatment plan/history, insurance/PAN/bank) are **not modelled for storage** and are prohibited from AI input and
public output (`.claude/rules/18`).

## 6. Transactions & consistency
The modular monolith keeps write consistency within a single database transaction per command. Cross-module
effects that must survive commit use the **transactional outbox** (ADR 0016), not distributed transactions.

## 7. Truthful status
No schema, migration, or query exists or runs in Step 3. RLS is a future defense-in-depth evaluation, not a
current claim.
