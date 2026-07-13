# Module Boundaries — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §34–§36 · **Rules:** `.claude/rules/03`, `07`, `08`, `20` · **ADR:** [0010](../decisions/adr/0010-repository-layout-and-module-boundaries.md).

Seventeen internally-bounded modules. Each owns its data and exposes only a public interface (contracts,
application services, domain events). **No module mutates another module's tables directly.** See also
[Module Dependency Matrix](MODULE_DEPENDENCY_MATRIX.md) and [Data Ownership Matrix](DATA_OWNERSHIP_MATRIX.md).

## Boundary rules (enforceable)
- Cross-module change **MUST** use an application service, contract, or domain event — never a foreign table write.
- Every business record carries `tenant_id`; branch-relevant records carry `branch_id` (`.claude/rules/03`).
- The Shared Kernel stays minimal; it **MUST NOT** hold module business logic.
- No **undocumented** circular dependency; documented read-only dependencies are listed in the dependency matrix.
- Reporting/analytics **MUST NOT** be a tenant-isolation bypass (ADR 0015).

## Module summary
| # | Module | Purpose | Tenant | Branch | Key emitted events |
|---|--------|---------|--------|--------|--------------------|
| 1 | PlatformAdmin | Platform-level tenant/plan/admin operations | platform | — | `TenantProvisioned`, `PlanChanged` |
| 2 | Identity | Users, roles, permissions, MFA, sessions | yes | scoped | `UserInvited`, `RoleAssigned` |
| 3 | Tenancy | Tenant + branch org hierarchy, settings | yes | yes | `BranchCreated`, `TenantSettingsUpdated` |
| 4 | Billing | Subscription, entitlement, usage metering, invoices | yes | — | `UsageMetered`, `EntitlementChanged` |
| 5 | Customer | Customer profiles + consent/opt-out | yes | yes | `CustomerConsentUpdated`, `CustomerOptedOut` |
| 6 | ServiceEvent | Transactions / service events (e.g. `VisitCompleted`) | yes | yes | `TransactionCompleted`, `VisitCompleted` |
| 7 | Survey | Survey definition + versioning (CSAT/NPS/CES) | yes | — | `SurveyPublished`, `SurveyVersioned` |
| 8 | Campaign | Campaigns + invitation eligibility/dispatch | yes | yes | `InvitationScheduled`, `InvitationSent`, `InvitationExpired` |
| 9 | Feedback | Feedback inbox, AI analysis linkage, triage | yes | yes | `SurveyResponseSubmitted`, `FeedbackAnalyzed`, `HighRiskFeedbackDetected` |
| 10 | Recovery | Recovery tickets, SLA, assignment, escalation | yes | yes | `RecoveryTicketOpened`, `RecoveryTicketEscalated`, `SLABreached` |
| 11 | Reputation | Google connection/mapping/sync, reply draft→approval→publish | yes | yes | `GoogleReviewIngested`, `ReplyApproved`, `ReplyPublished`, `ReplyPublicationFailed` |
| 12 | Knowledge | Tenant knowledge base + RAG source of truth | yes | yes | `KnowledgeDocIndexed` |
| 13 | AI | Provider abstraction, guardrails, agent runs, tool-call/cost/trace | yes | yes | `AgentRunCompleted`, `AgentRunFailed`, `GuardrailBlocked` |
| 14 | Notification | Channel-agnostic notifications (in-app, email, WhatsApp gateway) | yes | yes | `NotificationQueued`, `NotificationDelivered`, `NotificationFailed` |
| 15 | Integration | Outbound/inbound integration gateway (webhooks, adapters, outbox) | yes | scoped | `WebhookReceived`, `OutboxDispatched`, `OutboxDeadLettered` |
| 16 | Analytics | Read-model reporting, dashboards, exports | yes | yes | `ExportRequested`, `ExportCompleted` |
| 17 | Audit | Audit log, compliance, data export/deletion requests | yes | scoped | `AuditRecorded`, `DataDeletionRequested`, `DataExportCompleted` |

## Per-module contract (fields required for each)
Each module block below records: **Owned data/tables**, **Public services (commands/queries)**, **Consumes**,
**Allowed deps**, **Forbidden deps**, **Data classification**, **Audit**, **Manual fallback**, **Failure states**,
**Security boundary**. Full table ownership is in [Data Ownership Matrix](DATA_OWNERSHIP_MATRIX.md).

### 1. PlatformAdmin
Owned: `tenants`, `plans`, `platform_admins`. Services: `ProvisionTenant`, `ChangePlan`, `SuspendTenant`;
`GetTenantOverview`. Consumes: Billing entitlement events. Allowed deps: Identity, Billing (contracts).
Forbidden: reading tenant business tables (Feedback/Recovery/Reputation). Classification: config + PII (admin).
Audit: every provision/suspend/plan change. Manual fallback: admin console independent of AI. Failure states:
`Provision pending/failed`. Boundary: platform scope only; cannot read tenant customer content.

