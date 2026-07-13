# Pilot Customer Journeys — Aish Agentic AI

**Document:** Pilot Customer Journeys (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

End-to-end journeys derived from `PERSONA_AND_PILOT_USE_CASES.md` §6–§13 and the P0 catalog in
`PILOT_USE_CASE_CATALOG.md`. Each step names the persona touchpoint, the driving event, the use case,
and the truthful state. Truthful states use the Master Source §53 vocabulary; the full state machines
live in `PILOT_WORKFLOW_STATES.md`. Nothing here asserts that any flow is implemented or runs.

---

## Journey A — Happy-path CSAT feedback and invitation

**Personas:** DaengtisiaMS integration → Campaign engine → Customer/Wali → Owner/Branch Manager.

| # | Step | Persona touchpoint | Event / UC | Truthful state |
|---|---|---|---|---|
| 1 | Visit completed at Daengtisia Pusat | DaengtisiaMS integration | `VisitCompleted` / UC-P0-03 | event received → accepted (exactly once) |
| 2 | Eligibility confirmed, invitation scheduled | Campaign engine | UC-P0-04 | invitation created → scheduled |
| 3 | After 60-min delay, within 09:00–20:00, unique WhatsApp link sent | Campaign engine | UC-P0-04 | scheduled → sent |
| 4 | Customer opens survey (QR fallback if no link) | Customer/Wali | UC-P0-05 | sent → invitation opened |
| 5 | Customer answers CSAT 5 / CES 5 / NPS 9, optional comment | Customer/Wali | UC-P0-05 | opened → response submitted |
| 6 | Scores computed and stored; consent recorded | System | UC-P0-05 | submitted → scored |
| 7 | AI (or manual) analysis: positive sentiment, low severity | AI analysis service / CX user | UC-P0-06 | unanalyzed → analyzed |
| 8 | Dashboard reflects CSAT/NPS/CES, reconciled to source | Owner / Branch Manager | UC-P0-14 | empty → loading → loaded |

**Rules honored:** target completion under 2 minutes; frequency cap 1/customer/14 days; opt-out present;
no 5-star solicitation; no medical data requested (Persona §6.3, §7.2). A neutral Google Review link, if
shown, is offered equally to all eligible respondents regardless of score (Persona §12; `.claude/rules/06`).

---

## Journey B — Negative feedback → recovery ticket → private follow-up → resolution

**Personas:** Customer/Wali → Rule engine → Branch Manager → Recovery Assignee → authorized approver.

| # | Step | Persona touchpoint | Event / UC | Truthful state |
|---|---|---|---|---|
| 1 | Customer submits CSAT 2 + complaint, consents to follow-up | Customer/Wali | UC-P0-05 | response submitted → scored |
| 2 | Analysis flags negative / high severity | AI analysis service / CX user | UC-P0-06 | analyzed (High) |
| 3 | Branch-scoped recovery ticket created idempotently | Rule engine | `HighRiskFeedbackDetected` / UC-P0-07 | none → open (High) |
| 4 | Manager triages, assigns PIC, SLA clock starts | Branch Manager | UC-P0-08 | open → triaged → assigned |
| 5 | Assignee contacts customer privately via approved draft | Recovery Assignee | UC-P0-09 | contact pending → contacted |
| 6 | Contact attempt and response logged; internal note added | Recovery Assignee | UC-P0-09 | contacted → responded |
| 7 | Root cause, corrective action, outcome recorded | Recovery Assignee | UC-P0-10 | in progress → resolved |
| 8 | Required approval obtained; SLA result recorded; ticket closed | Authorized approver | UC-P0-10 | resolved → closed |

**SLA (Persona §10.1):** High = acknowledge 30 min, first private contact 2 h, action plan 8 h. Critical
would instead be acknowledge 15 min, first contact 60 min, with immediate owner escalation and public
reply withheld. **Constraints:** no public disclosure; unauthorized compensation promise blocked; opt-out
respected; max 2 proactive contacts / 3 business days; "recovered" not concluded merely because the ticket
is closed (Persona §10.2, §10.3, §11).

---

## Journey C — Google review → draft → approval → publish

**Personas:** Google integration worker → AI Response Assistant → Reputation Approver.

| # | Step | Persona touchpoint | Event / UC | Truthful state |
|---|---|---|---|---|
| 1 | New Google review synced with external ID | Google integration worker | `GoogleReviewSynced` / UC-P0-12 | sync pending → synced |
| 2 | AI generates a safe draft reply | AI Response Assistant | UC-P0-13 | no draft → draft generated |
| 3 | PII/medical guardrail runs on the draft | Guardrail | UC-P0-13 | draft generated → under review |
| 4 | Approver reviews; requests changes or edits | Reputation Approver | UC-P0-13 | under review → changes requested → under review |
| 5 | Approver approves the final reply | Reputation Approver | UC-P0-13 | under review → approved |
| 6 | Reply submitted to Google API | System | UC-P0-13 | approved → publishing |
| 7a | Google confirms publication | Google API | UC-P0-13 | publishing → published (only after verification) |
| 7b | API call fails | Google API | UC-P0-13 | publishing → publication failed |

**Rules honored:** human approval mandatory for every reply; no diagnosis / procedure / visit history /
payment dispute / private fact in the public reply; sensitive cases routed to a private channel; no review
gating, no requested rating, no incentive; `Published` shown only after provider verification
(Persona §12; `.claude/rules/06`; `PILOT_PUBLIC_REPLY_SAFETY.md`).

---

## Journey D — AI / provider failure → manual fallback

**Personas:** Operations staff → Branch Manager / Reputation Approver.

| # | Step | Persona touchpoint | Event / UC | Truthful state |
|---|---|---|---|---|
| 1 | AI analysis or Google/WhatsApp provider degrades | Operations staff | `AgentRunFailed` / UC-P0-16 | analysis failed / sync failed / publication failed (honest) |
| 2 | Failure surfaced truthfully; no fabricated success | System | UC-P0-16 | degraded state shown |
| 3 | Kill switch halts AI/external actions if needed | Operations staff | UC-P0-16 | AI actions paused |
| 4 | Feedback triaged and classified manually | CX user / Branch Manager | UC-P0-06 fallback | manually classified |
| 5 | Tickets created/assigned manually; SLA continues | Branch Manager | UC-P0-07/08 | open → assigned |
| 6 | Replies drafted manually; approval still required | Reputation Approver | UC-P0-13 fallback | under review → approved |
| 7 | Controlled retry when provider recovers — idempotent | System | UC-P0-16 | retried without duplicate side effect |

**Rules honored:** basic functions MUST NOT depend on AI; retry MUST NOT create duplicate invitation,
ticket, or reply; no external action called success before verification (Persona §14.1;
`.claude/rules/05`; `../ai/PILOT_MANUAL_FALLBACK.md`).

---

## Cross-journey invariants

- Tenant/branch isolation on every surface (`.claude/rules/03`; `../security/PILOT_DATA_BOUNDARY.md`).
- Customer feedback and reviews are untrusted input and MUST NOT drive tool calls (`.claude/rules/04`, `05`).
- Every material action is audited and truthfully stated (`.claude/rules/07`, `10`).
- No success state before external verification (Persona §14.1; `PILOT_WORKFLOW_STATES.md`).
