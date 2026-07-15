# Step 9 — Real Codebase Capability Inventory

**Status:** GOVERNANCE BASELINE — repository-evidence-based
**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Verified against:** canonical branch `main`, Step 8 merge SHA `6792db5` present in history; baseline green
(hermetic suite 354 passing, Pint clean, PHPStan clean, documentation gates all pass)
**Related:** `docs/product/competitive/STEP_9_COMPETITOR_CAPABILITY_MATRIX.md`,
`docs/product/competitive/STEP_9_COMPETITIVE_GAP_REGISTER.md`, `docs/architecture/experience-os/`, rule 34
**Canonical repo:** makemesick91-code/aish_agentic_ai

---

## 1. Purpose and method

This inventory classifies what actually exists in the repository today — not what the roadmap promises. Each row
carries repository evidence (file path, model, migration, command, rule, ADR, or test). A capability is marked
`IMPLEMENTED` only when working code plus tests exist; a roadmap statement alone is never sufficient.

**Classification vocabulary:** `IMPLEMENTED`, `PARTIAL`, `FOUNDATION ONLY`, `DOCUMENTED-NOT-IMPLEMENTED`,
`MISSING`, `DEFERRED`, `SUPERSEDED`.

**Baseline facts:** Laravel 12 modular monolith; PHP 8.4 (min `^8.3`); PostgreSQL 17; Redis 7; shared-schema
row-level multi-tenancy. 39 migrations under `database/migrations/`. Platform-core lives in top-level `app/`
namespaces (`app/Tenancy`, `app/Audit`, `app/Surveys`, `app/Feedback`, `app/Subscriptions`, `app/Platform`,
`app/Services/Notifications`); business modules under `app/Modules/` remain **NOT STARTED**.

---

## 2. Platform-core capabilities (Steps 5–8) — IMPLEMENTED foundation

