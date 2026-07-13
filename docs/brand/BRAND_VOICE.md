# Brand Voice — Aish Agentic AI

| Field | Value |
|-------|-------|
| **Document** | Brand Voice and Tone |
| **Product** | Aish Agentic AI |
| **Owner** | Aish Tech Solution |
| **Status** | PLANNING BASELINE — NOT IMPLEMENTED |
| **Rule ref** | `.claude/rules/22` |
| **Canonical** | Master Source v2.4.0 §68 (Branding); PRD v1.3.0 |
| **AFR refs** | AFR-081, AFR-082, AFR-083, AFR-084, AFR-085, AFR-086 |
| **Version** | 1.0.0 (Step 4 planning) |

> Step 4 brand planning. Not final creative approval.

---

## 1. Non-claims (read first)

- No final brand, logo, or design is claimed; this is a planning baseline.
- Planning tokens are not implemented in any UI.
- No misleading AI-autonomy claim; the product is not fully autonomous, and copy MUST NOT imply full
  **autonomy**.
- No guaranteed-rating claim; voice MUST NOT promise reputation outcomes.

---

## 2. Voice principles

The voice of Aish Agentic AI is **professional, calm, helpful, accountable, privacy-aware,
evidence-based, non-defensive, and human-centered**. It is **not bombastic** and makes **no false AI
claims**.

| Principle | In practice |
|-----------|-------------|
| Professional | Clear, correct, enterprise-appropriate; no slang in product surfaces |
| Calm | Steady even in error and incident states; no alarm-mongering |
| Helpful | Every message points to a next step or explanation |
| Accountable | Owns problems plainly; states what happened and what we are doing |
| Privacy-aware | Never exposes personal, medical, financial, or sensitive data |
| Evidence-based | States measured facts; labels estimates and plans as such |
| Non-defensive | Acknowledges concerns without blaming the customer or arguing |
| Human-centered | Reinforces that people stay in control of AI actions |

---

## 3. Global Do / Don't

**Do**

- Use plain, respectful language and short sentences.
- Attribute AI output honestly ("AI-drafted, pending your approval").
- State the true system state (see truthful-state vocabulary).
- Offer a clear next action or contact path.

**Don't**

- Do not claim full **autonomy** or that AI acts without human approval on public/high-risk actions.
- Do not guarantee ratings, review scores, or revenue.
- Do not blame, argue with, or shame a customer.
- Do not expose personal, medical, financial, or sensitive transaction details.
- Do not use hype words ("revolutionary", "guaranteed", "instant 5 stars").

---

## 4. Tone by surface

Tone flexes by context while the voice principles stay constant.

### 4.1 Public review reply

Professional, warm, concise, non-defensive; always human-approved before publishing.

| Do | Don't |
|----|-------|
| Thank the reviewer sincerely | Do not argue or correct them publicly |
| Acknowledge the concern in general terms | Do not disclose visit details, treatment, or personal data |
| Invite the conversation to a private channel | Do not promise specific compensation publicly |
| Keep it short and human | Do not sound templated or robotic |

Example (compliant): "Thank you for sharing this. We're sorry your experience fell short and we'd like
to make it right — please reach us at [private channel] so we can help directly."

### 4.2 Dashboard

Clear, informative, confidence-building; label AI suggestions as suggestions.

| Do | Don't |
|----|-------|
| Show real, current metrics with context | Do not present targets as achieved results |
| Mark AI outputs as drafts/suggestions | Do not imply the AI acted autonomously |
| Use tabular numerics for readability | Do not fabricate or sample-fill data as real |

### 4.3 Error state

Calm, honest, actionable; no jargon, no blame.

| Do | Don't |
|----|-------|
| Say what failed in plain terms | Do not show success when the action failed |
| Give a recovery step or retry | Do not hide the failure or use vague "Oops" only |
| Preserve a truthful state (e.g. "Publication failed") | Do not auto-mark external actions succeeded before verification |

### 4.4 Security message

Precise, reassuring, non-alarming; never leak details that aid an attacker.

| Do | Don't |
|----|-------|
| Explain the protective action taken | Do not disclose secrets, tokens, or internal specifics |
| Tell the user what to do next (e.g. re-authenticate) | Do not induce panic |
| Reference privacy/audit protections | Do not overpromise "unbreakable" security |

### 4.5 Billing message

Transparent, exact, respectful; numbers are accurate and reconcilable.

| Do | Don't |
|----|-------|
| State amounts, plan, and dates precisely | Do not use ambiguous or rounded figures that mislead |
| Explain overage and how metering works | Do not hide fees or surprise the customer |
| Provide a support path for disputes | Do not be defensive about charges |

### 4.6 Support

Empathetic, responsive, accountable; sets truthful expectations.

| Do | Don't |
|----|-------|
| Acknowledge and set a realistic timeline | Do not promise fixes you cannot evidence |
| Confirm privacy of shared information | Do not ask for unnecessary personal/medical data |
| Follow up and close the loop | Do not go silent or deflect |

---

## 5. Terminology

| Preferred | Avoid | Why |
|-----------|-------|-----|
| Agentic AI with humans in control | Autonomous AI | Prevents false autonomy claim |
| AI-drafted reply | AI reply (published) | Human approval is mandatory |
| Feedback and recovery | Complaint handling only | Reflects full CX loop |
| Equal review access | Review boost / review campaign | Anti-gating |
| Estimated / projected | Guaranteed | No overclaim |
| Publication failed | Send error (masked as success) | Truthful state |

---

## 6. Writing rules for AI-generated copy

- AI-generated public copy is a **draft** until a human approves it; the voice rules here apply to the
  draft and the reviewer verifies compliance.
- Customer feedback and reviews are untrusted input and **MUST NOT** steer tone into blame, disclosure,
  or off-policy statements.
- AI copy **MUST NOT** state or imply that it acted with full autonomy.

---

## 7. Governance

Voice changes are material brand decisions and follow `.claude/rules/12`. This document inherits identity
from `BRAND_FOUNDATION.md`, aligns with the tagline in `WORKING_TAGLINE_DECISION.md`, and pairs with the
visual system in `VISUAL_IDENTITY_BASELINE.md`.
