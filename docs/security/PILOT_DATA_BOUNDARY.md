# Pilot Data Boundary — Aish Agentic AI

- **Document:** Pilot Minimum-Data Boundary (healthcare pilot, Daengtisia)
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §8.1, §8.2, §7.3; Master Source §36, §37, §42, §43, §44; PRD §14, §15.2.
Rules: [`04-security-privacy-and-secrets`](../../.claude/rules/04-security-privacy-and-secrets.md),
[`03-multi-tenant-and-branch-isolation`](../../.claude/rules/03-multi-tenant-and-branch-isolation.md),
[`07-data-governance-and-audit`](../../.claude/rules/07-data-governance-and-audit.md).

## 1. Purpose and invariant

This document fixes the **minimum-data boundary** for the first healthcare pilot: which fields MAY exist
in the pilot at all, and — for each allowed field — which surfaces the field MAY flow to. The boundary is
data-minimization by construction (Persona §8; Master Source §42, §43).

**Invariant:** No field classified as prohibited in §3 MUST reach an AI provider prompt or any public
(Google Review) output. This is a hard safety gate (Persona §14.1). A violation is `NO-GO` until fixed and
re-tested.

Every pilot record MUST carry `tenant_id`, and every branch-relevant record MUST carry `branch_id`; the
boundary below is enforced **per tenant and per branch** on every surface (see
[`TENANT_ISOLATION.md`](./TENANT_ISOLATION.md)).

## 2. Allowed data (Persona §8.1) — field-to-surface flow

Surfaces: **Survey** (survey rendering/answers) · **Invite** (invitation delivery) · **AI** (AI provider
prompt / retrieval context) · **Reply** (public Google Review reply text) · **Analytics** (aggregated
dashboards) · **Export** (permissioned, audited export). `MUST NOT` means the field MUST NOT appear on that
surface even if technically available.

| Allowed field | Survey | Invite | AI | Reply | Analytics | Export | Notes |
|---|---|---|---|---|---|---|---|
| `tenant_id` | MUST (scope) | MUST | MUST (scope only) | MUST NOT | MUST (scope) | MAY | Isolation key, never in public text |
| `branch_id` | MUST (scope) | MUST | MAY (scope only) | MUST NOT | MUST (scope) | MAY | Branch scoping key |
| External / pseudonymous customer ref | MUST NOT | MAY (routing key) | MAY (pseudonymous only) | MUST NOT | MAY (aggregate) | MAY | Never a real medical record number |
| Preferred display name (if needed) | MUST NOT | MAY | MAY (only if operationally required) | MUST NOT | MUST NOT | MAY | Not required by default |
| Encrypted phone / email (if needed) | MUST NOT | MAY (delivery only) | MUST NOT | MUST NOT | MUST NOT | MAY (masked) | Encrypted at rest; delivery adapter only |
| Consent / opt-out state | MAY | MUST (gate send) | MAY | MUST NOT | MAY (aggregate) | MAY | Send blocked when opted out |
| Service event ID + completion timestamp | MAY | MAY | MAY | MUST NOT | MAY | MAY | Eligibility/idempotency key |
| Generic service category code | MAY | MAY | MAY | MUST NOT | MAY | MAY | Generic code only, never diagnosis |
| Survey invitation / response / answer / delivery state | MAY | MAY | MAY (answer text as untrusted input) | MUST NOT | MAY (aggregate) | MAY | Free-text answers are untrusted input |
| Feedback analysis / ticket / SLA / action / audit | MUST NOT | MUST NOT | MAY (redacted) | MUST NOT | MAY (aggregate) | MAY | Internal only |
| Google account / location / review data (authorized API) | MUST NOT | MUST NOT | MAY (review text as untrusted input) | MAY (own reply only) | MAY (aggregate) | MAY | From authorized API only |

Notes that bind:
- Free-text survey answers and Google review text are **untrusted input** (Persona §4.10; Master Source
  §44) and MUST NOT determine tool calls; they may be classified but MUST pass the guardrail before any AI
  action (see [`PROMPT_INJECTION_DEFENSE.md`](./PROMPT_INJECTION_DEFENSE.md)).
- No allowed field authorizes disclosure of a private fact in a public reply; public text is governed by
  [`PILOT_PUBLIC_REPLY_SAFETY.md`](./PILOT_PUBLIC_REPLY_SAFETY.md).

## 3. Prohibited-by-default data (Persona §8.2; Master Source §43)

The following MUST NOT enter the pilot data model, an AI provider prompt, or public output by default:

| Prohibited field | AI prompt | Public reply | Rationale |
|---|---|---|---|
| Diagnosis | MUST NOT | MUST NOT | Healthcare privacy |
| Clinical notes | MUST NOT | MUST NOT | Healthcare privacy |
| Medical record number (nomor rekam medis) | MUST NOT | MUST NOT | Direct identifier |
| Prescription / medication details | MUST NOT | MUST NOT | Healthcare privacy |
| Odontogram | MUST NOT | MUST NOT | Clinical record |
| Clinical photos / scans | MUST NOT | MUST NOT | Clinical record |
| Treatment-plan narrative | MUST NOT | MUST NOT | Healthcare privacy |
| Treatment history | MUST NOT | MUST NOT | Healthcare privacy |
| Insurance details | MUST NOT | MUST NOT | Sensitive financial |
| Payment-card / bank-account data | MUST NOT | MUST NOT | Financial / PCI |
| Unredacted internal incident notes | MUST NOT (in AI prompt) | MUST NOT | May contain PII / admissions |

Prohibition holds regardless of convenience or a customer volunteering the data in free text; volunteered
sensitive content MUST be redacted before AI processing (see
[`PILOT_PRIVACY_RULES.md`](./PILOT_PRIVACY_RULES.md)) and MUST NOT be echoed in public output.

## 4. Encryption and transport requirements

- Phone/email and any customer contact detail MUST be **encrypted at rest**; refresh tokens MUST NOT be
  stored in plaintext; Google credentials/tokens MUST be encrypted (Master Source §43;
  [`../integrations/google/OAUTH_AND_TOKEN_SECURITY.md`](../integrations/google/OAUTH_AND_TOKEN_SECURITY.md)).
- All transport (survey, invitation delivery, webhook intake, API, AI provider call) MUST use TLS.
- Backups and exports MUST inherit the same classification; exports MUST be permissioned and audited
  (Persona §4.7; Master Source §37).

## 5. Tenant and branch tagging (mandatory on every record)

Every pilot record MUST carry `tenant_id`; branch-relevant records MUST carry `branch_id`. Isolation MUST
be enforced on DB, cache, queue, storage, search, export, API, webhook, analytics, notifications, AI
retrieval, and tenant-visible logs. Queue jobs MUST carry tenant context; AI retrieval MUST be
tenant/branch-scoped and send only the minimum relevant context (Master Source §42).

## 6. Exception process

A prohibited field MAY be admitted only through **all** of: (1) documented privacy/security review,
(2) established lawful basis, (3) explicit data minimization for the specific use, and (4) a Master Source
update recording the decision. No exception is valid on the authority of chat history or convenience
([`12-documentation-living-source-versioning`](../../.claude/rules/12-documentation-living-source-versioning.md)).
Until such an update exists, the §3 prohibition is absolute.

## 7. Prohibited-field test (evidence requirement)

Before any pilot AI or public-reply capability is exercised, a **prohibited-field test** MUST exist and
pass, producing recorded evidence. The test (to be implemented when application code exists — currently
`APPLICATION IMPLEMENTATION NOT STARTED`) MUST assert, at minimum:

1. For an adversarial dataset that embeds each §3 field (including via free-text answer and review text),
   the AI provider prompt payload contains **zero** prohibited fields after redaction.
2. Generated public-reply candidates contain **zero** prohibited fields and no private-fact confirmation.
3. Cross-tenant and cross-branch fetch attempts return no data (isolation).
4. Every check emits an audit record; a single failure marks the suite failed and the pilot `NO-GO` for
   that surface.

Evidence location (when produced): `docs/evidence/validation/` and the Step 2 traceability matrix
[`../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`](../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md).
The test plan is elaborated in [`../ai/PILOT_AI_EVALUATION_PLAN.md`](../ai/PILOT_AI_EVALUATION_PLAN.md).

## Related documents

[`PILOT_PRIVACY_RULES.md`](./PILOT_PRIVACY_RULES.md) ·
[`PILOT_PUBLIC_REPLY_SAFETY.md`](./PILOT_PUBLIC_REPLY_SAFETY.md) ·
[`PILOT_THREAT_AND_ABUSE_CASES.md`](./PILOT_THREAT_AND_ABUSE_CASES.md) ·
[`PRIVACY_AND_PII.md`](./PRIVACY_AND_PII.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) ·
[`../canonical/MASTER_SOURCE.md`](../canonical/MASTER_SOURCE.md) ·
[`../canonical/PRD.md`](../canonical/PRD.md).
