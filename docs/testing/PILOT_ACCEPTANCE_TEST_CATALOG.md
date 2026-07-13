# Pilot Acceptance Test Catalog

**Document:** Pilot Acceptance Test Catalog
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Nature of this document

These are **PLANNED acceptance tests**. The application is NOT STARTED, so no test has been executed
and no result exists. Every entry is marked **PLANNED**. Tests are written against the P0 use cases
and hard safety/correctness gates in the canonical Step 2 source
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) (§9 use cases,
§14.1 hard gates). They will be executed only after implementation and pilot readiness (see the UAT
plan, [`PILOT_UAT_PLAN.md`](PILOT_UAT_PLAN.md)).

- Rule authority: [`../../.claude/rules/09-testing-and-quality-gates.md`](../../.claude/rules/09-testing-and-quality-gates.md).
- Contract references: [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md), [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md), [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md).
- Traceability: [`STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`](STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md).

Evidence rule: no test evidence may contain real customer PII; test data uses synthetic/pseudonymous
records only (Persona §15).

---

## 1. Test ID scheme

- `AT-P0-01` … `AT-P0-16` — one per P0 use case (UC-P0-01 … UC-P0-16).
- `AT-GATE-01` … `AT-GATE-07` — dedicated hard-gate tests covering Persona §14.1 gates G1–G7 plus the
  anti-gating rule (Persona §6.3/§12/§16). See §3 for the AT-GATE → gate mapping.

Status legend: **PLANNED** (not yet runnable — application NOT STARTED).

---

## 2. P0 use-case acceptance tests

### AT-P0-01 — Onboarding tenant and pilot branch
- **UC:** UC-P0-01 · **Status:** PLANNED
- **Objective:** Tenant, Daengtisia Pusat branch, timezone, roles, and pilot settings configured without cross-tenant visibility.
- **Preconditions:** Clean tenant; Pilot Coordinator role provisioned.
- **Steps:** Create tenant → create pilot branch → set Asia/Makassar → assign minimum roles → save pilot settings.
- **Expected:** Config persisted, tenant/branch scoped, setup fully audited.
- **Pass/fail:** Pass = no cross-tenant record visible AND every setup action audited. Fail = any cross-tenant read or missing audit entry.
- **Evidence:** Config snapshot, audit log extract, tenant-scope query result.

### AT-P0-02 — Create and publish survey
- **UC:** UC-P0-02 · **Status:** PLANNED
- **Objective:** Versioned CSAT/CES/NPS/comment/conditional survey published; versions immutable and traceable.
- **Preconditions:** Onboarded tenant/branch.
- **Steps:** Build survey → preview → publish → attempt to mutate a published version.
- **Expected:** Published version immutable; preview matches public page; version history traceable.
- **Pass/fail:** Pass = mutation of a published version is rejected AND preview == public render. Fail = published version editable or preview mismatch.
- **Evidence:** Version records, preview vs. public capture.

### AT-P0-03 — Receive completed-service event
- **UC:** UC-P0-03 · **Status:** PLANNED
- **Objective:** Eligible `VisitCompleted` accepted exactly once with auth, mapping, validation, audit, prohibited-field handling.
- **Preconditions:** Event contract baseline implemented; signing secret configured in secret store.
- **Steps:** Send signed event → resend same `event_id` → send unsigned/replayed event → send event with a prohibited medical field.
- **Expected:** First accepted once; duplicate is no-op; unsigned/replayed rejected; prohibited-field event rejected/quarantined with no medical data stored/forwarded.
- **Pass/fail:** Pass = exactly-once effect AND all invalid variants rejected AND no medical field reaches storage/AI. Fail = duplicate invitation, accepted invalid event, or leaked prohibited field.
- **Evidence:** Intake audit entries, idempotency store, rejection reason codes.

### AT-P0-04 — Create and send invitation
- **UC:** UC-P0-04 · **Status:** PLANNED
- **Objective:** Unique invitation scheduled/sent under frequency cap, window, expiry with truthful delivery state.
- **Preconditions:** Accepted eligible event; messaging/QR path configured.
- **Steps:** Trigger from event → observe delay/window scheduling → force a second event within 14 days → let one invitation expire.
- **Expected:** 60-min delay (configurable 30–120), 09:00–20:00 window respected, frequency cap blocks the second, expiry at 7 days, delivery state truthful.
- **Pass/fail:** Pass = all timing rules enforced AND no duplicate send AND states truthful. Fail = out-of-window send, cap bypass, or false `delivered`.
- **Evidence:** Invitation records, scheduler log, delivery-state transitions.

