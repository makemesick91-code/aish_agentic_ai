# Pilot Manual Fallback — Aish Agentic AI

- **Document:** Manual Fallback When AI or an External Provider Fails — Pilot
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona UC-P0-16, UC-P0-06, §7.3, §14.1; Master Source §34, §53; PRD §16, §21.
Rules: [`05-ai-governance-and-human-approval`](../../.claude/rules/05-ai-governance-and-human-approval.md),
[`10-ui-ux-and-truthful-states`](../../.claude/rules/10-ui-ux-and-truthful-states.md).

## 1. Purpose and principle

The pilot workflow MUST remain usable when AI is unavailable or an external provider (LLM, Google, email,
WhatsApp, DaengtisiaMS) fails. Basic functions MUST NOT depend on AI (Master Source §34). The operating
model is manual → semi-automated → approved automation; AI is assistance, never a single point of failure
(Persona UC-P0-16).

## 2. Fallback per critical workflow

| Workflow | Normal (AI/provider) | Manual fallback | Truthful state on failure |
|---|---|---|---|
| Feedback classification (UC-P0-06) | AI sentiment/topic/severity/summary suggestion | CX user classifies and sets severity manually | AI step marked failed/unavailable; feedback still triaged |
| Triage & assignment (UC-P0-08) | AI-suggested assignment/SLA | Manual assignment and SLA per policy | Suggestion absent; manual assignment recorded |
| Recovery contact (UC-P0-09) | Draft assist | Manual contact using approved templates under approval | Contact-attempt logged; no false "resolved" |
| Review reply drafting (UC-P0-13) | AI draft | Human writes reply; still requires approval before publish | `Publication failed` if Google API errors; never `Published` |
| Invitation delivery (UC-P0-04) | Signed API/webhook integration | CSV/manual import or on-site QR survey (Persona §7.3) | Shown as manual, never as real-time integration success |
| Audit / evidence (UC-P0-15) | Automated capture | Manual notes still recorded to audit | Audit continues regardless of AI state |

## 3. Truthful failure states

- The system MUST NOT display success when the underlying or external action has not verified (Master
  Source §53). Failed publish stays `Publication failed`; failed delivery stays failed
  ([`10-ui-ux-and-truthful-states`](../../.claude/rules/10-ui-ux-and-truthful-states.md)).
- Manual/CSV import and QR fallback MUST be labeled as manual in analytics and audit and MUST NOT be
  presented as real-time integration success (Persona §7.3).
- Empty, loading, failure, and permission-denied states MUST be modeled for every AI-assisted surface.

## 4. Kill switch, idempotency, and retry

- The kill switch MUST allow operators to stop AI/automated actions while keeping manual workflows usable.
- Retry after any failure MUST be idempotent — no duplicate invitation, ticket, or public reply (Persona
  §14.1; [`PILOT_AI_HUMAN_APPROVAL_RULES.md`](./PILOT_AI_HUMAN_APPROVAL_RULES.md)).
- On provider failure, external success MUST NOT be claimed until the provider verifies the action.

## 5. Audit continuity

Audit logging MUST continue during any AI or provider outage: manual classifications, assignments,
contacts, approvals, and fallback imports MUST all be recorded, so the pilot scorecard and evidence remain
reproducible by the Read-only Analyst/Auditor (Persona §4.7, §15). Audit history MUST NOT be deletable.

## 6. Mapping

| Concern | Master Source | PRD | Persona |
|---|---|---|---|
| AI-unavailable operation | §34 | §21 | UC-P0-16, UC-P0-06 |
| Truthful states | §53 | §16 | §7.3, §14.1 |
| Idempotent retry / no duplicate | §53 | §16 | §14.1 |
| Fallback channels (CSV/QR) | §34 | §21 | §7.3 |

## Related documents

[`PILOT_AI_HUMAN_APPROVAL_RULES.md`](./PILOT_AI_HUMAN_APPROVAL_RULES.md) ·
[`PILOT_AI_EVALUATION_PLAN.md`](./PILOT_AI_EVALUATION_PLAN.md) ·
[`AGENTIC_ARCHITECTURE.md`](./AGENTIC_ARCHITECTURE.md) ·
[`../security/PILOT_PUBLIC_REPLY_SAFETY.md`](../security/PILOT_PUBLIC_REPLY_SAFETY.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md).
