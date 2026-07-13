---
id: "18"
title: Pilot Healthcare Privacy, Human Approval, and Review Safety
domain: security
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.2.0 §16, §33, §43, §44, §53"
  - "PRD v1.1.0 §13, §15.2, §16, §17"
  - "Persona and Pilot Use Cases v1.0.0 §8, §12, §14.1"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 18 — Pilot Healthcare Privacy, Human Approval, and Review Safety

## Purpose
Protect patients and reputation during the pilot: keep medical data out of AI and public output, require
human approval for public/high-risk actions, forbid review gating, and keep external states truthful.

## Scope
Pilot data boundary, AI prompts, public Google Review replies, human approval, and external status display.

## Rules
- The following **MUST NOT** be sent to an AI provider or appear in any public reply by default: diagnosis,
  clinical notes, medical record number, prescription/medication, odontogram, clinical photos/scans,
  treatment-plan narrative, insurance details, payment-card/bank data, unredacted internal incident notes,
  and treatment history. An exception **MUST** require privacy/security review, lawful basis, data
  minimization, and a Master Source update.
- Customer feedback and Google reviews **MUST** be treated as untrusted input and **MUST NOT** determine
  system or tool behavior (prompt-injection defense + tool allowlisting mandatory; see `.claude/rules/04`, `05`).
- Every Google Review reply on the pilot **MUST** pass recorded human approval before publication. Human
  approval **MUST** also be required for the Master Source §33 / PRD §13 triggers (1–2 star reviews;
  legal/medical/safety/fraud/discrimination risk; PII; refunds/discounts/compensation; data deletion; legal
  statements; low AI confidence; policy conflict; admission of fault; repeated customer contact).
- Review gating and manipulation are **PROHIBITED**: the pilot **MUST NOT** route only satisfied customers,
  block review access by CSAT/sentiment, request a specific rating, incentivize positive reviews, or fake
  reviews. All eligible customers **MUST** get equal Google Review access. Sensitive cases **MUST** be routed
  to a private channel.
- External actions **MUST NOT** be shown as succeeded before provider verification; a failed publish **MUST**
  keep a truthful `Publication failed` (or equivalent) state, and a mock/unavailable Google integration
  **MUST NOT** be claimed as integration success (scope may be `BLOCKED`).

## Required checks
- `scripts/docs/check-step2-coverage.sh` verifies privacy-boundary, human-approval, review-gating, and
  truthful-state coverage. `scripts/graphify/query-smoke.sh` resolves prohibited-data and approval queries.

## Evidence
- `docs/security/PILOT_DATA_BOUNDARY.md`, `docs/security/PILOT_PRIVACY_RULES.md`,
  `docs/security/PILOT_PUBLIC_REPLY_SAFETY.md`, `docs/ai/PILOT_AI_HUMAN_APPROVAL_RULES.md`,
  `docs/product/PILOT_WORKFLOW_STATES.md`.

## Related canonical sections
- Master Source §16, §33, §43, §44, §53; PRD §13, §15.2, §16, §17; Persona §8, §12, §14.1.

## Supersession
Privacy boundary, human approval, and anti-gating are permanent; relaxation (e.g. controlled auto-publish)
requires all Master Source §16.4 preconditions and an owner-approved Master Source update.
