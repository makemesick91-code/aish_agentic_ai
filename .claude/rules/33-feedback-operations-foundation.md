---
id: "33"
title: Feedback Operations Foundation
domain: feedback-operations
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §47, §62; §16, §37, §43, §50, §53, §54"
  - "PRD v1.3.0 §10.7, §10.8, §14, §16, §23, §24"
  - "ADRs 0060, 0061, 0062; ADRs 0011–0016, 0029, 0051–0057; AFR-188..210; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30, 31, 32"
supersede: "Permanent for Step 8+. Idempotent one-item-per-source projection, fail-closed tenant/branch feedback isolation, the explicit guarded lifecycle, scope-validated assignment with membership-revocation fail-close, tenant-isolated manual tags, append-only notes and immutable timeline, private content-MIME-validated attachments, permission-aware search, bounded bulk operations, queued requester-scoped secure export with CSV formula-injection protection, the single authoritative entitlement resolver, idempotent usage metering, the approved notification dispatcher, sanitized audit redaction, Google Review anti-gating, platform-role isolation, and evidence-based release cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees."
---

# Rule 33 — Feedback Operations Foundation

## Purpose
Keep the Step 8 substrate — feedback projection from source events, the feedback lifecycle, assignment, manual tags
and notes, attachments, the immutable timeline, permission-aware search, bulk operations, and secure export —
tenant-safe, fail-closed, truthful, auditable, privacy-aware, and free of cross-tenant/cross-branch leakage or
content exposure from a clean checkout onward, without weakening any security, privacy, review-policy, documentation,
or release gate.

## Scope
Feedback projection and reconciliation; feedback item ownership and lifecycle; assignment; manual tags; internal
notes; attachments; the append-only timeline and audit; permission-aware search and indexing; bulk operations;
secure export; feedback entitlement and usage metering; and feedback notifications. Applies to `app/Feedback/**`,
`app/Models/Feedback*`, `app/Http/Controllers/Tenancy/Feedback/**`, `app/Http/Requests/Feedback/**`,
`app/Http/Middleware/EnsureFeedbackEnabled.php`, `app/Policies/FeedbackItemPolicy.php`, `app/Jobs/Feedback/**`,
`app/Events/SurveyResponseCompleted.php`, `app/Feedback/Listeners/**`, related `database/` and
`tests/{Feature/Feedback,Feature/Security/Sf08*,Feature/Audit/Sf08*,Unit/Feedback,Architecture/Sf08*,Feature/Console}`.
This is platform-core in top-level `app/` namespaces (ADR 0060), not inside `app/Modules/`; other business modules
remain **NOT STARTED**.

## Rules

### Projection
- A feedback item **MUST** be created from a source event exactly once; projection **MUST** be idempotent, enforced by
  a database unique constraint `(tenant_id, source_type, source_id)`, so a replay, retry, or duplicate event resolves
  to the **same** item with no duplicate side effect.
- Projection **MUST** run off the source write path via an **after-commit** domain event
  (`SurveyResponseCompleted`) and a queued listener; a survey completion **MUST NOT** be blocked or partially undone
  by feedback creation.
- Reconciliation (`aish:feedback-reconcile`) **MUST** be idempotent and safe to rerun, back-filling missing items
  without creating a duplicate; it **MUST NOT** be a second uncontrolled write path.

### Ownership
- Every feedback-owned record **MUST** carry `tenant_id` (and `branch_id` where the source is branch-scoped); a
  branch-restricted user **MUST** see only their branch's feedback. Cross-tenant or cross-branch feedback access is a
  release blocker.
- Platform roles **MUST NOT** imply feedback access; a platform role grants no tenant feedback data.

### Source integrity
- A feedback item **MUST** faithfully reference its immutable source (the answered survey version); it **MUST NOT**
  copy or expose free-text answer content into logs or audit, and free text is untrusted (escaped on output, not
  AI-fed in Step 8).

### Lifecycle
- Lifecycle state **MUST** be explicit (`new → triaged → assigned → in_progress → resolved → closed → archived`) and
  changed only through a guarded transition; an invalid transition **MUST** be rejected and every transition
  **MUST** be recorded on the immutable timeline.
- `resolved`/`closed` are operational feedback states **only**; they **MUST NOT** be presented as a customer-recovery
  outcome, refund, compensation, or apology (recovery remains NOT STARTED).

### Assignment
- Assignment **MUST** target only a member with an **active** tenant membership whose branch scope includes the item's
  branch; a branch-restricted item **MUST NOT** be assigned outside its branch.
- A member whose membership is later revoked or suspended **MUST** fail closed — they **MUST NOT** act on or be newly
  assigned a feedback item; assignment changes **MUST** append to the timeline/history.

### Tags
- Manual tags **MUST** be tenant-owned; a tag from one tenant **MUST NOT** be applied to another tenant's item.

### Notes
- Internal notes **MUST** be append-only (no edit/delete at the model layer), tenant/branch-scoped, and their free
  text **MUST** be treated as untrusted (escaped on output, never logged, not AI-fed in Step 8).

### Attachments
- Attachments **MUST** be stored on a **private** tenant-prefixed disk (`tenants/{id}/feedback/{item_id}/...`); there
  **MUST NOT** be a public disk or public listing, and user-supplied names **MUST NOT** be used as a path segment
  (path traversal prevented).
- Attachment MIME **MUST** be validated by content inspection against an allowlist (not by extension or the client
  `Content-Type`); a disallowed type **MUST** be refused. Removal **MUST** be a recorded remove-state, not a silent
  hard delete.

