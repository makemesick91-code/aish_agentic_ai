---
id: "06"
title: Google Review Policy and Anti-Gating
domain: policy
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §16, §29, §38"
  - "PRD §11.2, §17"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 06 — Google Review Policy and Anti-Gating

## Purpose
Ensure Google Review workflows are policy-safe: no gating, no manipulation, no privacy leakage.

## Scope
Review invitation flows, review sync, AI reply drafting, approval, and publication.

## Rules
- The product **MUST NOT** perform review gating or manipulation. Specifically it **MUST NOT**: route only
  satisfied customers to Google Review, hide reviews from unhappy customers, block review access by CSAT,
  request 5 stars or a specific rating, incentivize positive reviews, buy/fake reviews, coerce staff/family,
  set fake review targets, or remove review access by sentiment.
- All eligible customers **MUST** receive equal access to Google Review regardless of CSAT (Master Source §16.2).
- Review replies **MUST** be professional, concise, non-defensive, **MUST NOT** attack the reviewer, and
  **MUST NOT** disclose personal data, medical data, or sensitive transactions; sensitive cases **MUST** be
  routed to a private channel.
- On MVP, every Google Review reply **MUST** pass human approval before publication. Auto-publish **MUST NOT**
  be enabled unless all Master Source §16.4 preconditions are met (explicit tenant consent, AI eval targets,
  stable guardrails, full audit, kill switch, rate limit, controlled templates, risky-review exclusion).
- Google credentials **MUST** be encrypted; the current Google policy/API **MUST** be re-verified before production.

## Required checks
- `docs/integrations/google/GOOGLE_REVIEW_POLICY.md` enumerates prohibitions and the allowed flow.
- Contradiction check ensures no active decision permits gating/auto-publish outside §16.4.

## Evidence
- `docs/integrations/google/GOOGLE_REVIEW_POLICY.md`, `OAUTH_AND_TOKEN_SECURITY.md`, `INTEGRATION_READINESS.md`.

## Related canonical sections
- Master Source §16 (review rules), §29 (response agent), §38 (Google integration); PRD §11.2, §17.

## Supersession
Anti-gating is permanent and policy-bound; it cannot be weakened by convenience — only by verified external policy change recorded in a Master Source update.
