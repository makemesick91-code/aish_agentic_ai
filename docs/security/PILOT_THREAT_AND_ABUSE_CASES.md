# Pilot Threat and Abuse Cases — Aish Agentic AI

- **Document:** Threat / Abuse-Case Catalog and Mitigations — Pilot
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §4.10, §7.3, §8, §12, §14.1, §17; Master Source §43, §44, §50; PRD §15, §23.
Rules: [`04-security-privacy-and-secrets`](../../.claude/rules/04-security-privacy-and-secrets.md),
[`03-multi-tenant-and-branch-isolation`](../../.claude/rules/03-multi-tenant-and-branch-isolation.md),
[`05-ai-governance-and-human-approval`](../../.claude/rules/05-ai-governance-and-human-approval.md).

## 1. Purpose

Catalog the concrete threat and abuse cases for the healthcare pilot and their required mitigations. This
extends the foundation [`THREAT_MODEL_BASELINE.md`](./THREAT_MODEL_BASELINE.md) with pilot-specific
vectors (DaengtisiaMS integration, healthcare data, Google Review approval). These are controls to design
and test; nothing here is implemented yet (`APPLICATION IMPLEMENTATION NOT STARTED`).

## 2. Threat / abuse-case catalog

| Threat | Vector | Impact | Mitigation | Related rule |
|---|---|---|---|---|
| Cross-tenant access / IDOR | Guessable/forged record IDs in API or export; missing tenant scope | Cross-tenant medical/PII exposure | Every query tenant/branch-scoped; deny-by-default authz; object-level checks; cross-tenant & IDOR tests must pass | [`03`](../../.claude/rules/03-multi-tenant-and-branch-isolation.md) |
| Broken access control / privilege escalation | Branch user reaching another branch; role tampering; horizontal/vertical escalation | Unauthorized data or actions | RBAC + branch scoping; least privilege; server-side enforcement; privilege-escalation tests | [`04`](../../.claude/rules/04-security-privacy-and-secrets.md) |
| OAuth / token leakage | Tokens in logs, repo, error traces; unencrypted refresh token | Google account takeover, review abuse | Encrypt tokens at rest; no refresh token in plaintext; validate OAuth state; rotation; no secrets in logs/repo; secret scan | [`04`](../../.claude/rules/04-security-privacy-and-secrets.md), [`06`](../../.claude/rules/06-google-review-policy.md) |
| Prompt injection via feedback/review | Malicious instructions embedded in survey answer or review text | Guardrail bypass, unsafe tool call, data leak | Treat input as untrusted; input never drives tool calls; tool allowlist + arg validation; guardrail agent; injection test set | [`05`](../../.claude/rules/05-ai-governance-and-human-approval.md) |
| PII / medical exfiltration via AI | Sensitive data routed into prompt or emitted in output/public reply | Healthcare privacy breach | Redaction before AI; prohibited-field boundary; output guardrail; zero-leakage AI gate; prohibited-field test | [`04`](../../.claude/rules/04-security-privacy-and-secrets.md) |
| Webhook forgery / replay from DaengtisiaMS | Unsigned/forged/replayed `VisitCompleted` events | Fake eligibility, spam invitations, poisoned data | Verify webhook signature; reject unsigned; nonce/timestamp anti-replay; idempotency key on event ID; tenant-scoped intake | [`08`](../../.claude/rules/08-architecture-and-event-workflows.md) |
| Invitation abuse / spam / frequency-cap bypass | Repeated or duplicate invitations; cap evasion | Customer harassment, reputational harm | Consent/opt-out gate; frequency cap; dedupe on service event ID; repeated-contact triggers approval | [`06`](../../.claude/rules/06-google-review-policy.md) |
| Duplicate external action via retry | Non-idempotent retry of invitation, ticket, or reply | Duplicate messages / public replies | Idempotency keys; controlled retry with no duplicate side-effects; provider-verified success only | [`05`](../../.claude/rules/05-ai-governance-and-human-approval.md) |
| Review gating pressure | Staff/owner asks to route only happy customers or request 5 stars | Policy violation, Google TOS risk | Hard anti-gating rule; equal access for all eligible; no rating request/incentive; documented prohibition | [`06`](../../.claude/rules/06-google-review-policy.md) |
| Staff over-promising compensation | CS promises refund/discount outside authority in public or private contact | Financial/legal exposure | Compensation requires human approval; authority thresholds; approval logged; no AI auto-compensation | [`05`](../../.claude/rules/05-ai-governance-and-human-approval.md) |
| Falsified success state | UI shows `Published`/success before provider confirms | Untruthful state, audit corruption | Truthful state vocabulary; `Publication failed` on API error; success only after provider verification | [`10`](../../.claude/rules/10-ui-ux-and-truthful-states.md) |

## 3. Cross-cutting requirements

- Every mitigation above MUST be covered by a test category from Master Source §50 (multi-tenant, security,
  AI evaluation) before any release gate can pass ([`09`](../../.claude/rules/09-testing-and-quality-gates.md));
  see [`../ai/PILOT_AI_EVALUATION_PLAN.md`](../ai/PILOT_AI_EVALUATION_PLAN.md) and the Step 2 traceability
  matrix [`../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`](../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md).
- A kill switch MUST exist to halt AI/automated actions; incident logging and security alerting MUST be in
  place (Master Source §43).
- Untrusted-input handling and PII/medical exfiltration controls are detailed in
  [`PROMPT_INJECTION_DEFENSE.md`](./PROMPT_INJECTION_DEFENSE.md), [`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md),
  and [`PILOT_PRIVACY_RULES.md`](./PILOT_PRIVACY_RULES.md).

## 4. Mapping

| Category | Master Source | PRD | Persona |
|---|---|---|---|
| Prompt injection / untrusted input | §44 | §15.2 | §4.10 |
| Healthcare data protection | §43 | §15.2 | §8.2 |
| Security test coverage | §50 | §23 | §14.1, §19 |
| Idempotency / truthful state | §53 | §16 | §14.1 |
| Webhook / event integrity | §35, §39 | §18.2 | §7.3, §4.10 |

## Related documents

[`THREAT_MODEL_BASELINE.md`](./THREAT_MODEL_BASELINE.md) ·
[`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md) ·
[`PILOT_PRIVACY_RULES.md`](./PILOT_PRIVACY_RULES.md) ·
[`PILOT_PUBLIC_REPLY_SAFETY.md`](./PILOT_PUBLIC_REPLY_SAFETY.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md).
