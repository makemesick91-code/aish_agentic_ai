# Pilot Public Reply Safety — Aish Agentic AI

- **Document:** Public (Google Review) Reply Safety Rules and Guardrails — Pilot
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §12, §12.1, §12.2, UC-P0-13; Master Source §16, §29, §43, §53; PRD §11.2, §16, §17.
Rules: [`06-google-review-policy`](../../.claude/rules/06-google-review-policy.md),
[`10-ui-ux-and-truthful-states`](../../.claude/rules/10-ui-ux-and-truthful-states.md).

## 1. Purpose and hard rules

Any public Google Review reply during the pilot MUST be safe, policy-compliant, and truthful about its
publication state. On the pilot, **every** reply MUST pass human approval before publication (Master Source
§16.4). Auto-publish MUST NOT be enabled — the §16.4 preconditions are not met during the pilot.

Hard safety gates (Persona §14.1): zero unauthorized public reply; zero known PII/medical leakage; 100% of
public replies have recorded human approval; no external action reported as success before provider
verification.

## 2. Mandatory human approval

- Every reply MUST be drafted, reviewed, and explicitly approved by the **Reputation Approver** (Persona
  §4.6) before publication.
- All Master Source §33 / PRD §13 triggers additionally force approval and, where sensitive, routing to a
  private channel — see [`../ai/PILOT_AI_HUMAN_APPROVAL_RULES.md`](../ai/PILOT_AI_HUMAN_APPROVAL_RULES.md)
  and [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md).
- The AI response agent MAY only **suggest** a draft; it MUST NOT publish.

## 3. PII / medical guardrail

A reply candidate MUST be blocked from publication if it contains, confirms, or implies any of the
authoritative prohibited set (Master Source §67.5 / Rule 18 / [`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md)
§3): diagnosis, clinical notes, medical procedure, treatment-plan narrative, treatment or visit history,
odontogram, clinical photos/scans, doctor–patient relationship, prescription/medications, test/scan results,
medical record number, insurance details, payment-card/bank data, payment-dispute detail, or any other
private fact. The guardrail runs on the draft **and** on the final approved text before the publish call.

## 4. Safe template patterns (Persona §12.2)

- **Positive review:** warm, brief thanks; no private facts; no confirmation of what service was received.
  Example: *"Terima kasih atas ulasan dan kepercayaan Anda. Senang mendengar pengalaman Anda positif."*
- **Negative / sensitive review:** empathetic, non-defensive, no confirmation of any sensitive fact, and an
  invitation to a **private channel**. Example: *"Terima kasih atas masukan Anda. Kami ingin memahami
  pengalaman Anda lebih lanjut melalui kanal resmi dan privat kami."*
- A reply MUST NOT confirm or deny that the reviewer was a patient, what treatment occurred, or any
  transaction detail. Sensitive cases MUST be routed to a private channel, not resolved in public text.

## 5. Brand voice (Persona §12.1)

Replies MUST be warm, professional, empathetic, and concise; MUST NOT be defensive; MUST NOT attack or
blame the reviewer; MUST NOT make legal admissions or admissions of fault; and MUST default to Indonesian
(Bahasa Indonesia) unless the review language clearly requires otherwise.

## 6. Untrusted-input handling

Review content is untrusted input (Master Source §44). It MUST NOT determine tool calls or instructions to
the model; embedded instructions in a review ("ignore your rules", "post my phone number") MUST be ignored.
Tool arguments MUST be validated and tools allowlisted
([`PROMPT_INJECTION_DEFENSE.md`](./PROMPT_INJECTION_DEFENSE.md)).

## 7. No gating, no manipulation

The pilot MUST NOT gate or manipulate reviews (Master Source §16.1): it MUST NOT request 5 stars or any
specific rating, MUST NOT incentivize positive reviews, MUST NOT route only satisfied customers to Google
Review, MUST NOT hide reviews from unhappy customers, and MUST NOT condition review access on CSAT. All
eligible customers get **equal** access regardless of sentiment
([`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md)).

## 8. Truthful publication state

Reply state MUST use the truthful vocabulary (Master Source §53; PRD §16): no draft → draft generated →
under review → changes requested → approved → publishing → published → publication failed → moderation
pending → policy issue → removed. On a failed Google API call the status MUST remain **`Publication
failed`** and MUST NOT be shown as `Published`. Retry MUST be idempotent and MUST NOT create duplicate
public replies (see [`../ai/PILOT_MANUAL_FALLBACK.md`](../ai/PILOT_MANUAL_FALLBACK.md)).

## 9. Reputation Approver pre-publish checklist

The Reputation Approver MUST confirm all of the following before approving publication; any "no" blocks
publication:

1. No prohibited/private fact is present or implied — the full set (diagnosis, clinical notes, procedure,
   treatment-plan narrative, visit/treatment history, odontogram, clinical photos/scans, doctor–patient
   relation, prescription/medications, results, MRN, insurance, payment/dispute detail); see
   [`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md) §3.
2. No admission of fault and no legal statement.
3. Tone is warm, professional, empathetic, concise, and non-defensive; reviewer is not attacked.
4. Language default is Indonesian and appropriate to the review.
5. No rating request, incentive, or gating language.
6. Sensitive/negative case routes to a private channel rather than disclosing detail publicly.
7. Reply targets the correct tenant/branch/review (no cross-tenant mismatch).
8. Any §33/PRD-§13 trigger present has been handled per the approval rules.
9. Approval and approver identity are recorded to the audit log before publish.

Recording the approval decision and approver identity is mandatory audit evidence (Persona §15).

## Related documents

[`PILOT_DATA_BOUNDARY.md`](./PILOT_DATA_BOUNDARY.md) ·
[`PILOT_PRIVACY_RULES.md`](./PILOT_PRIVACY_RULES.md) ·
[`../ai/PILOT_AI_HUMAN_APPROVAL_RULES.md`](../ai/PILOT_AI_HUMAN_APPROVAL_RULES.md) ·
[`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md).