### Timeline & audit
- The timeline (`FeedbackEvent`) and audit **MUST** be append-only (no `updated_at`; update/delete blocked at the
  model layer) and **MUST NOT** be deletable; audit/timeline metadata **MUST** be sanitized and **MUST NOT** contain
  tokens, secrets, passwords, or free-text answer/customer/medical content, and **MUST** carry actor + tenant.

### Search
- Metadata search (status, tag, assignee, date) **MAY** be available to any feedback list-viewer; **content** search
  over free text **MUST** be gated by the `feedback.view-content` permission and **MUST** be excluded from the query
  (never returned, never a match source) for users without it.
- Search indexing **MUST** stay inside the tenant boundary (native PostgreSQL FTS `tsvector`/GIN with a portable
  `LIKE` fallback); it **MUST NOT** introduce an unscoped external index that could leak across tenants.

### Bulk operations
- Bulk operations (bulk status change, assignment, tagging) **MUST** be bounded (a hard item cap), **MUST**
  re-authorize the specific per-action permission for every item, **MUST** stay within tenant/branch scope, and
  **MUST** record each change on the timeline.

### Export
- Export **MUST** be a queued job writing to a **private, expiring** location; it **MUST** be entitlement-gated via
  the single authoritative resolver and metered as tenant-scoped idempotent usage (a retry **MUST NOT** double-count).
- The download **MUST** re-authorize the **requesting** user (ownership of the export record) and re-check
  tenant/branch/content scope; another user **MUST NOT** fetch someone else's export and a link **MUST NOT** be
  public.
- Every exported cell beginning with `=`, `+`, `-`, `@`, tab, or CR **MUST** be neutralized against CSV
  formula-injection; export fields **MUST** be minimized (no secret/token/unrelated-tenant data).

### Entitlement & usage
- Base feedback access **MUST** be entitlement-gated (`EnsureFeedbackEnabled`); an unknown/ungranted key **MUST** fail
  closed; a commercial state **MUST NOT** override a security suspension.
- Feedback usage meters **MUST** be tenant-scoped and idempotent; a failed/duplicate operation **MUST NOT**
  double-count.

### AI/Recovery boundary
- Step 8 **MUST NOT** perform AI sentiment/topic/severity/summary, customer recovery, SLA, agent orchestration, or
  RAG; feedback free text **MUST NOT** be sent to an AI provider in Step 8 (rules 05, 18).

### Review-policy preserved
- A feedback state or score **MUST NEVER** determine whether Google Review access is shown; review gating remains
  **prohibited**; all eligible customers **MUST** retain equal review access in future implementation (rules 06, 18).

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These remain binding but are scheduled later; Step 8 **MUST NOT** be read as delivering them: AI sentiment/topic/
severity/summary and insight; customer recovery, recovery tickets, and SLA; Google OAuth, review sync, and reply;
agent orchestration and RAG; WhatsApp/SMS/push notification channels and a production email provider; billing/payment;
advanced analytics/benchmarking/predictive insight; and deployment.

## Required checks
- `tests/Feature/Feedback/*`, `tests/Unit/Feedback/*`, `tests/Feature/Security/Sf08CrossTenantMatrixTest.php`,
  `tests/Feature/Audit/Sf08AuditTest.php`, `tests/Feature/Sf08MigrationIntegrityTest.php`,
  `tests/Feature/Console/Sf08CommandsTest.php`, `tests/Architecture/{Sf08BoundariesTest,TenancyBoundariesTest}.php`;
  the consolidated Step-8-style GO/WATCH/NO-GO gate; a clean-checkout Step 8 verification on the merged SHA
  (`scripts/runtime/verify-step-8.sh` / `php artisan aish:verify-step-8`); `scripts/docs/secret-scan.sh`; the
  `backend-runtime-ci` gate (rules 28, 29).

## Evidence
- `app/Feedback/**`, `app/Models/Feedback*`, `app/Http/Controllers/Tenancy/Feedback/**`,
  `app/Http/Requests/Feedback/**`, `app/Http/Middleware/EnsureFeedbackEnabled.php`, `app/Policies/FeedbackItemPolicy.php`,
  `app/Jobs/Feedback/**`, `app/Events/SurveyResponseCompleted.php`, `app/Feedback/Listeners/**`;
  `tests/**/Sf08*`, `tests/Feature/Feedback/**`, `tests/Unit/Feedback/**`;
  `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-8/`.

## Related canonical sections
- Master Source §47, §62; §16, §37, §43, §50, §53, §54; PRD v1.3.0 §10.7, §10.8, §14, §16, §23, §24; ADRs 0060–0062;
  ADRs 0011–0016, 0029, 0051–0057; AFR-188..210; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30,
  31, 32.

## Supersession
Permanent for Step 8+. Idempotent one-item-per-source projection, fail-closed tenant/branch feedback isolation, the
explicit guarded lifecycle, scope-validated assignment with membership-revocation fail-close, tenant-isolated manual
tags, append-only notes and immutable timeline, private content-MIME-validated attachments with no public disk,
permission-aware search, bounded bulk operations, queued requester-scoped secure export with CSV formula-injection
protection, the single authoritative entitlement resolver, idempotent usage metering, the approved notification
dispatcher, sanitized audit redaction, Google Review anti-gating, platform-role isolation, and evidence-based release
are permanent; superseded only by a higher-version Master Source update that preserves these guarantees.