### 2. Identity
Owned: `users`, `roles`, `permissions`, `role_user`, `sessions`, `mfa_factors`. Services: `InviteUser`,
`AssignRole`, `EnableMfa`; `Authorize(user,ability,scope)`. Consumes: Tenancy branch events. Allowed deps:
Tenancy (contract). Forbidden: business modules. Classification: PII. Audit: role/permission/MFA changes.
Manual fallback: login + RBAC do not depend on AI. Failure states: `Auth failed`, `MFA required`. Boundary:
enforces tenant + branch scope for all callers (ADR 0013).

### 3. Tenancy
Owned: `branches`, `tenant_settings`, `org_units`. Services: `CreateBranch`, `UpdateSettings`;
`ResolveTenantContext`, `ListBranches`. Consumes: PlatformAdmin provisioning. Allowed deps: none upward.
Forbidden: business modules. Classification: config. Audit: settings/branch changes. Manual fallback: full.
Failure states: n/a runtime. Boundary: source of tenant/branch context for the whole app (ADR 0012).

### 4. Billing
Owned: `subscriptions`, `entitlements`, `usage_records`, `invoices`. Services: `MeterUsage` (idempotent),
`CheckEntitlement`, `IssueInvoice`. Consumes: usage-producing events from AI/Campaign/Analytics. Allowed deps:
Tenancy. Forbidden: customer content. Classification: financial (tenant billing), not customer PII. Audit:
every metering + plan change; reconcilable (Master Source §46). Manual fallback: metering queue-based, retry-safe.
Failure states: `Metering deferred`, `Invoice draft/failed`. Boundary: no customer medical/PII.

### 5. Customer
Owned: `customers`, `customer_consents`, `opt_outs`. Services: `UpsertCustomer`, `RecordConsent`, `OptOut`;
`GetConsentState`. Consumes: ServiceEvent. Allowed deps: Tenancy. Forbidden: Reputation/AI table writes.
Classification: PII (minimized); **no medical data**. Audit: consent/opt-out changes. Manual fallback: full.
Failure states: n/a. Boundary: consent gate for Campaign; opt-out is honored everywhere (`.claude/rules/17`).

### 6. ServiceEvent
Owned: `service_events`, `transactions`. Services: `RecordServiceEvent`; `ListEvents`. Consumes: Integration
(DaengtisiaMS `VisitCompleted`). Allowed deps: Customer, Tenancy. Forbidden: Survey/Campaign internals.
Classification: transaction metadata (no clinical detail). Audit: ingestion source truthfully labelled.
Manual fallback: manual/CSV import shown as non-real-time. Failure states: `Import pending/failed`. Boundary:
triggers Campaign via event, not direct call.

### 7. Survey
Owned: `surveys`, `survey_versions`, `questions`. Services: `DefineSurvey`, `PublishVersion`; `GetActiveSurvey`.
Consumes: none. Allowed deps: Tenancy. Forbidden: response storage (owned by Feedback). Classification: config.
Audit: version publish. Manual fallback: full. Failure states: n/a. Boundary: immutable published versions.

### 8. Campaign
Owned: `campaigns`, `invitations`, `invitation_tokens`. Services: `ScheduleInvitations`, `SendInvitation`
(idempotent), `ExpireInvitations`; `GetInvitation`. Consumes: `VisitCompleted`, consent state. Allowed deps:
Customer (consent), Survey, Notification, Tenancy. Forbidden: bypassing opt-out/frequency cap. Classification:
PII (contact). Audit: every send/reminder. Manual fallback: QR/link works without AI. Failure states:
`Scheduled/Sent/Delivered/Failed/Expired`; frequency cap 1/14 days; window 09:00–20:00 (`.claude/rules/17`).
Boundary: **no review gating**; equal access.

### 9. Feedback
Owned: `feedback`, `survey_responses`, `ai_analyses`. Services: `SubmitResponse`, `RequestAnalysis`, `Triage`;
`GetInbox`. Consumes: `SurveyResponseSubmitted`. Allowed deps: Survey, AI (contract), Recovery (event), Tenancy.
Forbidden: publishing public content. Classification: PII + free-text (untrusted). Audit: analysis + triage.
Manual fallback: manual classification/triage without AI (UC-P0-16). Failure states: `Awaiting analysis`,
`Analysis failed → manual`. Boundary: customer content is untrusted input (ADR 0019).

