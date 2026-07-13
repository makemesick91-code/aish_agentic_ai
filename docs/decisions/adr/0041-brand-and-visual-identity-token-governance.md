# ADR 0041 — Brand and Visual Identity Token Governance

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **tokens NOT implemented in any UI**
- **Owner:** Brand Systems Architect
- **Rule:** `.claude/rules/22`, `10` (AFR-081..086) · **Canonical:** Master Source v2.4.0 §68; §52; PRD v1.3.0

## Context
The product needs a governed brand and an accessible visual-token baseline so future UI work is consistent —
without producing final creative or implementing tokens in a UI.

## Decision
Brand hierarchy is the branded house Aish Tech Solution → Aish Agentic AI; the product name and official
descriptor are fixed and MUST NOT be reduced to a survey/review/chatbot tool. A machine-readable planning-token
file [`brand-tokens.v1.json`](../../brand/tokens/brand-tokens.v1.json) defines color and typography groups,
labelled "PLANNING TOKENS — NOT IMPLEMENTED IN UI". Contrast targets WCAG 2.2 AA and is verified at design review
before any UI implementation. A working tagline carries status "APPROVED WORKING BASELINE", not a trademark. No
final logo/brand is claimed without owner approval; no misleading AI-autonomy or guaranteed-rating claim is
permitted. See [Brand Foundation](../../brand/BRAND_FOUNDATION.md) and
[Visual Identity Baseline](../../brand/VISUAL_IDENTITY_BASELINE.md).

## Alternatives
- **Finalize logo/design now** — rejected: requires design review + owner approval; out of Step 4 scope.
- **No token governance** — rejected: leads to inconsistent, inaccessible UI later.
- **Skip accessibility targets** — rejected: WCAG 2.2 AA is required.

## Consequences
A consistent, accessible brand baseline that UI implementation adopts; final creative is a later gated step.

## Impacts
- **Security:** self-hosted fonts avoid third-party CDN calls (no data leakage to font providers).
- **Privacy:** brand assets carry no personal/medical data; public reply tone forbids PII disclosure.
- **Tenant isolation:** brand is product-wide, not tenant data; no tenant-specific hard-coding.
- **Database:** none (no data model change).
- **Operational:** token versioning + asset checksum + approval workflow defined.
- **Cost:** open-source fonts (OFL); design production is a later planning cost category.

## Verification / fitness function
`check-brand-tokens.sh` parses the token JSON and asserts the required token groups + planning label;
`check-step4-coverage.sh` asserts brand governance, tagline status, and accessibility target (V4-BRAND-01/02).

## Related
Requirement: Master Source v2.4.0 §68, §52; PRD v1.3.0. Application rules: AFR-081..086. Rules: 22, 10, 01, 06.
ADRs: 0018 (frontend), 0028 (human approval / truthful states).

## Evidence
`docs/brand/*` (foundation, architecture, voice, tagline, visual identity, accessibility, logo governance),
`docs/brand/tokens/brand-tokens.v1.json`.

## Non-claims
No final logo, brand, or design is claimed. Tokens are not implemented in any UI and contrast is not verified in a
running UI. The tagline is a working baseline, not a registered trademark.

## Rollback
Tokens and tagline are reversible before UI implementation; changes are recorded decisions. Weakening accessibility
targets requires documented owner approval and a Master Source update.
