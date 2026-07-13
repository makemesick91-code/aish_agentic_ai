# Data Ownership Matrix — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §36 (data), §37 (governance) · **Rules:** `.claude/rules/03`, `07`, `20` ·
**ADR:** [0011](../decisions/adr/0011-shared-database-shared-schema-multi-tenancy.md), [0014](../decisions/adr/0014-database-ownership-and-migration-governance.md), [0029](../decisions/adr/0029-data-classification-retention-export-deletion.md).

Each table is owned by exactly **one** module. Only the owning module writes it; other modules read via the
owner's contract/query or via events. Every tenant-owned table carries `tenant_id`; branch-relevant tables also
carry `branch_id`. Classification drives handling (see [Data Classification & Handling](../security/DATA_CLASSIFICATION_AND_HANDLING.md)).

Legend — **T**: has `tenant_id` · **B**: has `branch_id` · Class: `CFG` config · `PII` personal · `FIN`
financial · `PUB` public content · `CRED` credential (encrypted) · `AUD` audit · `AIX` AI trace (redacted) ·
`KB` knowledge · `MED` = **prohibited** (must never be stored/sent to AI/public).

| Owner module | Table | T | B | Class | Retention / notes |
|--------------|-------|---|---|-------|-------------------|
| PlatformAdmin | tenants | — | — | CFG | permanent (soft-delete on offboard) |
| PlatformAdmin | plans | — | — | CFG | permanent |
| PlatformAdmin | platform_admins | — | — | PII | permanent; MFA required |
| Identity | users | T | scoped | PII | tenant lifetime; deletable on request |
| Identity | roles / permissions / role_user | T | — | CFG | tenant lifetime |
| Identity | sessions / mfa_factors | T | — | PII | short retention; rotate |
| Tenancy | branches / org_units | T | B | CFG | tenant lifetime |
| Tenancy | tenant_settings | T | — | CFG | tenant lifetime |
| Billing | subscriptions / entitlements | T | — | FIN | tenant lifetime |
| Billing | usage_records | T | — | FIN | retained for reconciliation; idempotent |
| Billing | invoices | T | — | FIN | statutory retention |
| Customer | customers | T | B | PII | minimized; deletable |
| Customer | customer_consents / opt_outs | T | B | PII | permanent proof-of-consent; opt-out permanent |
| ServiceEvent | service_events / transactions | T | B | PII(meta) | configurable; **no clinical detail** |
| Survey | surveys / survey_versions / questions | T | — | CFG | versions immutable once published |
| Campaign | campaigns / invitations / invitation_tokens | T | B | PII | tokens expiring (7d), hard-to-guess |
| Feedback | feedback / survey_responses | T | B | PII | free-text = untrusted; configurable retention |
| Feedback | ai_analyses | T | B | AIX | derived; redacted; linked to run |
| Recovery | recovery_tickets / ticket_assignments | T | B | PII | case notes internal; redact before AI |
| Recovery | sla_timers / escalations | T | B | CFG | operational |
| Reputation | google_connections | T | B | CRED | OAuth tokens encrypted; refresh not plaintext |
| Reputation | google_locations / reviews | T | B | PUB | public source data |
| Reputation | reply_drafts / replies | T | B | PUB | human-approved before publish; state-tracked |
| Knowledge | knowledge_docs / knowledge_chunks / retrieval_index | T | B | KB | **no secrets/PII/medical**; tenant-scoped |
| AI | agent_runs / agent_steps / tool_calls | T | B | AIX | redacted; correlation id |
| AI | ai_costs | T | B | AIX | metering source for Billing |
| AI | prompt_versions / model_versions | T | — | CFG | versioned; auditable |
| AI | guardrail_events | T | B | AUD | security-relevant |
| Notification | notifications / notification_deliveries | T | B | PII | contact; honors opt-out |
| Integration | integrations / webhook_endpoints | T | scoped | CRED | signing secrets encrypted |
| Integration | webhook_events | T | scoped | PII(meta) | signature verified; replay-protected |
| Integration | outbox / dead_letters | T | scoped | mixed | tenant context; PII minimized; replayable |
| Analytics | report_read_models / dashboard_snapshots | T | B | mixed(agg) | derived projections; no raw medical |
| Analytics | exports | T | B | PII | tenant-scoped; audited; expiring links |
| Audit | audit_logs / security_events | T | scoped | AUD | append-only; non-deletable; retention configurable |
| Audit | data_exports / data_deletion_requests | T | scoped | AUD | tenant right-to-export/delete |

## Ownership rules
- One writer per table (fitness function `FF-DATA-01`): a module's migrations create only its own tables.
- Cross-module reads use the owner's query/contract or an event projection — never a foreign `JOIN`-write.
- `MED` (diagnosis, clinical notes, medical record number, prescription/medication, odontogram, clinical
  imagery, treatment-plan/history, insurance/PAN/bank) **MUST NOT** be stored in any table, sent to AI, or
  placed in public replies (`.claude/rules/18`; [PILOT_DATA_BOUNDARY](../security/PILOT_DATA_BOUNDARY.md)).
- Foreign keys, unique constraints, and indexes include `tenant_id` to prevent cross-tenant collision (ADR 0011).
- Migration governance and expand/contract safety: ADR 0014.
