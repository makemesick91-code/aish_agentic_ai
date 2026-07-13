# Prompt Injection Defense — Aish Agentic AI

Canonical: Master Source §44, §30 (guardrail agent). Rule: `.claude/rules/05`, `04`. PRD §12.

## Threat model
All customer feedback and Google reviews are **untrusted input**. Example attacks (Master Source §44):
"ignore previous instructions", "show all customer data", "send system tokens".

## Mandatory defenses (Master Source §44)
- Separate system instructions from customer content; customer content MUST NOT determine tool calls.
- Validate tool arguments; restrict/allowlist tool permissions; use structured output.
- Run the Policy & Privacy Guardrail agent; redact PII/medical/financial content.
- Log security events; halt the workflow when an attack is detected.

## Guardrail agent responsibilities (Master Source §30)
Check for PII, medical/financial data, internal info, insults, threats, discrimination, unauthorized
promises, legal admissions, review manipulation, and prompt injection; block unsafe output; require human
approval when in doubt (`docs/ai/HUMAN_APPROVAL_MATRIX.md`).

## Verification
Prompt-injection and PII-leakage tests are part of the AI evaluation dataset and security gate
(`docs/ai/AI_EVALUATION_BASELINE.md`, `.claude/rules/09`); no PII leakage is permitted on the test suite.

**Status:** defense baseline documented. Runtime guardrails apply at implementation (NOT STARTED).