| # | Capability | Status | Evidence (repository) | Source of truth | Tenant/branch scope | Permission | Audit | Queue/failure | Known limitations |
|---|-----------|--------|-----------------------|-----------------|---------------------|-----------|-------|---------------|-------------------|
| C-01 | Runtime bootstrap, health/readiness, queue+scheduler | IMPLEMENTED | `scripts/runtime/bootstrap-local.sh`, `verify-runtime.sh`; `/live` + `/ready` routes; ADRs 0047–0050; rule 29 | Application runtime | n/a | n/a | Queue foundation, failed-job path | Foundation only — no business jobs |
| C-02 | Global user identity + authentication | IMPLEMENTED | `app/Models/User.php` (no `tenant_id`); Fortify/Sanctum; registration disabled; ADR 0053; rule 30 | Identity & Access | Global identity | n/a | Auth events audited | No self-service registration (by design) |
| C-03 | Tenant & branch lifecycle | IMPLEMENTED | `app/Models/Tenant.php`, `app/Models/Branch.php`, `app/Models/BranchAccessGrant.php` | Tenant & Branch | Row-level `tenant_id`/`branch_id` | Platform/tenant split | Yes (`AuditLog`) | — |
| C-04 | Membership + invitation | IMPLEMENTED | `app/Models/TenantMembership.php` (invited/active/suspended/revoked), `app/Models/TenantInvitation.php` (hashed one-time), last-active-owner protection | Tenant & Branch | Tenant-scoped | Membership-gated | Yes | — |
| C-05 | Immutable fail-closed tenant context | IMPLEMENTED | `app/Tenancy/` (immutable, request/job-scoped, no silent fallback); ADR 0052; rule 30 | Tenant context | Enforced everywhere | n/a | n/a | Cleared between requests/jobs | — |
| C-06 | Tenant-scoped RBAC + policies | IMPLEMENTED | Spatie `laravel-permission` teams on `tenant_id`; `app/Policies/`, `app/Authorization/`; ADR 0013 | Identity & Access | Tenant-scoped | Defense-in-depth (policy + service) | Role/permission changes audited | — |
| C-07 | Append-only audit | IMPLEMENTED | `app/Models/AuditLog.php`, `app/Audit/` (no `updated_at`; update/delete blocked) | Audit/Security | Tenant + platform context | n/a | Immutable | — |
| C-08 | Tenant isolation (DB/cache/queue/storage/log) | IMPLEMENTED | `app/Tenancy/`, `app/Support/`; tests `tests/Feature/Tenancy/*`; rule 03/30 | Cross-cutting | All surfaces | n/a | Queue carries tenant envelope | — |
| C-09 | Notification delivery foundation | IMPLEMENTED | `app/Services/Notifications/`, `app/Models/NotificationDelivery.php` (truthful states, per-(recipient,channel) dedup, bounded retry), `app/Models/NotificationPreference.php` (quiet hours, critical bypass); `app/Mail/`; ADR 0054; rule 31 | Notification | Tenant-scoped, membership-verified | Ownership-checked inbox | Sanitized | In-app + email only | No WhatsApp/SMS/push/webhook channel |
| C-10 | Subscription + entitlement + usage metering | IMPLEMENTED (commercial-only) | `app/Models/Plan.php`, `PlanFeature.php`, `TenantSubscription.php`, `SubscriptionEvent.php`, `UsageRecord.php`; `app/Subscriptions/` (single fail-closed resolver); `aish:subscription-reconcile`; ADR 0055; rule 31 | Subscription/Entitlement/Usage | Tenant-scoped, idempotent | Entitlement-gated | Append-only events | **No payment/invoicing/tax/dunning** (out of scope) |
| C-11 | Platform-admin plane | IMPLEMENTED | `app/Platform/`, `app/Models/PlatformRoleAssignment.php`, `PlatformSupportNote.php`; `aish:platform-admin-provision`; ADR 0056; rule 31 | Platform Administration | Platform plane (no tenant bypass) | Per-permission, least privilege | Reason-required, audited | **Impersonation prohibited** |
| C-12 | Survey authoring + immutable versioning | IMPLEMENTED | `app/Models/Survey.php`, `SurveyVersion.php`, `SurveyQuestion.php`, `SurveyOption.php`, `SurveyCampaign.php`; `app/Surveys/`; ADR 0057; rule 32 | Survey & Campaign | Tenant/branch-owned | Authoring-gated | Sanitized | — |
| C-13 | Secure public invitation / link / QR | IMPLEMENTED | `app/Models/SurveyInvitation.php` (SHA-256 hashed one-time tokens, no-enumeration gateway, per-token+IP rate limit), URL-only QR (bacon/qr-code); ADR 0058; rule 32 | Survey distribution | Tenant/branch-scoped | Public no-RBAC gateway | Token never logged | — |
| C-14 | Tokenized public responses | IMPLEMENTED | `app/Models/SurveyResponse.php`, `SurveyAnswer.php` (one-time, write-once, transactional, idempotent) | Survey & Campaign | Tenant/branch-scoped | Public token | Sanitized | Anonymous by default; no customer identity created |
| C-15 | Deterministic CSAT / NPS / CES | IMPLEMENTED | single MetricCalculator in `app/Surveys/` (deterministic, explicit rounding, null-on-empty); ADR 0059; rule 32 | Survey scoring | Tenant/branch/version scoped | Read-gated | n/a | No cross-tenant aggregation |
| C-16 | Feedback projection (idempotent) | IMPLEMENTED | `app/Feedback/FeedbackProjector.php`, `app/Events/SurveyResponseCompleted.php`, `app/Feedback/Listeners/ProjectFeedbackOnSurveyResponseCompleted.php`; DB unique `(tenant_id, source_type, source_id)`; `aish:feedback-reconcile`; ADR 0060; rule 33 | Feedback Operations | Tenant/branch-scoped | n/a | Timeline event | After-commit queued; replay-safe | — |
| C-17 | Feedback lifecycle | IMPLEMENTED | `app/Feedback/FeedbackLifecycle.php` (`new→triaged→assigned→in_progress→resolved→closed→archived`, guarded transitions) | Feedback Operations | Tenant/branch-scoped | Permission-gated | Every transition on timeline | Invalid transitions rejected | `resolved`/`closed` are NOT recovery outcomes |
| C-18 | Assignment (scope-validated) | IMPLEMENTED | `app/Feedback/FeedbackAssignmentService.php` (active membership + branch scope; revocation fail-close); `app/Models/FeedbackAssignment.php` | Feedback Operations | Tenant/branch-scoped | Membership-verified | Timeline | — | — |
| C-19 | Manual tags | IMPLEMENTED | `app/Feedback/FeedbackTagService.php`, `app/Models/FeedbackTag.php`, `FeedbackItemTag.php` (tenant-owned) | Feedback Operations | Tenant-scoped | Permission-gated | Yes | No AI auto-tagging |
| C-20 | Append-only notes + immutable timeline | IMPLEMENTED | `app/Feedback/FeedbackNoteService.php`, `FeedbackTimeline.php`; `app/Models/FeedbackNote.php`, `FeedbackEvent.php` (no `updated_at`; update/delete blocked) | Feedback Operations | Tenant/branch-scoped | Permission-gated | Immutable | Free text not AI-fed |
| C-21 | Private attachments (content-MIME) | IMPLEMENTED | `app/Feedback/FeedbackAttachmentService.php`, `app/Models/FeedbackAttachment.php` (private tenant-prefixed disk; content-based MIME allowlist; no public disk; no path traversal) | Feedback Operations | Tenant/branch-scoped | Permission-gated | Remove-state recorded | — |
| C-22 | Permission-aware search | IMPLEMENTED | `app/Feedback/Search/FeedbackSearchService.php`, `FeedbackSearchCriteria.php` (PostgreSQL FTS + LIKE fallback; content search gated by `feedback.view-content`) | Feedback Operations | Tenant-scoped index | Content-permission-gated | n/a | No external search index |
| C-23 | Bounded bulk operations | IMPLEMENTED | `app/Feedback/Bulk/FeedbackBulkService.php`, `BulkResult.php` (hard item cap; per-action re-authorization; timeline per change) | Feedback Operations | Tenant/branch-scoped | Per-action | Timeline | — | — |
| C-24 | Queued secure CSV export | IMPLEMENTED | `app/Feedback/Export/FeedbackExportService.php`, `FeedbackExportWriter.php`; `app/Models/FeedbackExport.php` (queued, private+expiring, requester-scoped re-auth download, CSV formula-injection guard, entitlement-gated + metered); ADR 0062; rule 33 | Feedback Operations | Tenant/branch-scoped | Requester-scoped | Yes | — | — |
| C-25 | Feedback entitlement gate | IMPLEMENTED | `app/Http/Middleware/EnsureFeedbackEnabled.php`, `app/Feedback/FeedbackEntitlements.php` (fail-closed via single resolver) | Feedback Operations | Tenant-scoped | Entitlement-gated | n/a | — | — |

