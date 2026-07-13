# Tenant Isolation Control Matrix — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §17, §37, §42 · PRD v1.2.0 §9, §14.2, §23.2 · **Rules:** `.claude/rules/03`, `20` ·
**ADR:** [0011](../decisions/adr/0011-shared-database-shared-schema-multi-tenancy.md), [0012](../decisions/adr/0012-tenant-and-branch-context-propagation.md), [0015](../decisions/adr/0015-queue-cache-storage-search-export-analytics-isolation.md).

Every isolation surface has a preventive control, a fitness function, an application rule (AFR), and an evidence
pointer. No cross-tenant leakage is permitted on any surface. "Enforced by test" runs at implementation
(`tests/Security`, `tests/Architecture`); Step 3 asserts the control is **specified** per surface.

| Surface | Preventive control | FF | AFR | ADR | Impl. test |
|---------|--------------------|----|-----|-----|-----------|
| DB queries | tenant-context query scope + tenant-leading constraints | FF-TEN-01 | AFR-011 | 0011,0012 | cross-tenant read/write |
| Cache | tenant:branch-prefixed keys; no shared namespace | FF-TEN-02 | AFR-012 | 0015 | cache-key collision |
| Queue jobs | tenant_id in payload; rehydrate before access; no ambient default | FF-TEN-03 | AFR-013 | 0012 | queue-context loss |
| File storage | `/tenant/branch/...` paths; signed, scoped access | FF-TEN-04 | AFR-014 | 0015 | storage-path collision |
| Search | tenant-filtered index/query | FF-TEN-05 | AFR-015 | 0015 | search leakage |
| Export | tenant-scoped + audited; expiring links | FF-TEN-06 | AFR-016 | 0015,0029 | export leakage |
| Analytics/read-models | tenant-scoped projections; no cross-tenant aggregation | FF-TEN-07 | AFR-017 | 0015 | analytics leakage |
| AI retrieval | tenant/branch-filtered, minimal context | FF-TEN-08 | AFR-018 | 0023,0019 | AI-retrieval leakage |
| Knowledge retrieval | tenant-scoped index; no secret/PII/MED | FF-TEN-09 | AFR-046 | 0023 | KB tenant-filter |
| Notifications | tenant-scoped; honors opt-out | FF-TEN-10 | AFR-020 | 0015 | notification scope |
| Public API | scope from credential, not client input; opaque ids | FF-TEN-11 | AFR-037 | 0017,0013 | IDOR / cross-tenant |
| Webhooks | tenant-scoped + signed + replay-protected | FF-TEN-12 | AFR-039 | 0017 | webhook forgery/scope |
| Audit views | tenant-scoped; append-only | FF-TEN-13 | AFR-019 | 0024 | audit scope |
| Tenant-facing logs | tenant-scoped + redacted (no PII/secret/MED) | FF-TEN-14 | AFR-058 | 0024 | log redaction/scope |

## Defense-in-depth
1. **AuthZ**: user scope ⊆ tenant/branch (ADR 0013). 2. **Context**: immutable tenant context on every path
(ADR 0012). 3. **Query scope**: global + repository scoping. 4. **DB constraints**: tenant-leading unique/FK.
5. **Optional RLS**: future evaluation (OD-01), not claimed now.

## Assertion
No critical gap: every isolation surface above maps to a control, FF, AFR, ADR, and a planned test. No tenant
data or isolation test executes in Step 3.
