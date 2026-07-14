# ADR 0061 — Feedback Search, Timeline, and Assignment Architecture

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 8 Feedback Operations Foundation; feedback capability IN PROGRESS toward GO
- **Owner:** Principal Architect / Application Security Engineer
- **Rule:** `.claude/rules/33`, `.claude/rules/03`, `.claude/rules/07` · **Canonical:** Master Source §47, §37; PRD v1.3.0 §10.7, §10.8 (FR-FBK-*); rules 33, 03, 07, 30

## Context
An operable inbox needs search, an assignment workflow, manual tags/notes, and a trustworthy history. Each of these
is a leakage or integrity risk: full-text search over feedback content can expose sensitive free text to a user who
may see the item but not its content; assignment can hand an item to a member outside its branch or to a member whose
tenant membership has been revoked; manual tags/notes can bleed across tenants; and a mutable history destroys
auditability. The failure modes to prevent: content search that ignores the content-view permission, assignment to an
out-of-scope or revoked member, cross-tenant tag/note reuse, and an editable/deletable timeline.

## Decision
- **Permission-aware search.** Search uses PostgreSQL full-text search (a stored `tsvector` column with a GIN index)
  with a portable `LIKE`/`ILIKE` fallback for SQLite/dev. Metadata search (status, tag, assignee, date) is available
  to any user who can view the feedback list; **content** search (matching free-text answer/comment text) is gated by
  the `feedback.view-content` permission and is silently excluded from the query for users without it — never
  returned and never used as a match source.
- **Append-only immutable timeline.** `FeedbackEvent` records every lifecycle transition, assignment change, tag
  change, note addition, and attachment change with actor + tenant. It is append-only (no `updated_at`; update/delete
  blocked at the model layer, consistent with ADR 0029); the history is never mutated or deleted, and its metadata is
  sanitized (no tokens, secrets, or free-text answer content).
- **Scope-validated assignment with append-only history.** Assignment sets `assigned_to` only to a member with an
  **active** tenant membership whose branch scope includes the item's branch; a branch-restricted user cannot be
  assigned another branch's item. Assignment changes append to the timeline and to an assignment history; a member
  whose membership is later revoked/suspended is treated as unassignable and their effective access fails closed
  (ADR 0053) — a revoked member cannot act on or be newly assigned an item.
- **Tenant-isolated manual tags and append-only notes.** `FeedbackTag` and item↔tag links are tenant-owned; a tag
  from one tenant can never be applied to another tenant's item. Internal notes (`FeedbackNote`) are append-only
  (no edit/delete at the model layer), tenant/branch-scoped, and their free text is treated as untrusted (escaped on
  output, never logged, never AI-fed in Step 8).
- **Placement.** Search/timeline/assignment code is platform-core in `App\Feedback\*` (not `app/Modules/`).

## Alternatives
- **Content search available to all list-viewers** — rejected: exposes sensitive free text to users authorized only
  for metadata; the `feedback.view-content` gate is mandatory.
- **A single external search engine (e.g. Elasticsearch) now** — rejected: adds an unscoped tenant-leak surface and an
  operational dependency; native Postgres FTS with a portable fallback is sufficient and stays inside the tenant
  boundary.
- **Editable status/history rows** — rejected: destroys the audit trail; an append-only timeline is the record of
  truth.
- **Assignment without scope/membership revalidation** — rejected: enables cross-branch handoff and lets a revoked
  member retain effective access.

## Consequences
Search never leaks content to an unauthorized viewer; assignment stays within branch scope and honors membership
revocation; tags and notes never cross tenants; and the feedback history is a permanent, sanitized, append-only
record.

## Impacts
- **Security:** content search gated by `feedback.view-content`; assignment revalidates active membership + branch
  scope; revoked/suspended members fail closed; timeline is tamper-evident append-only.
- **Privacy:** free-text answer/comment content is never in search results for unauthorized viewers, never in the
  timeline/audit metadata, and never logged.
- **Tenant isolation:** search, tags, notes, assignment, and timeline are all fail-closed tenant-scoped; branch scope
  enforced in policies and queries.
- **Database:** adds a `tsvector` + GIN index for content search, `feedback_events` (append-only), `feedback_tags` +
  pivot, `feedback_notes` (append-only), and assignment columns/history; no cross-tenant relationship.
- **Operational:** portable `LIKE` fallback keeps dev/SQLite working; the GIN index keeps Postgres search bounded.
- **Cost:** negligible; native database features, no external service.

## Verification / fitness function
`tests/Feature/Feedback/FeedbackSearchTest.php`, `FeedbackAssignmentTest.php`, `FeedbackTagNoteTest.php`,
`FeedbackTimelineTest.php`, `tests/Feature/Security/Sf08CrossTenantMatrixTest.php`,
`tests/Feature/Audit/Sf08AuditTest.php`, `tests/Architecture/Sf08BoundariesTest.php` assert content-search permission
gating, scope-validated assignment, membership-revocation handling, tenant-isolated tags/notes, and the append-only
immutable timeline. AFR-192..AFR-196, AFR-199, AFR-200, AFR-201.

## Related
Requirement: Master Source §47, §37; PRD v1.3.0 §10.7, §10.8. Rules: 33, 03, 07, 30. ADRs: 0012, 0013, 0029, 0053,
0060.

## Evidence
`app/Feedback/Search/*`, `app/Feedback/FeedbackAssignmentService.php`, `app/Feedback/FeedbackTimeline.php`,
`app/Models/{FeedbackEvent,FeedbackTag,FeedbackNote}.php`, `app/Policies/FeedbackItemPolicy.php`,
`database/migrations/2026_07_15_*`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-8/`.

## Non-claims
Does not implement AI classification, semantic/vector search, RAG, or an external search engine (all NOT STARTED);
does not expose free-text content to unauthorized viewers; does not claim deployment/pilot/production readiness.

## Rollback
Permission-gated content search, scope-validated assignment with membership-revocation fail-close, tenant-isolated
tags/notes, and the append-only immutable timeline are permanent; loosening any requires an owner-approved Master
Source update.