### 10. Recovery
Owned: `recovery_tickets`, `ticket_assignments`, `sla_timers`, `escalations`. Services: `OpenTicket`, `Assign`,
`Escalate`, `Resolve`; `GetTicket`. Consumes: `HighRiskFeedbackDetected`. Allowed deps: Feedback (read),
Notification, Identity, Tenancy. Forbidden: public replies (Reputation owns). Classification: PII + case notes
(internal, redacted before AI). Audit: assignment/SLA/escalation. Manual fallback: full ticketing without AI.
Failure states: `Open/Assigned/Escalated/Resolved`, `SLA breached`. Boundary: compensation/refund needs approval.

### 11. Reputation
Owned: `google_connections`, `google_locations`, `reviews`, `reply_drafts`, `replies`. Services:
`ConnectGoogle`, `SyncReviews`, `DraftReply`, `SubmitForApproval`, `Publish` (idempotent, provider-verified);
`GetReviews`. Consumes: `GoogleReviewIngested`. Allowed deps: AI (draft), Identity (approver), Integration,
Tenancy. Forbidden: publishing without recorded human approval; disclosing PII/medical. Classification:
public content + OAuth credentials (encrypted). Audit: every draft/approval/publish. Manual fallback: manual
reply drafting/approval. Failure states: reply-state vocabulary (`no draft → … → published / publication failed
/ policy issue`). Boundary: **no gating**, human-approved, truthful states (`.claude/rules/06`, `18`).

### 12. Knowledge
Owned: `knowledge_docs`, `knowledge_chunks`, `retrieval_index`. Services: `IndexDoc`, `Retrieve`
(tenant/branch-filtered, minimal context). Consumes: none. Allowed deps: Tenancy. Forbidden: cross-tenant
retrieval; indexing secrets/PII/medical. Classification: tenant KB. Audit: index changes. Manual fallback: n/a.
Failure states: `Indexing pending/failed`. Boundary: RAG sends only minimum tenant-scoped context (ADR 0023).

### 13. AI
Owned: `agent_runs`, `agent_steps`, `tool_calls`, `ai_costs`, `prompt_versions`, `model_versions`,
`guardrail_events`. Services: `RunAgent` (structured output, timeout, retry, kill switch), `Redact`,
`Guardrail`. Consumes: analysis/draft requests. Allowed deps: Knowledge (retrieval), Integration (provider).
Forbidden: determining tool calls from customer content; sending prohibited data. Classification: prompts/traces
(redacted). Audit: run/step/tool-call/cost/prompt+model version. Manual fallback: every AI step has a manual
path. Failure states: `Queued/Running/Succeeded/Failed/Killed/Guardrail-blocked`. Boundary: supervisor +
specialist agents; human approval upstream of public action (ADR 0019, 0028).

### 14. Notification
Owned: `notifications`, `notification_deliveries`. Services: `Queue`, `MarkDelivered`; `GetStatus`. Consumes:
domain events needing notice. Allowed deps: Integration (channels), Tenancy. Forbidden: sending customer PII to
public channels. Classification: PII (contact). Audit: delivery. Manual fallback: in-app works without external
channel. Failure states: `Queued/Delivered/Failed`. Boundary: tenant-scoped; honors opt-out.

### 15. Integration
Owned: `integrations`, `webhook_endpoints`, `webhook_events`, `outbox`, `dead_letters`. Services:
`ReceiveWebhook` (signed, replay-protected), `DispatchOutbox` (idempotent), `ReplayDeadLetter`. Consumes:
outbound events from any module via outbox. Allowed deps: Tenancy; provider adapters. Forbidden: executing
tool/behaviour from untrusted payload content. Classification: integration metadata + encrypted credentials.
Audit: every inbound/outbound + signature verification. Manual fallback: manual import; kill switch. Failure
states: webhook `Pending/Delivering/Delivered/Retry/Failed/DeadLettered/Cancelled`. Boundary: single choke
point for external side effects; provider-state verified before success (ADR 0016, 0017, 0021).

### 16. Analytics
Owned: `report_read_models`, `dashboard_snapshots`, `exports`. Services: `BuildReadModel`, `RequestExport`;
`GetDashboard`. Consumes: domain events (projections). Allowed deps: read-only projections of other modules
(explicit), Tenancy. Forbidden: cross-tenant aggregation; writing business tables. Classification: aggregated
(no raw medical). Audit: export requests. Manual fallback: dashboards degrade gracefully without AI. Failure
states: `Export pending/failed`. Boundary: tenant/branch scoping on every read model (ADR 0015).

### 17. Audit
Owned: `audit_logs`, `security_events`, `data_exports`, `data_deletion_requests`. Services: `Record` (append
only), `RequestExport`, `RequestDeletion`. Consumes: audit events from all modules. Allowed deps: Tenancy.
Forbidden: mutating/deleting audit history. Classification: audit (sensitive). Audit: itself immutable. Manual
fallback: n/a. Failure states: `Export/Deletion pending/completed/failed`. Boundary: append-only; tenant-scoped
views; retention configurable (`.claude/rules/07`, ADR 0029).
