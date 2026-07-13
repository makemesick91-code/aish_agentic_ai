# WhatsApp Invitation — Pilot Baseline

**Document:** WhatsApp Invitation Pilot Baseline
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Nature of this document

This is an **invitation baseline**, not an implemented integration. No messaging provider, link
tokenizer, scheduler, or delivery tracker exists. The first pilot uses a **unique survey link
shared over WhatsApp** (and QR fallback) — it does **not** use automated WhatsApp Business Platform
delivery, which is deferred to P2 (Persona §9.3). Nothing here claims a live messaging integration.

- Canonical Step 2 source: [`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) §6 (survey), §7 (invitation/eligibility), UC-P0-04 (create/send), UC-P0-05 (customer fills feedback), §4.1 (Customer/Wali persona).
- Event trigger contract: [`DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md).
- Rule authority: [`../../.claude/rules/08-architecture-and-event-workflows.md`](../../.claude/rules/08-architecture-and-event-workflows.md), [`../../.claude/rules/04-security-privacy-and-secrets.md`](../../.claude/rules/04-security-privacy-and-secrets.md), [`../../.claude/rules/03-multi-tenant-and-branch-isolation.md`](../../.claude/rules/03-multi-tenant-and-branch-isolation.md), [`../../.claude/rules/10-ui-ux-and-truthful-states.md`](../../.claude/rules/10-ui-ux-and-truthful-states.md).
- Canonical map: Master Source §35 (invitation events), §39 (integration), §53 (truthful states), §16.2 (equal review access).

---

## 1. Channel model

| Channel | Role in first pilot | Notes |
|---------|--------------------|-------|
| Unique survey link over **WhatsApp** | Primary | Link is shared via a lawful WhatsApp path; not automated platform delivery |
| **QR code** / public campaign link | **Mandatory fallback** | Attribution to branch/campaign without exposing customer identifier |
| **Email** | Optional | Only when a lawful, consented address exists |

Automated WhatsApp Business Platform delivery is **P2** (Persona §9.3) and MUST NOT be enabled in
the first pilot. A **lawful communication path** or on-site QR is required for eligibility
(Persona §7.1).

---

## 2. Unique per-invitation survey link

- Each invitation carries a **unique**, **hard-to-guess**, **scoped**, **expiring** token
  (Persona §6.3, §7). One response per invitation unless a controlled correction flow exists
  (Persona §6.3).
- The token MUST be tenant/branch-scoped and MUST NOT be enumerable or reusable across customers
  (Rule 03; no cross-tenant leakage). No login/account is required (Persona §4.1, §6.3).
- The link MUST NOT embed diagnosis, medical history, or sensitive identifiers (Persona §8.2).
- Consent and privacy notice MUST be visible before submit (Persona §6.3).

---

## 3. Eligibility (from event to sendable invitation)

An invitation may be created when (Persona §7.1):

- Visit/service status is `completed` and the event resolves to the correct tenant + pilot branch.
- The record is not cancelled, test, duplicate, or internal-staff data.
- A lawful communication path or on-site QR exists.
- For minors, communication is routed to the configured **guardian/contact** (see §8).

---

## 4. Timing, frequency, reminder, expiry (pilot defaults)

| Setting | Default | Source |
|---------|---------|--------|
| Trigger | `VisitCompleted` | Persona §7.2 |
| Delay after completion | 60 min; configurable **30–120 min** | Persona §7.2 |
| Sending window | **09:00–20:00 Asia/Makassar**; out-of-window events scheduled to next window | Persona §7.2, §7.2 note |
| Frequency cap | **1 invitation / customer / 14 calendar days** | Persona §7.2 |
| Reminder | **Max 1**, after 24h | Persona §7.2 |
| Expiration | **7 days** | Persona §7.2 |
| Opt-out | **Mandatory** | Persona §7.2 |

- Scheduling and any external send MUST run on the queue with tenant context (Rule 08; Rule 03).
- The frequency cap and window are enforcement rules, not display hints; a duplicate or
  out-of-window send MUST NOT occur (Persona §14.1: no duplicate invitation from retry).

---

## 5. Truthful delivery states

Delivery state vocabulary (Persona UC-P0-04; Master Source §53; Rule 10):

```
queued → sent → delivered → failed → expired
```

- A state MUST reflect the real disposition; the UI MUST NOT show `delivered`/`sent` when the
  provider has not confirmed it (Rule 10; Persona §14.1).
- Each invitation records channel, failure reason (when failed), and intake source
  (integrated vs. manual/QR) for reconciliation.
- `expired` applies after the 7-day window with no response.

---

## 6. Opt-out and consent

- Opt-out is mandatory and MUST be honored: an opted-out customer MUST NOT be re-contacted
  (Persona §7.2, §10.2). Consent state is stored with the response (Persona §8.1).
- Recovery follow-up contact (a separate flow) also respects consent and the contact policy
  (Persona §10.2) and is not part of automated invitation sending.

---

## 7. QR / email fallbacks

- **QR (mandatory fallback):** records branch/campaign attribution without exposing a customer
  identifier (Persona §7.3). Used on-site when no lawful per-customer channel exists.
- **Email (optional):** only with a lawful, consented address; encrypted at rest if stored
  (Persona §8.1). Same delivery-state truthfulness applies.

---

## 8. Minors / guardian handling

- For customers who are minors, invitation and any follow-up MUST be directed to the configured
  **guardian/lawful contact**, never to the minor directly by default (Persona §7.1). The
  `guardian_flag` on the event contract signals this routing (see event contract §4).

---

## 9. No review gating in the invitation flow

- Any Google Review link presented in the survey/thank-you flow MUST be **neutral** and **equal**
  for all eligible respondents regardless of CSAT/score (Persona §6.3, §12; Master Source §16.2).
- The flow MUST NOT request a specific rating, route only satisfied customers to Google, or
  withhold review access by sentiment (Rule 06).

---

## 10. Baseline acceptance expectations (for Step 3 / pilot readiness)

Mapped to UC-P0-04 / UC-P0-05 acceptance (Persona §9.1), validated by planned tests only:

1. Unique scoped expiring token; not enumerable; one-response enforcement.
2. Frequency cap, sending window, 7-day expiry, single-reminder enforced.
3. Truthful delivery states with recorded failure reasons.
4. Opt-out honored; consent stored.
5. QR fallback attributes without exposing customer identity.
6. Guardian routing for minors.
7. No automated WhatsApp Business Platform delivery (P2 deferred).

Open dependency: provider decisions for WhatsApp/email are unresolved (Persona §20 item 12) and
require a Step 3 ADR before implementation.

**Status:** Invitation baseline documented. Messaging integration NOT STARTED. No message has been
sent.
