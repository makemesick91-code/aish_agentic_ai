# Pilot Use Case Catalog — Aish Agentic AI

**Document:** Pilot Use Case Catalog (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §9 (use cases), with severity/SLA from §10, data boundary
from §8, Google policy from §12, and metrics from §14. Every P0 case below carries an owner (actor),
acceptance criteria, and an evidence requirement — no critical use case is left without them (Persona §19).
This document describes intended behaviour; it makes no claim that any of it is implemented or runs.

---

## 1. Conventions

- **Truthful states** use the Master Source §53 vocabulary; the full state machines live in
  `PILOT_WORKFLOW_STATES.md`.
- **Tenant scope / branch scope** MUST be enforced on every surface (`.claude/rules/03`).
- **Audit events** MUST be immutable (`.claude/rules/07`).
- **Manual fallback** is mandatory: no basic function may depend on AI availability (`.claude/rules/05`).
- Evidence MUST be tenant-safe and MUST NOT store real customer PII in the repository (Persona §15).

---

## 2. P0 — Mandatory for pilot operation (Persona §9.1)

### UC-P0-01 — Onboarding Tenant and Pilot Branch

- **Actor / owner:** Pilot Coordinator.
- **Trigger:** Pilot preparation kickoff (Persona §13.1).
- **Preconditions:** Named roles assigned; canonical repo verified; owner-approved pilot scope.
- **Main flow:** Create tenant → create Daengtisia Pusat branch → set timezone Asia/Makassar → assign
  roles/permissions → configure pilot settings.
- **Alternate flow:** Owner substitutes an alternative branch via decision log if Pusat readiness fails
  (Persona §2 item 2), without changing product scope.
- **Failure flow:** Missing role or permission conflict blocks activation; configuration stays in draft
  and is not shown as live.
- **Minimum data:** `tenant_id`, `branch_id`, timezone, role assignments (Persona §8.1).
- **Permission:** Coordinator; MUST NOT grant excessive permission.
- **Tenant scope:** Single tenant. **Branch scope:** Single pilot branch.
- **Audit events:** tenant created, branch created, role assigned, setting changed.
- **External dependency:** None.
- **Truthful states:** configuration draft → configured (never "live" until verified).
- **Security & privacy:** No cross-tenant visibility; least privilege (`.claude/rules/03`, `04`).
- **Acceptance (Persona §9.1):** Tenant/branch scope passes, setup audited, no cross-tenant visibility.
- **Evidence:** Configuration snapshot + audit log of setup.
- **Manual fallback:** Fully manual configuration; no AI dependency.
- **Out of scope:** Multi-branch onboarding; production data edit.

### UC-P0-02 — Create and Publish Survey

- **Actor / owner:** Pilot Coordinator.
- **Trigger:** Approved survey design (Persona §6).
- **Preconditions:** UC-P0-01 complete; consent/privacy text approved.
- **Main flow:** Build versioned survey (CSAT 1–5, CES 1–5, NPS 0–10, optional comment, conditional
  problem category + follow-up consent) → preview → publish version.
- **Alternate flow:** Save as draft; iterate before publish.
- **Failure flow:** Missing mandatory question or consent text blocks publish.
- **Minimum data:** Survey definition, version, consent/privacy text.
- **Permission:** Coordinator publish right.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch / campaign.
- **Audit events:** survey drafted, survey published, version pinned.
- **External dependency:** None.
- **Truthful states:** draft → published (immutable version).
- **Security & privacy:** No 5-star solicitation; no medical/diagnosis questions (Persona §6.3).
- **Acceptance:** Draft/published version immutable and traceable; preview matches the public page.
- **Evidence:** Published survey version + consent/privacy text snapshot.
- **Manual fallback:** Survey usable without AI (manual operation).
- **Out of scope:** Requesting specific ratings; login-gated survey.

### UC-P0-03 — Receive Completed-Service Event

- **Actor / owner:** DaengtisiaMS integration (system persona).
- **Trigger:** `VisitCompleted` after server-side authentication (Persona §4.10, §7.3).
- **Preconditions:** Authenticated API/webhook contract; tenant/branch mapping configured.
- **Main flow:** Receive signed event → authenticate → validate → map to tenant/branch → deduplicate →
  durably acknowledge.
- **Alternate flow:** Controlled CSV/manual import with minimum pilot fields; on-site QR intake — both
  labelled honestly as fallback, not real-time integration.
- **Failure flow:** Failed auth or invalid payload rejected and logged; no partial ingestion.
- **Minimum data:** `tenant_id`, `branch_id`, external customer reference/pseudonymous ID, service event
  ID, completion timestamp, generic service category (Persona §8.1).
- **Permission:** Machine credential, tenant-scoped.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** event received, event accepted/rejected, idempotency key recorded.
- **External dependency:** DaengtisiaMS.
- **Truthful states:** received → accepted (exactly once) / rejected. Manual import never shown as
  real-time success (Persona §7.3).
- **Security & privacy:** Prohibited fields (diagnosis, clinical notes, MRN, prescription, odontogram,
  etc.) MUST be absent/rejected (Persona §8.2; `.claude/rules/04`).
- **Acceptance:** Authentication, idempotency, tenant/branch mapping, validation, audit, and
  prohibited-field handling pass.
- **Evidence:** Event log with idempotency keys + prohibited-field test result.
- **Manual fallback:** CSV/manual and QR intake.
- **Out of scope:** Ingesting clinical documents; replacing DaengtisiaMS workflow.

### UC-P0-04 — Create and Send Invitation

- **Actor / owner:** Campaign engine (or staff fallback).
- **Trigger:** Eligible completed-service event (Persona §7.1).
- **Preconditions:** Eligibility met; lawful communication path or on-site QR.
- **Main flow:** Confirm eligibility → apply delay (default 60 min, configurable 30–120) → respect
  sending window 09:00–20:00 Asia/Makassar → generate unique WhatsApp survey link → send/share →
  record delivery state.
- **Alternate flow:** QR / public campaign link fallback (mandatory); email optional; events outside the
  window scheduled to the next window.
- **Failure flow:** Delivery failure recorded with reason; no silent success.
- **Minimum data:** Invitation, channel, delivery state, encrypted phone/email if needed (Persona §8.1).
- **Permission:** Campaign engine / coordinator.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** invitation created, scheduled, sent/shared, failed, expired.
- **External dependency:** WhatsApp/email provider (see `WHATSAPP_INVITATION_PILOT_BASELINE.md`).
- **Truthful states:** created → scheduled → sent/shared → delivered/failed → expired (7 days).
- **Security & privacy:** Frequency cap 1/customer/14 calendar days; max 1 reminder after 24h; opt-out
  mandatory (Persona §7.2).
- **Acceptance:** Frequency cap, sending window, expiry, channel state, failure reason, and truthful
  delivery state applied.
- **Evidence:** Invitation logs with cap/window/expiry enforcement.
- **Manual fallback:** Staff-shared QR/link.
- **Out of scope:** Fully automated WhatsApp Business Platform delivery (P2); more than one reminder.

### UC-P0-05 — Customer Fills Feedback

- **Actor / owner:** Customer or Wali.
- **Trigger:** Customer opens a valid survey token (Persona §4.1, §6).
- **Preconditions:** Token valid, scoped, not expired; consent/privacy notice visible before submit.
- **Main flow:** Open mobile-first survey → answer CSAT/CES/NPS + optional comment → answer conditional
  problem category + follow-up consent if CSAT 1–3 → submit → scores computed and stored.
- **Alternate flow:** Controlled correction flow for a single response if enabled.
- **Failure flow:** Expired/invalid token shows a truthful error, not a false success.
- **Minimum data:** Response, answers, consent/opt-out state (Persona §8.1).
- **Permission:** Anonymous token holder; no console access.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** response opened, submitted, consent recorded.
- **External dependency:** None.
- **Truthful states:** invitation opened → response submitted → scored.
- **Security & privacy:** Token hard to guess, scoped, expiring; no medical history requested; customer
  input is untrusted (Persona §6.3, §12).
- **Acceptance:** Token scope/expiry pass, duplicate submission controlled, consent stored, mobile
  usability good; target completion under 2 minutes.
- **Evidence:** Response records + consent capture (no real PII in repo).
- **Manual fallback:** Survey works without AI.
- **Out of scope:** Account creation; requesting medical detail.

### UC-P0-06 — Analyze Feedback with Manual Fallback

- **Actor / owner:** AI analysis service and CX user.
- **Trigger:** Stored survey response (Persona §9.1).
- **Preconditions:** Response present; guardrails and prompt/model versions configured.
- **Main flow:** Redact prohibited fields → run analysis → produce sentiment, topic, severity, risk,
  summary, confidence, suggested action as structured output → run guardrails → record model/prompt/cost.
- **Alternate flow:** Low-confidence or guardrail-flagged output escalates to human review.
- **Failure flow:** AI failure falls back to manual classification; the failure is shown truthfully.
- **Minimum data:** Feedback analysis record; no prohibited fields in the prompt (Persona §8.2).
- **Permission:** Analysis service (tenant-scoped); CX user reviews.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** analysis run, guardrail result, model/prompt version, cost, manual override.
- **External dependency:** LLM provider (see `PILOT_AI_HUMAN_APPROVAL_RULES.md`,
  `PILOT_AI_EVALUATION_PLAN.md`).
- **Truthful states:** unanalyzed → analyzed / analysis failed → manually classified.
- **Security & privacy:** Customer content MUST NOT determine tool calls; prohibited fields never sent to
  AI (Persona §8.2; `.claude/rules/04`, `05`).
- **Acceptance:** Structured output valid; model/prompt/cost recorded; guardrails run; manual
  classification available when AI fails.
- **Evidence:** Structured-output validity sample + trace/cost log + manual-fallback proof.
- **Manual fallback:** Human classification (mandatory).
- **Out of scope:** Autonomous action from AI output; auto-compensation.

### UC-P0-07 — Automatic Recovery Ticket Creation

- **Actor / owner:** Rule engine.
- **Trigger:** Negative / high-risk feedback detected (Persona §9.1, §10.1).
- **Preconditions:** Analysis or rule match; severity mapping configured.
- **Main flow:** Evaluate rules → create branch-scoped ticket with severity, priority, SLA, reason code →
  emit audit.
- **Alternate flow:** Manual ticket creation by a CX user.
- **Failure flow:** Idempotent creation prevents duplicate tickets on retry.
- **Minimum data:** Ticket, severity, priority, SLA, reason code (Persona §8.1).
- **Permission:** Rule engine; branch-scoped.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** ticket created, severity/SLA assigned, reason code recorded.
- **External dependency:** None.
- **Truthful states:** none → open (new).
- **Security & privacy:** No PII/medical leakage into ticket public fields (Persona §8.2).
- **Acceptance:** Creation idempotent; severity, priority, SLA, reason code, and audit present.
- **Evidence:** Ticket creation log with idempotency + severity mapping.
- **Manual fallback:** Manual ticket creation.
- **Out of scope:** Auto-refund/compensation; autonomous complaint resolution.

### UC-P0-08 — Triage, Assignment, and Escalation

- **Actor / owner:** Branch Manager.
- **Trigger:** New/open ticket in branch queue (Persona §9.1, §10.1).
- **Preconditions:** UC-P0-07 complete; SLA baseline configured.
- **Main flow:** Review ticket → set severity if needed → assign accountable assignee → start SLA clock →
  define escalation path.
- **Alternate flow:** Reassignment with recorded history; escalation to owner for critical.
- **Failure flow:** Unassigned critical/high triggers escalation/notification; SLA breach recorded.
- **Minimum data:** Assignment, SLA clock, escalation path, reassignment history (Persona §8.1).
- **Permission:** Branch Manager (own branch only).
- **Tenant scope:** Single tenant. **Branch scope:** Own branch.
- **Audit events:** triaged, assigned, reassigned, escalated, SLA started/breached.
- **External dependency:** Notification channel.
- **Truthful states:** open → triaged → assigned → escalated (as applicable).
- **Security & privacy:** Branch permission enforced; notification deduplicated (Persona §9.1).
- **Acceptance:** Branch permission, SLA clock, notification deduplication, and reassignment history pass.
- **Evidence:** Assignment/escalation samples + SLA timeline.
- **Manual fallback:** Manual assignment.
- **Out of scope:** Cross-branch visibility; auto-escalation without audit.

### UC-P0-09 — Contact Customer Privately

- **Actor / owner:** Recovery Assignee.
- **Trigger:** Assigned ticket requiring follow-up (Persona §9.1, §10.2).
- **Preconditions:** Consent/contact policy configured; approved contact draft available.
- **Main flow:** Open ticket → use approved contact draft → contact via private channel → record contact
  attempt and customer response → add internal note.
- **Alternate flow:** If customer opted out, no repeated contact; alternative service action recorded.
- **Failure flow:** No-response path follows contact-attempt policy (max 2 attempts / 3 business days).
- **Minimum data:** Contact attempt, response, internal note (Persona §8.1).
- **Permission:** Recovery Assignee.
- **Tenant scope:** Single tenant. **Branch scope:** Own branch/tickets.
- **Audit events:** contact attempted, response logged, note added.
- **External dependency:** Private contact channel (WhatsApp/phone if consented).
- **Truthful states:** contact pending → contacted → responded / no response.
- **Security & privacy:** No public disclosure; consent/contact policy enforced; unauthorized
  compensation promise blocked (Persona §10.2, §11).
- **Acceptance:** No public disclosure, consent/contact policy applied, unauthorized compensation promise
  blocked.
- **Evidence:** Contact-attempt evidence (tenant-safe).
- **Manual fallback:** Fully manual contact with approved template.
- **Out of scope:** Financial commitment by assignee; public reply.

### UC-P0-10 — Ticket Resolution and Closure

- **Actor / owner:** Recovery Assignee and authorized approver.
- **Trigger:** Contact/action completed (Persona §9.1, §10.3).
- **Preconditions:** Required fields ready; approval role available for high-risk remedies.
- **Main flow:** Record root cause, corrective action, outcome, evidence → obtain required approval →
  record SLA result → close.
- **Alternate flow:** Reopen path if issue recurs.
- **Failure flow:** Missing required field or approval blocks closure.
- **Minimum data:** Root cause, corrective action, outcome, evidence, approval (Persona §10.3).
- **Permission:** Assignee + authorized approver.
- **Tenant scope:** Single tenant. **Branch scope:** Own branch.
- **Audit events:** resolution recorded, approval, SLA result, closed, reopened.
- **External dependency:** None.
- **Truthful states:** in progress → resolved → closed / reopened. "Recovered" MUST NOT be concluded
  merely because a ticket is closed or a review changed (Persona §10.3).
- **Security & privacy:** No unresolved critical safety/privacy/legal/clinical item at closure.
- **Acceptance:** Required fields, approval, SLA result, reopen path, and audit complete.
- **Evidence:** Recovery outcome record + approval trail.
- **Manual fallback:** Manual closure workflow.
- **Out of scope:** Auto-closure without approval; fictitious recovery claims.

### UC-P0-11 — Connect Google Business Profile

- **Actor / owner:** Integration Admin or authorized owner.
- **Trigger:** Owner authorizes Google connection (Persona §9.1, §12).
- **Preconditions:** Account/location controlled by an authorized Daengtisia representative.
- **Main flow:** Start OAuth → validate state → store encrypted token → run permission diagnostic → map
  pilot location to branch.
- **Alternate flow:** Reauthorization when token expires; disconnect flow with credential deletion.
- **Failure flow:** Failed OAuth or missing permission leaves Google scope BLOCKED (honestly), pilot
  CSAT/recovery still runs (Persona §12).
- **Minimum data:** Encrypted OAuth token, account/location mapping (Persona §8.1).
- **Permission:** Integration Admin/owner; requires additional approval (`.claude/rules/15`).
- **Tenant scope:** Single tenant. **Branch scope:** Mapped location → pilot branch.
- **Audit events:** OAuth initiated, token stored, permission diagnostic, mapped, disconnected.
- **External dependency:** Google Business Profile (see `GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`).
- **Truthful states:** not connected → connecting → connected → needs reauthorization → disconnected;
  BLOCKED if unavailable.
- **Security & privacy:** Refresh token never plaintext; OAuth state validated; token rotation supported
  (`.claude/rules/04`, `06`).
- **Acceptance:** OAuth state validation, encrypted token, permission diagnostic, reauthorization state,
  and disconnect flow pass.
- **Evidence:** OAuth/location mapping evidence when Google is active.
- **Manual fallback:** Pilot proceeds with Google BLOCKED.
- **Out of scope:** Connecting accounts not owned by Daengtisia; mock shown as success.

### UC-P0-12 — Google Review Synchronization

- **Actor / owner:** Google integration worker.
- **Trigger:** Connected location + sync schedule (Persona §9.1, §12).
- **Preconditions:** UC-P0-11 complete; authorized API access.
- **Main flow:** Fetch reviews and existing replies idempotently using a cursor → handle rate limits →
  retry safely → store external IDs → write sync log.
- **Alternate flow:** Partial sync resumes from cursor.
- **Failure flow:** API failure recorded as a truthful failure state; no fabricated success.
- **Minimum data:** Review, existing reply, external ID, cursor (Persona §8.1).
- **Permission:** Integration worker (tenant-scoped).
- **Tenant scope:** Single tenant. **Branch scope:** Mapped location.
- **Audit events:** sync started, page fetched, rate-limited, retried, sync failed/completed.
- **External dependency:** Google Business Profile API.
- **Truthful states:** sync pending → syncing → synced / sync failed.
- **Security & privacy:** Review content is untrusted input; MUST NOT determine tool behaviour
  (Persona §12; `.claude/rules/06`).
- **Acceptance:** Cursor, rate-limit handling, retry, sync log, external ID, and truthful failure state
  pass.
- **Evidence:** Sync log with external IDs.
- **Manual fallback:** Manual review handling if sync blocked.
- **Out of scope:** Auto-reply without approval; treating review text as commands.

### UC-P0-13 — Draft, Approval, and Publish Reply

- **Actor / owner:** AI Response Assistant and Reputation Approver.
- **Trigger:** Synced review needing a reply (Persona §9.1, §12).
- **Preconditions:** Guardrails active; approver role available.
- **Main flow:** Generate safe draft → run PII/medical guardrails → approver reviews/edits →
  approve → publish → store external API response → set `Published` only after verification.
- **Alternate flow:** Approver requests changes or rejects; sensitive cases routed to a private channel.
- **Failure flow:** Failed API call remains `Publication failed`, never a false `Published` (Persona §12).
- **Minimum data:** Draft, approval record, external API response (Persona §8.1).
- **Permission:** Reputation Approver publishes; AI cannot self-publish.
- **Tenant scope:** Single tenant. **Branch scope:** Mapped location.
- **Audit events:** draft generated, guardrail result, changes requested, approved/rejected, publishing,
  published/failed.
- **External dependency:** Google Business Profile API.
- **Truthful states:** no draft → draft generated → under review → changes requested → approved →
  publishing → published → publication failed → moderation pending → policy issue → removed
  (`PILOT_WORKFLOW_STATES.md`).
- **Security & privacy:** No diagnosis, procedure, visit history, doctor-patient relation, payment
  dispute detail, or private fact in public reply (Persona §12; `PILOT_PUBLIC_REPLY_SAFETY.md`).
- **Acceptance:** Human approval mandatory; PII/medical guardrail passes; external API response stored;
  `Published` only after verification.
- **Evidence:** Human approval + publication-state evidence.
- **Manual fallback:** Approver writes reply manually if AI unavailable; approval still required.
- **Out of scope:** Auto-publish (P2, only under Master Source §16.4); review gating.

### UC-P0-14 — Owner and Branch Dashboard

- **Actor / owner:** Owner and Branch Manager.
- **Trigger:** User opens dashboard (Persona §9.1, §14).
- **Preconditions:** Data present; KPI definitions documented.
- **Main flow:** Load feedback, SLA, recovery, and review metrics scoped to role → reconcile to source
  records.
- **Alternate flow:** Branch Manager sees only own branch; owner sees whole tenant.
- **Failure flow:** Missing data shows an empty/loading state, never fabricated values.
- **Minimum data:** Aggregated KPI derived from source records (Persona §14).
- **Permission:** Owner (tenant), Branch Manager (branch).
- **Tenant scope:** Single tenant. **Branch scope:** Own branch for manager.
- **Audit events:** dashboard viewed (as configured), export triggered.
- **External dependency:** None.
- **Truthful states:** empty → loading → loaded / failed (no false success).
- **Security & privacy:** No cross-branch leakage; no sample data shown as real (`.claude/rules/10`).
- **Acceptance:** KPI definitions documented, branch scope applied, dashboard reconciled with source
  records.
- **Evidence:** Reconciliation report (dashboard vs source) — target 100% for release-critical KPI.
- **Manual fallback:** Source records readable without AI.
- **Out of scope:** Predictive analytics; fabricated trends.

### UC-P0-15 — Audit and Evidence Export

- **Actor / owner:** Auditor or Pilot Coordinator.
- **Trigger:** Evidence/export request (Persona §9.1, §15).
- **Preconditions:** Permissioned export role.
- **Main flow:** Select material actions / pilot KPI → generate permissioned, tenant-scoped, timestamped
  export → record audit.
- **Alternate flow:** Scheduled weekly evidence checkpoint.
- **Failure flow:** Unauthorized export denied and logged.
- **Minimum data:** Audit records, KPI, export metadata (Persona §8.1, §15).
- **Permission:** Auditor / Coordinator; export permissioned and audited.
- **Tenant scope:** Single tenant. **Branch scope:** As permissioned.
- **Audit events:** export requested, generated, downloaded, denied.
- **External dependency:** None.
- **Truthful states:** requested → generated → delivered / denied.
- **Security & privacy:** Export tenant-scoped; no real customer PII stored in repo (Persona §15;
  `.claude/rules/07`).
- **Acceptance:** Export permissioned, tenant-scoped, timestamped, and audited.
- **Evidence:** Export audit record + evidence bundle.
- **Manual fallback:** Manual evidence compilation from audited records.
- **Out of scope:** Bulk unrestricted export; PII export to repo.

### UC-P0-16 — Operate When AI or Provider Fails

- **Actor / owner:** Operations staff.
- **Trigger:** AI or external provider outage/degradation (Persona §9.1, §16 risks).
- **Preconditions:** Manual workflows configured; kill switch available.
- **Main flow:** Detect failure → surface truthful failure state → continue survey, manual triage,
  ticketing, approval, and audit manually → controlled retry without duplicate side effects.
- **Alternate flow:** Kill switch halts AI/external actions while manual workflow continues.
- **Failure flow:** Retry is idempotent; no duplicate invitation, ticket, or reply.
- **Minimum data:** Failure/incident log (Persona §8.1).
- **Permission:** Operations staff; kill switch access controlled.
- **Tenant scope:** Single tenant. **Branch scope:** Pilot branch.
- **Audit events:** failure detected, kill switch toggled, manual action taken, retry attempted.
- **External dependency:** The failing provider (degraded).
- **Truthful states:** All affected surfaces show honest failure/degraded states.
- **Security & privacy:** No fabricated success; no duplicate external action from retry (Persona §14.1).
- **Acceptance:** Failure state honest, retry does not create duplicate action, kill switch/manual
  fallback works.
- **Evidence:** Incident/failure log + retry idempotency test.
- **Manual fallback:** This use case IS the fallback path (`PILOT_MANUAL_FALLBACK.md`).
- **Out of scope:** Silent auto-recovery that hides failure; duplicate side effects.

---

## 3. P1 — Valuable at stabilization (Persona §9.2)

These are earned after P0 is stable and MUST NOT be built before basic workflows are stable
(`.claude/rules/02`):

- Single reminder with opt-out and delivery audit.
- Weekly owner digest.
- Approved knowledge-base templates and branch information.
- Root-cause trend and repeated-complaint detection.
- Saved filters and assignment queue.
- Pilot scorecard and cost-per-run report.
- Safe bulk assignment **without** bulk auto-publish.

## 4. P2 — Deferred until evidence supports (Persona §9.3)

- Fully automated WhatsApp Business Platform delivery.
- Multi-branch pilot expansion.
- Controlled low-risk auto-publish (only under Master Source §16.4 preconditions).
- Advanced predictive analytics.
- Social media inbox beyond Google.
- Voice / call agent.
- Automated refund, discount, or compensation.

## 5. Coverage note

Every P0 use case above has a named actor/owner, acceptance criteria, and an evidence requirement, per
Persona §19. Acceptance tests for these cases are catalogued in
`../testing/PILOT_ACCEPTANCE_TEST_CATALOG.md`, and requirement traceability in
`../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`.
