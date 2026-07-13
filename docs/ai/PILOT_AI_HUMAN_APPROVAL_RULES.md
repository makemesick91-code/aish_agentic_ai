# Pilot AI Human-Approval Rules — Aish Agentic AI

- **Document:** Enforceable Human-Approval Rules for the Pilot
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §9 (UC-P0-06..13, UC-P0-16), §11, §12; Master Source §23–§33, §44; PRD §12, §13.
Rules: [`05-ai-governance-and-human-approval`](../../.claude/rules/05-ai-governance-and-human-approval.md),
[`06-google-review-policy`](../../.claude/rules/06-google-review-policy.md).
Cross-reference: [`HUMAN_APPROVAL_MATRIX.md`](./HUMAN_APPROVAL_MATRIX.md).

## 1. Purpose

Fix, in enforceable terms, when the pilot requires a human decision before an action, which agents may only
**suggest** versus which actions need approval, and the mandatory AI-governance controls (kill switch,
idempotent retry, tool allowlisting, structured output). Extends the foundation
[`HUMAN_APPROVAL_MATRIX.md`](./HUMAN_APPROVAL_MATRIX.md) for the pilot; it does not weaken it.

## 2. Full approval-trigger list (Master Source §33 / PRD §13)

Human approval is **mandatory before the action** for every trigger below:

- 1-star and 2-star review replies.
- Legal risk / legal statement; medical risk; safety issue; threat; discrimination; fraud allegation;
  potential viral issue.
- Personal data present (PII).
- Refund / discount / compensation.
- Data deletion.
- Admission of fault.
- Low AI confidence.
- Policy conflict.
- Repeated customer contact.
- Critical knowledge-base change.
- **Any** Google Review reply publication (MVP/pilot — public action).

## 3. Suggest-only agents vs approval-gated actions

- The following agents/steps MAY act as **suggest-only** and MAY be automated early (Master Source §33):
  sentiment classification, topic classification, summary, severity suggestion, internal assignment, SLA
  calculation, reminders, draft creation, duplicate detection, spam detection, internal insight. Their
  output is a suggestion, not an executed external action.
- The **Google Review Response agent** MAY only draft a reply; publication is an approval-gated action for
  the Reputation Approver ([`../security/PILOT_PUBLIC_REPLY_SAFETY.md`](../security/PILOT_PUBLIC_REPLY_SAFETY.md)).
- The **Recovery agent** MAY suggest severity/assignment/next action; contacting the customer, promising
  compensation, deletion, or any §33 trigger requires human approval.
- The supervisor MUST NOT bypass approval for any sensitive action (Master Source §23–§33).

## 4. Approval-flow table

| Action | Trigger | Approver role | Evidence |
|---|---|---|---|
| Publish Google Review reply | Any reply (MVP) + 1–2 star / medical / PII / legal | Reputation Approver | Approval record, approver identity, final text, publish result |
| Private customer contact (recovery) | Negative feedback / recovery ticket | Recovery Assignee / CS (per policy) | Ticket note, contact-attempt log, template used |
| Repeated customer contact | Second+ contact attempt | Recovery Assignee + supervisor | Contact history, approval note |
| Refund / discount / compensation | Compensation proposed | Authorized approver per threshold (Persona §11) | Approval + monetary authority record |
| Data deletion | Deletion request | Corporate Admin / owner | Deletion request record, audit entry |
| Legal statement / admission of fault | Present in draft | Owner / designated approver | Approval record, reviewed text |
| Low-confidence AI classification | Confidence below threshold | CX user (manual review) | Manual classification, audit entry |
| Critical knowledge-base change | KB edit affecting policy/reply | Corporate Admin | Change record, approval |

All rows produce audit evidence (Persona §15). No row is auto-executed by AI.

## 5. Mandatory AI-governance controls

- **Kill switch:** a control MUST exist to halt AI and automated external actions immediately.
- **Idempotent / controlled retry:** retries MUST NOT create duplicate external side-effects (no duplicate
  invitation, ticket, or public reply); success is claimed only after provider verification (Persona §14.1).
- **Tool allowlisting & argument validation:** tools MUST be allowlisted and every tool argument validated;
  customer content MUST NOT determine which tool is called or its arguments (Master Source §44).
- **Structured output:** agents MUST produce structured output that passes the guardrail before any action;
  invalid structured output MUST block the action.
- **Untrusted input:** survey answers and review text are untrusted; they are classified, never obeyed
  ([`../security/PROMPT_INJECTION_DEFENSE.md`](../security/PROMPT_INJECTION_DEFENSE.md)).
- **Tracing & cost:** model version, prompt version, tool calls, and cost MUST be logged
  ([`AI_COST_AND_TRACING.md`](./AI_COST_AND_TRACING.md)).
- **Manual fallback:** classification, triage, and approval MUST remain usable when AI is unavailable
  ([`PILOT_MANUAL_FALLBACK.md`](./PILOT_MANUAL_FALLBACK.md)).

## 6. Supersession

Approval requirements are permanent. Relaxation (e.g. any auto-publish) requires all Master Source §16.4
preconditions and a Master Source update
([`12-documentation-living-source-versioning`](../../.claude/rules/12-documentation-living-source-versioning.md)).

## Related documents

[`HUMAN_APPROVAL_MATRIX.md`](./HUMAN_APPROVAL_MATRIX.md) ·
[`AGENTIC_ARCHITECTURE.md`](./AGENTIC_ARCHITECTURE.md) ·
[`PILOT_AI_EVALUATION_PLAN.md`](./PILOT_AI_EVALUATION_PLAN.md) ·
[`PILOT_MANUAL_FALLBACK.md`](./PILOT_MANUAL_FALLBACK.md) ·
[`../security/PILOT_PUBLIC_REPLY_SAFETY.md`](../security/PILOT_PUBLIC_REPLY_SAFETY.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md).
