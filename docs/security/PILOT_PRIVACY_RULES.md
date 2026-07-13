# Pilot Privacy Rules — Aish Agentic AI

- **Document:** Privacy-by-Design Rules for the Healthcare Pilot
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §8, §10.2, §15; Master Source §37, §42, §43, §44; PRD §14, §15.2.
Rules: [`04-security-privacy-and-secrets`](../../.claude/rules/04-security-privacy-and-secrets.md),
[`07-data-governance-and-audit`](../../.claude/rules/07-data-governance-and-audit.md).

## 1. Purpose

Encode privacy-by-design controls for a healthcare pilot where the tenant operates a dental clinic
(Daengtisia) and customers may be patients or their guardians. These rules complement the field-level
[`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md) and the foundation
[`PRIVACY_AND_PII.md`](./PRIVACY_AND_PII.md).

## 2. PII minimization and classification

- The pilot MUST collect only the allowed fields in Persona §8.1; every field MUST be classified
  (identifier, contact, consent, operational, feedback, integration) and minimized.
- Personal, medical, financial, and sensitive-transaction data MUST NOT appear in public output and MUST
  NOT be sent to an AI provider except as explicitly allowed and redacted (Master Source §43).
- Pseudonymous customer references SHOULD be preferred over any direct identifier. A real medical record
  number MUST NOT be used as a customer reference.

## 3. Consent capture and opt-out

- Invitation send MUST be gated on a recorded consent/opt-out state; an opted-out customer MUST NOT receive
  an invitation or follow-up (Persona §7.2, §8.1).
- Opt-out MUST be honored on all channels and MUST be auditable. Opt-out MUST NOT reduce a customer's
  eligibility for equal Google Review access (no gating — Master Source §16.2;
  [`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md)).
- Consent and opt-out changes MUST be timestamped and retained per the retention configuration.

## 4. Guardian and minor handling

- Where the patient is a minor, the invitation/contact MUST be directed to the recorded guardian/contact,
  not the minor, and only encrypted contact detail MAY be stored (Persona §8.1).
- The pilot MUST NOT collect a minor's clinical data (see prohibited fields, Persona §8.2); guardian
  relationship MUST be treated as sensitive and MUST NOT appear in public output.
- Where local lawful basis for contacting a minor's guardian is unclear, contact MUST be treated as an
  exception requiring privacy/security review (see §9).

## 5. Redaction before AI

- Free-text survey answers and Google review text are untrusted input and MUST be passed through redaction
  before any AI provider call; detected prohibited fields (Persona §8.2) MUST be removed or masked.
- AI retrieval/RAG context MUST be tenant/branch-scoped and limited to the minimum relevant content
  (Master Source §42). Customer content MUST NOT determine tool calls
  ([`PROMPT_INJECTION_DEFENSE.md`](./PROMPT_INJECTION_DEFENSE.md)).

## 6. Controlled AI-output storage

- AI output storage MUST be controlled: model version, prompt version, tool calls, and cost MUST be
  recorded ([`../ai/AI_COST_AND_TRACING.md`](../ai/AI_COST_AND_TRACING.md)).
- Stored AI output MUST NOT reintroduce prohibited fields; guardrail validation applies to output as well
  as input.

## 7. No real customer PII in repository or evidence

- The repository, tests, fixtures, and evidence artifacts MUST NOT contain real customer PII, real medical
  data, secrets, or tokens. Adversarial and evaluation datasets MUST use synthetic data
  ([`../ai/PILOT_AI_EVALUATION_PLAN.md`](../ai/PILOT_AI_EVALUATION_PLAN.md)).
- `scripts/docs/secret-scan.sh` MUST pass; committed evidence MUST be redacted.

## 8. Retention, disconnect, and deletion

- Data retention MUST be configurable per tenant; retention and deletion MUST be auditable and audit
  history MUST NOT be deletable (Master Source §37).
- The tenant MUST be able to disconnect Google and delete its Google credentials; deletion MUST revoke and
  purge tokens ([`../integrations/google/OAUTH_AND_TOKEN_SECURITY.md`](../integrations/google/OAUTH_AND_TOKEN_SECURITY.md)).
- Data export and deletion MUST be permissioned, audited, and tested before any production GO
  ([`09-testing-and-quality-gates`](../../.claude/rules/09-testing-and-quality-gates.md)).

## 9. Exception process

Any collection or processing beyond §8.1 (including a prohibited field or a new lawful-basis question) MUST
go through privacy/security review, establish lawful basis, apply data minimization, and be recorded in a
Master Source update before use (Persona §8.2). No exception is authorized by chat history.

## 10. Mapping

| Control | Master Source | PRD | Persona |
|---|---|---|---|
| Public-output prohibition (healthcare) | §43 | §15.2 | §8.2, §12 |
| Data minimization / classification | §37, §42 | §14 | §8.1 |
| Consent / opt-out | §37 | §14 | §7.2, §8.1 |
| Redaction before AI | §43, §44 | §15.2 | §8.2 |
| Retention / disconnect / deletion | §37 | §14 | §8, §15 |
| Untrusted input | §44 | §15.2 | §4.10 |

## Related documents

[`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md) ·
[`PILOT_PUBLIC_REPLY_SAFETY.md`](./PILOT_PUBLIC_REPLY_SAFETY.md) ·
[`PILOT_THREAT_AND_ABUSE_CASES.md`](./PILOT_THREAT_AND_ABUSE_CASES.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) ·
[`../canonical/MASTER_SOURCE.md`](../canonical/MASTER_SOURCE.md).