---

## 3. Experience OS target capabilities — PARTIAL / MISSING / DEFERRED

| # | Capability | Status | Evidence / related foundation | Notes |
|---|-----------|--------|-------------------------------|-------|
| G-01 | CRM service / **Customer 360** / unified identity resolution | MISSING | No `Customer` model; feedback references source, not a unified customer. Global user identity + survey/feedback sources exist as inputs. | **Next: Step 10.** Design: `docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md` |
| G-02 | Transaction / service-event ingestion | MISSING | Survey `VisitCompleted` / DaengtisiaMS contract is planning-only (`docs/integrations/`). | Enables recovery + 360. Wave 1 |
| G-03 | Customer Recovery OS (tickets, SLA, escalation, playbooks) | MISSING | Feedback assignment/lifecycle (`app/Feedback/`) is the nearest foundation; recovery is a distinct domain. | Wave 1. Canonical event names reserved (`RecoveryTicket*`, `SLABreached`) |
| G-04 | AI sentiment / topic / severity / summary | MISSING | Governance rules 05/18 binding; no AI runtime. Feedback free text is deliberately NOT AI-fed today. | Wave 1 (basic AI). MED data excluded from AI |
| G-05 | Google Review & reputation (OAuth, sync, AI reply, approval, publish) | DOCUMENTED-NOT-IMPLEMENTED | Policy `docs/integrations/google/GOOGLE_REVIEW_POLICY.md`; anti-gating rules 06/18; no integration/credentials. | Wave 1. Human approval mandatory; anti-gating permanent |
| G-06 | Omnichannel inbox & conversations (WhatsApp/email/web/social/SMS/voice) | MISSING | Notification foundation (SF-05) is outbound-only, in-app+email. No inbound/conversation model. | Wave 2. Design: `docs/architecture/experience-os/CHANNEL_ADAPTER_ARCHITECTURE.md` |
| G-07 | AI copilot / human handover / QA | MISSING | Depends on basic AI + AI tool-permission design. | Wave 2. Design: `docs/architecture/experience-os/AI_TOOL_PERMISSION_AND_APPROVAL_ARCHITECTURE.md` |
| G-08 | Knowledge base & policy controls + RAG | MISSING | Data model reserved (Master Source §36); tenant/branch-scoped retrieval required (rules 05/07). | Wave 2 |
| G-09 | Workflow automation & controlled tool actions / Agent Studio | MISSING | Autonomy only after manual/semi-automated stable (rules 02/05). | Wave 3 |
| G-10 | Analytics / branch intelligence / ROI / outcome ledger | MISSING | Depends on Experience Event Ledger design (`docs/architecture/experience-os/EXPERIENCE_EVENT_LEDGER.md`). | Wave 1/2 |
| G-11 | Public API / webhooks / integration marketplace | PARTIAL/MISSING | Internal event/outbox patterns (ADRs 0016/0017) exist; no public API surface or webhooks. | Wave 3 |
| G-12 | Payment / billing / invoicing / tax / dunning | DEFERRED | Explicitly out of MVP scope (Master Source §48); subscription is commercial-not-payment. | Deferred by decision |
| G-13 | Experience Event Ledger (cross-domain append-only stream) | MISSING | Step 8 Feedback Timeline (`app/Models/FeedbackEvent.php`) is the nearest existing append-only stream and is PRESERVED, not replaced. | Wave 1 enabler |

