---
id: "22"
title: Brand Governance and Accessible Visual Baseline
domain: brand
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §68 (Branding)"
  - "PRD v1.3.0 (brand requirements)"
  - "ADR 0041; AFR-081..086"
supersede: "Only via a versioned Master Source update; product name, no-misleading-claims, and accessibility targets are permanent."
---

# Rule 22 — Brand Governance and Accessible Visual Baseline

## Purpose
Keep brand identity consistent and accessible, and prevent misleading or premature brand/AI claims.

## Scope
Product naming, descriptor, brand hierarchy, tagline, voice, visual tokens, accessibility, and logo/asset governance.

## Rules
- The official product descriptor **MUST** be used; positioning **MUST NOT** be reduced to a survey app, review
  generator, chatbot, or Google Review tool (AFR-081).
- Brand hierarchy **MUST** be the branded house **Aish Tech Solution → Aish Agentic AI** (AFR-082).
- A working tagline **MUST** carry status **"APPROVED WORKING BASELINE"** and **MUST NOT** be presented as a final
  trademark (AFR-083).
- Brand tokens **MUST** be versioned and labelled planning; they **MUST NOT** be claimed implemented in any UI
  (AFR-084). Visual token contrast **MUST** target **WCAG 2.2 AA** and **MUST** be verified at design review before
  UI implementation (AFR-085).
- No final logo/brand/design **MUST** be claimed without owner approval; there **MUST NOT** be a misleading
  AI-autonomy claim (e.g. "fully autonomous") or a guaranteed-rating-improvement claim (AFR-086).
- Brand voice **MUST** be professional, calm, accountable, privacy-aware, evidence-based, non-defensive, and
  human-centered; public-reply tone **MUST NOT** disclose PII/medical data.

## Required checks
- `scripts/docs/check-step4-coverage.sh` (brand governance, tagline status, accessibility target) and
  `scripts/docs/check-brand-tokens.sh` (token JSON structure + planning label).

## Evidence
- `docs/brand/*`; `docs/brand/tokens/brand-tokens.v1.json`.

## Related canonical sections
- Master Source v2.4.0 §68, §52; PRD v1.3.0; ADR 0041; ADRs 0018, 0028; rules 01, 06, 10.

## Supersession
Product name, no-misleading-claims, and accessibility targets are permanent; visual tokens/tagline may evolve via a
recorded design decision and Master Source update.