### AT-P0-05 — Customer fills feedback
- **UC:** UC-P0-05 · **Status:** PLANNED
- **Objective:** Response stored and scores computed; token scope/expiry enforced; duplicate submission controlled; consent stored.
- **Preconditions:** Valid invitation token.
- **Steps:** Open valid token → submit → resubmit same token → open expired/tampered token → submit on mobile viewport.
- **Expected:** One response stored, CSAT/CES/NPS computed, consent captured, duplicate/expired/tampered rejected, mobile usable.
- **Pass/fail:** Pass = single response AND invalid tokens rejected AND scores correct. Fail = duplicate accepted, expired token works, or wrong score.
- **Evidence:** Response record, computed metrics, token validation log.

### AT-P0-06 — Analyze feedback with manual fallback
- **UC:** UC-P0-06 · **Status:** PLANNED
- **Objective:** Structured sentiment/topic/severity/risk/summary/confidence/suggested-action with logging and guardrails; manual classification when AI fails.
- **Preconditions:** Stored responses; AI provider configured plus a forced-failure mode.
- **Steps:** Run analysis → validate structured output → record model/prompt/cost → simulate AI outage → classify manually.
- **Expected:** Valid structured output, guardrails run, model/prompt/cost logged, manual classification available on failure.
- **Pass/fail:** Pass = valid output + full logging + working manual fallback. Fail = invalid output, missing trace/cost, or workflow blocked when AI down.
- **Evidence:** Structured output sample, trace/cost log, manual-fallback record.

### AT-P0-07 — Auto-create recovery ticket
- **UC:** UC-P0-07 · **Status:** PLANNED
- **Objective:** Negative/high-risk feedback creates a branch-scoped ticket idempotently with severity/priority/SLA/reason code.
- **Preconditions:** Analyzed negative/high-risk feedback.
- **Steps:** Trigger rule → re-run rule on same feedback → inspect ticket fields.
- **Expected:** Exactly one ticket per qualifying feedback; severity/priority/SLA/reason present; branch-scoped; audited.
- **Pass/fail:** Pass = idempotent single ticket with required fields. Fail = duplicate ticket or missing SLA/reason.
- **Evidence:** Ticket record, rule-run log, audit entry.

### AT-P0-08 — Triage, assignment, escalation
- **UC:** UC-P0-08 · **Status:** PLANNED
- **Objective:** Ticket gets accountable assignee + escalation path with branch permission, SLA clock, notification dedup, reassignment history.
- **Preconditions:** Open ticket; Branch Manager role.
- **Steps:** Assign → reassign → escalate → observe SLA clock and notifications.
- **Expected:** Only in-branch assignees selectable, SLA clock runs, notifications deduplicated, reassignment history retained.
- **Pass/fail:** Pass = branch scope enforced AND history/SLA correct. Fail = cross-branch assignment or duplicate notifications.
- **Evidence:** Assignment history, SLA timeline, notification log.

### AT-P0-09 — Contact customer privately
- **UC:** UC-P0-09 · **Status:** PLANNED
- **Objective:** Recovery Assignee uses approved draft, logs contact attempt/response, with no public disclosure and blocked unauthorized compensation.
- **Preconditions:** Assigned ticket with consent state.
- **Steps:** Open approved contact draft → record attempt/outcome → attempt to promise a refund/compensation.
- **Expected:** Private channel only, consent/contact policy applied, unauthorized compensation promise blocked, attempts logged.
- **Pass/fail:** Pass = no public disclosure AND compensation block enforced. Fail = public leak or unauthorized financial commitment allowed.
- **Evidence:** Contact-attempt log, permission-check result.

### AT-P0-10 — Resolution and closure
- **UC:** UC-P0-10 · **Status:** PLANNED
- **Objective:** Root cause, corrective action, outcome, evidence recorded with required fields, approval, SLA result, reopen path.
- **Preconditions:** Contacted ticket.
- **Steps:** Fill required fields → request closure without approval → obtain approval → close → reopen.
- **Expected:** Closure blocked until required fields + approval present; SLA result recorded; reopen path works; fully audited.
- **Pass/fail:** Pass = closure gated on required fields/approval AND reopen works. Fail = closure without approval or lost history.
- **Evidence:** Ticket closure record, approval log, audit trail.