---

## 4. Cross-cutting invariants already enforced (must be preserved by all future work)

- Fail-closed immutable tenant context; membership-only tenant access; no cross-tenant read/write (rule 03/30).
- Registration disabled; secure provisioning + invitation only.
- Append-only audit and append-only feedback timeline (no update/delete at model layer).
- Single authoritative entitlement resolver; fail-closed on unknown key; **security suspension precedence** over
  commercial state.
- Truthful delivery/UI states; no success shown before verification.
- Google Review **anti-gating** and human-approval-for-public-reply are permanent (rules 06/18) — not yet exercised
  because the integration is not implemented, but binding on all future work.
- Impersonation prohibited (rule 31).
- Secrets never committed; encryption-at-rest for credentials/tokens (rules 04/24).

---

## 5. Truthful status summary

Implemented today: a secure multi-tenant SaaS core (identity, tenant/branch, RBAC, audit, isolation), a
notification/subscription/platform-admin substrate, immutable-versioned surveys with deterministic CSAT/NPS/CES, and
an operable feedback-operations inbox. **Everything customer-facing beyond survey + feedback — Customer 360, recovery,
Google Review, AI, omnichannel, knowledge base, analytics, public API, and payment — is NOT yet implemented.**
Deployment, pilot readiness, pilot runtime, and production readiness remain **NOT STARTED**. No domain is owned;
nothing is deployed.
