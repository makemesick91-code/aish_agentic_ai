# AI Guardrail and Approval Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §16, §33, §44 · PRD v1.2.0 §13, §16 · **Rules:** `.claude/rules/05`, `06`, `18`, `20` ·
**ADR:** [0019](../decisions/adr/0019-ai-provider-abstraction.md), [0028](../decisions/adr/0028-feature-flags-human-approval-kill-switches.md).

## Guardrails
- **Input**: redact PII, strip `MED`, treat customer content as untrusted (prompt-injection defense).
- **Output**: validate structured schema; block disclosure of personal/medical/sensitive-transaction data;
  block non-professional/defensive/attacking review replies (`.claude/rules/06`).
- **Tool**: allowlist + argument validation; customer content never selects a tool.
- Guardrail blocks are recorded as security events; a PII guardrail failure raises an alert (ADR 0024).

## Human approval (mandatory triggers — Master Source §33 / PRD §13)
Abridged list (the complete §33 set is in the Human Approval Matrix): 1–2★ reviews · legal/medical/safety/
fraud/discrimination risk · **threat** · **potential viral issue** · PII · refunds/discounts/compensation ·
data deletion · legal statements · low AI confidence · policy conflict · admission of fault · repeated customer
contact · **critical knowledge-base change**. **Every public Google reply is human-approved before publication.** See
[HUMAN_APPROVAL_MATRIX](HUMAN_APPROVAL_MATRIX.md) and [PILOT_AI_HUMAN_APPROVAL_RULES](PILOT_AI_HUMAN_APPROVAL_RULES.md).

## Anti-gating (permanent)
The system **MUST NOT** route only satisfied customers to Google Review, block review access by CSAT/sentiment,
request a specific rating, incentivize positive reviews, or fake reviews. All eligible customers get **equal**
access; sensitive cases route to a private channel (`.claude/rules/06`, `18`).

## Kill switch & fallback
A kill switch disables AI/external-effect classes without data loss; manual workflow remains usable (ADR 0016,
0028). Controlled retry is idempotent (no duplicate reply/ticket/invitation).

## Assertion
No guardrail, approval workflow, or kill switch runs in Step 3. This is the planned safety contract.