### AT-P0-11 — Connect Google Business Profile
- **UC:** UC-P0-11 · **Status:** PLANNED
- **Objective:** Authorized account + pilot location mapped with OAuth state validation, encrypted token, permission diagnostic, reauthorization, disconnect.
- **Preconditions:** Authorized Google test account (or documented BLOCKED state).
- **Steps:** Start OAuth → tamper with `state` → complete valid connect → run permission diagnostic → disconnect and delete credentials.
- **Expected:** Tampered `state` rejected, token stored encrypted (no plaintext refresh), permission diagnostic truthful, disconnect deletes credentials.
- **Pass/fail:** Pass = state validation + encrypted storage + working disconnect. Fail = accepted bad state, plaintext token, or residual credential.
- **Evidence:** OAuth callback log, encrypted-token verification, disconnect audit.

### AT-P0-12 — Sync Google reviews
- **UC:** UC-P0-12 · **Status:** PLANNED
- **Objective:** Reviews/replies sync idempotently with cursor, rate-limit handling, retry, external IDs, truthful failure.
- **Preconditions:** Connected Google location (or BLOCKED).
- **Steps:** Initial sync → re-sync → simulate rate-limit/error → inspect external IDs and failure state.
- **Expected:** No duplicate reviews/replies, cursor advances, rate-limit handled with retry, failure shows `sync failed`.
- **Pass/fail:** Pass = idempotent sync AND truthful failure state. Fail = duplicates or false success on error.
- **Evidence:** Sync log, external-ID mapping, failure-state capture.

### AT-P0-13 — Draft, approval, publish reply
- **UC:** UC-P0-13 · **Status:** PLANNED
- **Objective:** Safe draft reviewed/edited/approved/published; human approval mandatory; PII/medical guardrail; `Published` only after verification.
- **Preconditions:** Synced review; Reputation Approver role.
- **Steps:** Generate draft → run guardrail → attempt publish without approval → approve → publish → simulate API failure.
- **Expected:** Publish blocked without approval, guardrail blocks PII/medical, API response stored, `Published` only after provider verification, failure stays `Publication failed`.
- **Pass/fail:** Pass = 100% human approval AND no false `Published` AND guardrail enforced. Fail = unapproved publish, PII leak, or false success.
- **Evidence:** Approval record, guardrail result, external API response, publication-state log.

### AT-P0-14 — Owner and branch dashboard
- **UC:** UC-P0-14 · **Status:** PLANNED
- **Objective:** Feedback/SLA/recovery/review metrics visible, branch-scoped, reconciled with source records.
- **Preconditions:** Populated tenant data.
- **Steps:** Open owner dashboard → open branch dashboard as Branch Manager → reconcile KPIs against source records.
- **Expected:** KPI definitions documented, branch scope enforced, dashboard == source for release-critical KPIs.
- **Pass/fail:** Pass = 100% reconciliation on release-critical KPIs AND branch scope enforced. Fail = mismatch or cross-branch leak.
- **Evidence:** Reconciliation report, scope-check result.

### AT-P0-15 — Audit and export evidence
- **UC:** UC-P0-15 · **Status:** PLANNED
- **Objective:** Material actions and KPIs exportable; export permissioned, tenant-scoped, timestamped, audited.
- **Preconditions:** Auditor/Coordinator role; activity present.
- **Steps:** Request export without permission → grant permission → export → verify audit of the export itself.
- **Expected:** Unpermissioned export blocked; export tenant-scoped, timestamped; export action audited; no real PII in evidence.
- **Pass/fail:** Pass = permissioned + scoped + audited export. Fail = unauthorized export or missing audit.
- **Evidence:** Export manifest, permission check, export audit entry.

### AT-P0-16 — Operate when AI/provider fails
- **UC:** UC-P0-16 · **Status:** PLANNED
- **Objective:** Survey, manual triage, ticketing, approval, audit remain usable; failure states truthful; retry causes no duplicate; kill switch works.
- **Preconditions:** Forced AI and Google outage modes.
- **Steps:** Disable AI → run manual triage/ticketing/approval → force retries → engage kill switch.
- **Expected:** Core workflow usable without AI, honest failure states, no duplicate action from retry, kill switch stops automated action.
- **Pass/fail:** Pass = manual path works AND no duplicate AND kill switch effective. Fail = workflow blocked by AI outage or duplicate side-effect.
- **Evidence:** Manual-run records, retry/idempotency log, kill-switch audit.

---

## 3. Hard-gate acceptance tests

