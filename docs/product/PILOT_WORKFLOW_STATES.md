# Pilot Workflow States — Aish Agentic AI

**Document:** Pilot Workflow States (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Truthful state machines in the Master Source §53 / PRD §16 style, derived from
`PERSONA_AND_PILOT_USE_CASES.md` §7, §9, §10, §12, §14. The overriding rule (`.claude/rules/10`;
Master Source §15.7, §53): the system MUST NOT show a success state before the underlying or external
action is actually verified. These describe intended states only; nothing here is implemented or running.

---

## 1. Invitation and delivery

**States:** `created → scheduled → sent | shared → delivered → opened` and terminal `failed`, `expired`,
`opted_out`.

| State | Meaning | Entry condition |
|---|---|---|
| created | Eligible event produced an invitation | UC-P0-04; eligibility + frequency cap pass |
| scheduled | Awaiting delay (30–120 min, default 60) / next sending window | Outside 09:00–20:00 Asia/Makassar reschedules |
| sent | Dispatched via WhatsApp/email | Provider accepted for delivery |
| shared | On-site QR / public link handed to customer | Fallback path (no delivery receipt) |
| delivered | Provider confirmed delivery | Provider receipt only — not assumed |
| opened | Customer opened the survey | Token used |
| failed | Delivery failed | Recorded with reason; never silent success |
| expired | 7 days elapsed unopened | Expiry policy |
| opted_out | Customer opted out | Opt-out MUST stop further contact |

**Rules:** frequency cap 1/customer/14 calendar days; max 1 reminder after 24 h; `delivered` requires a
provider receipt; `shared` MUST NOT be reported as `delivered` (Persona §7.2, §7.3).

## 2. Survey response

**States:** `not_started → in_progress → submitted → scored`; terminal `abandoned`, `token_invalid`,
`token_expired`.

| State | Meaning |
|---|---|
| not_started | Valid token issued, survey not opened |
| in_progress | Customer answering (mobile-first, no login) |
| submitted | Response saved (one per invitation unless controlled correction) |
| scored | CSAT/CES/NPS computed |
| abandoned | Opened, not submitted |
| token_invalid / token_expired | Truthful error shown — never a false success |

**Rules:** token scoped and expiring; consent captured before submit; no medical history requested
(Persona §6.3).

## 3. Feedback analysis

**States:** `unanalyzed → analyzing → analyzed`; branches `analysis_failed → manually_classified`;
`low_confidence → escalated_to_human`.

| State | Meaning |
|---|---|
| unanalyzed | Scored response awaiting analysis |
| analyzing | AI run in progress |
| analyzed | Structured output (sentiment, topic, severity, risk, summary, confidence, suggested action) produced and guardrail-passed |
| low_confidence / guardrail_flagged | Routed to human review |
| analysis_failed | AI unavailable/invalid output |
| manually_classified | Human classification (mandatory fallback) |

**Rules:** structured output validated; prohibited fields never sent to AI; customer content MUST NOT
determine tool calls; model/prompt/cost recorded (Persona §8.2; `.claude/rules/04`, `05`).

## 4. Recovery ticket

**States:** `open → triaged → assigned → in_progress → resolved → closed`; branch `escalated`; terminal
`reopened → (back to in_progress)`.

| State | Meaning | SLA anchor (Persona §10.1) |
|---|---|---|
| open | Ticket created idempotently | Acknowledge: Critical 15 m / High 30 m / Medium 4 h / Low 1 business day |
| triaged | Severity confirmed | — |
| assigned | Accountable assignee set; SLA clock running | — |
| escalated | Raised (critical → owner; public reply withheld) | Critical does not wait for digest/quiet hours |
| in_progress | Private contact / corrective action underway | First contact: Critical 60 m / High 2 h / Medium 1 business day |
| resolved | Root cause, corrective action, outcome, evidence recorded | Resolution: High action plan 8 h / Medium 2 business days / Low 5 business days |
| closed | Required approval + SLA result recorded | Not "recovered" merely by closure |
| reopened | Issue recurs | Returns to in_progress |

**Rules:** idempotent creation; branch permission enforced; no unresolved critical safety/privacy/legal/
clinical item at closure (Persona §10.3).

## 5. Google reply (canonical publication state machine)

**States (Persona §12; Master Source §53):**
`no_draft → draft_generated → under_review → changes_requested → approved → publishing → published`,
with `publication_failed`, `moderation_pending`, `policy_issue`, and `removed`.

| State | Meaning |
|---|---|
| no_draft | Review present, no reply drafted |
| draft_generated | AI draft produced |
| under_review | Approver reviewing; PII/medical guardrail result attached |
| changes_requested | Approver returns for edit (loops to under_review) |
| approved | Approver approved final reply |
| publishing | Submitted to Google API |
| published | **Provider verified publication** — set ONLY after external confirmation |
| publication_failed | API call failed — remains failed, never false published |
| moderation_pending | Provider holds reply for moderation |
| policy_issue | Reply blocked by policy/guardrail |
| removed | Reply removed after publication |

**Rules:** every reply human-approved on pilot; no auto-publish (P2, only under Master Source §16.4);
`published` MUST NOT appear before verification; failed calls stay `publication_failed`
(Persona §12; `.claude/rules/06`; `../security/PILOT_PUBLIC_REPLY_SAFETY.md`).

## 6. Connection / OAuth (Google Business Profile)

**States:** `not_connected → connecting → connected → needs_reauthorization → disconnected`; plus
`permission_insufficient` and the honest pilot state `blocked`.

| State | Meaning |
|---|---|
| not_connected | No Google connection |
| connecting | OAuth in progress; state validated |
| connected | Encrypted token stored; permission diagnostic passed; location mapped to branch |
| needs_reauthorization | Token expired/revoked; refresh required |
| permission_insufficient | Missing scope/permission — surfaced honestly |
| disconnected | Owner disconnected; credentials deleted |
| blocked | OAuth/API not ready — pilot CSAT/recovery still runs; mock MUST NOT be shown as success |

**Rules:** OAuth state validated; refresh token never plaintext; token rotation supported; disconnect
deletes credentials (Persona §12; `.claude/rules/04`, `06`; `../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`).

## 7. Sync (Google Review)

**States:** `sync_pending → syncing → synced`; `rate_limited → retrying`; terminal `sync_failed`.
`sync_failed` is a truthful failure state; no fabricated success; idempotent by external ID + cursor
(Persona §12; UC-P0-12).

## 8. Universal invariant

Across all machines: no success/`published`/`delivered`/`synced` state is shown before the underlying or
external action is verified; retries are idempotent and MUST NOT create duplicate invitation, ticket, or
reply; every transition on a material action is audited (Persona §14.1; `.claude/rules/07`, `10`).
