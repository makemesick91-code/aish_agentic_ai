# Google Business Profile — Pilot Readiness

**Document:** Google Business Profile Pilot Readiness
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Nature of this document

This is a **pilot readiness baseline**, not an implemented integration. No OAuth client, token
store, sync worker, or reply-publishing path exists. Every readiness item in §10 is marked
**NOT VERIFIED / NOT STARTED**. Nothing here claims that Google access is connected or that any
reply has been published.

- Canonical Step 2 source: [`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) §12 (Google Review pilot rules), UC-P0-11 (connect), UC-P0-12 (sync), UC-P0-13 (draft/approve/publish), §4.6 (Reputation Approver).
- Policy: [`google/GOOGLE_REVIEW_POLICY.md`](google/GOOGLE_REVIEW_POLICY.md); OAuth/token: [`google/OAUTH_AND_TOKEN_SECURITY.md`](google/OAUTH_AND_TOKEN_SECURITY.md); readiness: [`google/INTEGRATION_READINESS.md`](google/INTEGRATION_READINESS.md).
- Rule authority: [`../../.claude/rules/06-google-review-policy.md`](../../.claude/rules/06-google-review-policy.md), [`../../.claude/rules/04-security-privacy-and-secrets.md`](../../.claude/rules/04-security-privacy-and-secrets.md), [`../../.claude/rules/05-ai-governance-and-human-approval.md`](../../.claude/rules/05-ai-governance-and-human-approval.md).
- Approval matrix: [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md).
- Canonical map: Master Source §16 (review rules), §38 (Google integration/workflows), §53 (truthful states).

---

## 1. Ownership and authorization prerequisites

- Only a Google account/location controlled by an **authorized Daengtisia representative** may be
  connected (Persona §12; Master Source §16). Aish MUST NOT connect an account it does not have
  authorized consent to manage.
- The pilot starts from **one** mapped Google location (Persona §12; §2.3). Multi-location is out of
  pilot scope (Persona §18) until first-branch evidence supports expansion.
- Ownership/authorization verification is a preparation-phase task (Persona §13.1) and an unresolved
  open decision (Persona §20 item 8).
- The current Google policy/API MUST be **re-verified before production** (Rule 06); today's baseline
  cannot assume Google API surface stability.

---

## 2. OAuth scopes and state validation

- Request the **minimum** scopes required to read reviews and post authorized replies for the mapped
  location; no broader scope than operationally necessary (data minimization, Rule 04).
- The OAuth `state` parameter MUST be generated, bound to the session, and **validated** on callback
  to defend against CSRF/authorization-code injection (Rule 04; OAuth token security doc).
- Connection workflow (baseline, from OAuth token security doc): connect → OAuth redirect → tenant
  grants → callback (`state` validated) → token encrypted → business account fetched → locations
  fetched → mapped to branch → initial sync → connection health recorded.

---

## 3. Token storage, rotation, and no plaintext refresh

- Access tokens MUST be **encrypted at rest**; refresh tokens MUST NOT be stored in plaintext
  (Rule 04; Master Source §43).
- Tokens MUST support **rotation and reauthorization**; credentials live in a secure secret store
  referenced by environment variables and are never committed.
- Tenants MUST be able to **disconnect** Google and delete stored credentials (Rule 07; Persona
  UC-P0-11 disconnect flow).

---

## 4. Single mapped location and permission diagnostics

- Exactly one Google location is mapped to the pilot branch for the first pilot (Persona §2.3, §12).
- **Permission diagnostics** MUST report whether the connected account actually holds the rights to
  read reviews and post replies for that location, surfacing a truthful `permission missing` state
  rather than failing silently (Master Source §53; UC-P0-11 acceptance).

---

## 5. Reauthorization and disconnect flows

- Connection states (baseline, Master Source §53): connected, expiring, reauthorization required,
  permission missing, syncing, sync failed, disconnected.
- On token/OAuth expiry (`GoogleConnectionExpired`, Event Catalog), the system MUST prompt
  reauthorization and alert (Rule 11), never present the connection as healthy.
- Disconnect MUST revoke/delete stored credentials and stop sync; audited (Rule 07).

---

## 6. Review sync (idempotent, rate-limited, truthful)

Per UC-P0-12 and Master Source §38, sync MUST use:

- **Incremental sync** with a persisted **cursor**.
- **Idempotency** keyed on external review IDs — re-sync MUST NOT duplicate reviews or replies.
- **Rate-limit handling** and **retry** with backoff for Google API quotas/errors.
- **External ID** capture linking Aish records to Google review/reply IDs.
- A **sync log** and **truthful failure state** — a failed sync is shown as `sync failed`, never as
  success (Persona §14.1; Master Source §53).
- Review content is **untrusted input** and MUST NOT determine system/tool behavior (Persona §12;
  Rule 05, [`../security/PROMPT_INJECTION_DEFENSE.md`](../security/PROMPT_INJECTION_DEFENSE.md)).

---

## 7. Human-approved reply publication

- **Every** Google Review reply MUST pass human approval before publication during the pilot
  (Persona §12, §4.6; Master Source §16.4). Auto-publish is prohibited unless all §16.4
  preconditions are met — out of pilot scope (Persona §18).
- Publication pipeline (baseline): review → AI analysis → draft → guardrail (PII/medical) → staff
  review → approver → send → API response recorded → publication state monitored.
- Reply states (Master Source §53 / PRD §16): no draft → draft generated → under review → changes
  requested → approved → publishing → published → publication failed → moderation pending → policy
  issue → removed. `Published` is set **only after provider verification** (Persona §14.1).
- On any Google API error, the reply MUST remain **`Publication failed`** (or an equivalent truthful
  state), never falsely `Published` (Persona §12; UC-P0-13 acceptance).
- Public replies MUST NOT disclose diagnosis, procedure, visit history, doctor-patient relation,
  payment-dispute detail, or other private facts (Persona §12; sensitive cases route to a private
  channel per [`google/GOOGLE_REVIEW_POLICY.md`](google/GOOGLE_REVIEW_POLICY.md)).
- **No review gating**: neutral review access/links, if shown, are equal for all eligible
  respondents regardless of score (Persona §6.3, §12; Master Source §16.2).

---

## 8. BLOCKED-status handling when access is unavailable

- If OAuth/API is not yet ready, the pilot CSAT/recovery workflow MUST still run with the Google
  scope explicitly marked **BLOCKED** (Persona §12, §17 risk row).
- A mock, stub, or sample connection MUST NOT be claimed as integration success (Persona §12;
  §14.1 hard gate: no external success before provider verification).
- BLOCKED is a truthful operating state, not a failure of the pilot; Google metrics simply do not
  apply until access is verified.

---

## 9. Pre-production re-verification

Before any production GO involving Google, the team MUST re-verify the current Google Business
Profile policy and API behavior (Rule 06), re-confirm authorized ownership, and re-test token
security, sync idempotency, and human-approval publication. This document does not authorize
production use.

---

## 10. Readiness checklist (all NOT VERIFIED / NOT STARTED)

| # | Readiness item | Canonical | Status |
|---|----------------|-----------|--------|
| 1 | Authorized Daengtisia ownership of Google account/location confirmed | §16, Persona §12 | NOT VERIFIED |
| 2 | Single pilot Google location identified and mapped to branch | §38, Persona §2.3 | NOT STARTED |
| 3 | OAuth client + minimum scopes defined | §38 | NOT STARTED |
| 4 | OAuth `state` generation/validation designed and tested | §38, Rule 04 | NOT STARTED |
| 5 | Encrypted token storage; no plaintext refresh; rotation | §43, Rule 04 | NOT STARTED |
| 6 | Permission diagnostics (read reviews / post replies) | §53 | NOT STARTED |
| 7 | Reauthorization + disconnect + credential deletion flows | §38, Rule 07 | NOT STARTED |
| 8 | Incremental sync with cursor + external IDs | §38 | NOT STARTED |
| 9 | Idempotent sync (no duplicate review/reply) | §38, §54 | NOT STARTED |
| 10 | Rate-limit handling + retry with backoff | §38 | NOT STARTED |
| 11 | Truthful sync failure state (`sync failed`) | §53 | NOT STARTED |
| 12 | Human-approved reply publication (100%) | §16.4, Rule 05/06 | NOT STARTED |
| 13 | `Published` only after provider verification | §53, Persona §14.1 | NOT STARTED |
| 14 | `Publication failed` retained on API error | §53, Persona §12 | NOT STARTED |
| 15 | PII/medical guardrail on public replies | §43, Rule 04/06 | NOT STARTED |
| 16 | No review gating / equal access | §16.2 | NOT STARTED |
| 17 | BLOCKED-status handling when access unavailable (mock ≠ success) | Persona §12 | NOT STARTED |
| 18 | Pre-production Google policy/API re-verification | Rule 06 | NOT STARTED |

Verification evidence, when it exists, will be produced during pilot preparation (Persona §13.1) and
recorded under `docs/evidence/` — none exists today.

**Status:** Readiness baseline documented. Google integration NOT STARTED. No account is connected;
no reply has been published.