These map to the Persona §14.1 hard safety/correctness gates G1–G7, plus the anti-gating rule
(Persona §6.3/§12/§16). AT-GATE-01 → G1, AT-GATE-02 → G3, AT-GATE-03 → G4, AT-GATE-04 → G7,
AT-GATE-05 → G6, AT-GATE-06 → anti-gating (§12/§16), AT-GATE-07 → G5.

### AT-GATE-01 — Cross-tenant isolation
- **Status:** PLANNED · **Objective:** Zero cross-tenant data exposure across DB, API, export, search, dashboard, notifications.
- **Steps:** As tenant A, attempt to read/enumerate tenant B records via direct ID, API, export, and search.
- **Expected:** All cross-tenant attempts denied; no B data returned.
- **Pass/fail:** Pass = zero cross-tenant exposure. Fail = any tenant-B datum visible. **Any failure = NO-GO.**
- **Evidence:** Denied-access logs, isolation test report.

### AT-GATE-02 — Prohibited-field / medical-data rejection
- **Status:** PLANNED · **Objective:** Medical/prohibited data never reaches AI prompt, storage, or public output.
- **Steps:** Inject prohibited fields at event intake, survey input, and reply drafting; inspect AI prompt payload and public render.
- **Expected:** Prohibited content rejected/redacted at every entry point; absent from AI prompts and public replies.
- **Pass/fail:** Pass = zero prohibited-field leakage. Fail = any medical field in AI/public. **Any failure = NO-GO.**
- **Evidence:** Rejection logs, AI-prompt capture (sanitized), guardrail result.

### AT-GATE-03 — Human approval before publish
- **Status:** PLANNED · **Objective:** 100% of public Google replies have recorded human approval before publication.
- **Steps:** Attempt to publish a reply through every path (API, retry, bulk) without approval.
- **Expected:** Every publish path blocked without an approval record.
- **Pass/fail:** Pass = 100% replies human-approved. Fail = any unapproved publish. **Any failure = NO-GO.**
- **Evidence:** Approval records, publication log.

### AT-GATE-04 — Idempotency / no duplicate on retry
- **Status:** PLANNED · **Objective:** No duplicated invitation, ticket, or reply from retry/idempotency failure.
- **Steps:** Force retries on event intake, invitation send, ticket creation, and reply publish.
- **Expected:** Exactly-once effect on each; retries are safe no-ops.
- **Pass/fail:** Pass = no duplicate side-effects. Fail = any duplicate. **Any failure = NO-GO.**
- **Evidence:** Idempotency-store snapshots, side-effect counts.

### AT-GATE-05 — Truthful failure / external state
- **Status:** PLANNED · **Objective:** No external action reported as success before provider verification.
- **Steps:** Force provider errors on invitation delivery, review sync, and reply publish; inspect displayed states.
- **Expected:** States show `failed`/`sync failed`/`Publication failed`; never a false success.
- **Pass/fail:** Pass = every unverified action shown truthfully. Fail = any false success. **Any failure = NO-GO.**
- **Evidence:** State-transition logs, provider-response captures.

### AT-GATE-06 — No review gating (Persona §6.3/§12/§16)
- **Status:** PLANNED · **Objective:** Equal Google Review access for all eligible respondents regardless of score.
- **Steps:** Submit high- and low-CSAT responses; compare review-link presentation and access.
- **Expected:** Identical, neutral review access regardless of score; no rating request; no sentiment-based routing.
- **Pass/fail:** Pass = equal access confirmed. Fail = any gating/selective access. **Any failure = NO-GO.**
- **Evidence:** Flow captures for both score bands.

### AT-GATE-07 — Critical-incident owner, timeline, and audit evidence (gate G5)
- **Status:** PLANNED · **Objective:** 100% of critical incidents have a named owner, a recorded timeline, and audit evidence.
- **Steps:** Trigger a critical-severity feedback/incident; verify an incident owner is assigned, acknowledgement/first-contact timestamps are recorded against SLA, and every material action is written to the non-deletable audit log.
- **Expected:** Each critical incident carries owner + timeline + immutable audit trail; none is closed without required evidence.
- **Pass/fail:** Pass = every critical incident fully evidenced. Fail = any critical incident lacking owner/timeline/audit. **Any failure = NO-GO.**
- **Evidence:** Incident register, SLA timers, audit-log excerpts (tenant-safe, no real PII).

---

## 4. Status summary

All 16 P0 tests and all 6 hard-gate tests are **PLANNED**. Application implementation is NOT STARTED;
no acceptance test has been executed and no pass/fail result exists yet. Execution is governed by
[`PILOT_UAT_PLAN.md`](PILOT_UAT_PLAN.md).
