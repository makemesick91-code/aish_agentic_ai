---
id: "01"
title: Product Identity and Positioning
domain: product
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §7-§14, §63"
  - "PRD §2, §3, §7"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 01 — Product Identity and Positioning

## Purpose
Keep product naming, positioning, and target personas consistent across all artifacts and UI surfaces.

## Scope
All documentation, UI copy, marketing, API docs, OAuth consent screens, and generated content.

## Rules
- The official product name **MUST** be **Aish Agentic AI** everywhere (Master Source, PRD, roadmap,
  sprints, repo docs, UI, login, system email, OAuth consent, API docs, landing page, ToS, privacy
  policy, invoices, admin). A temporary/alternative name **MUST NOT** replace it without explicit owner decision.
- The product **MUST** be positioned as an *Agentic AI Customer Experience and Reputation Operating
  Platform*, not merely a survey/CSAT/review tool or chatbot.
- The product **MUST** be multi-tenant SaaS with multi-branch support, combining CSAT, NPS, CES, feedback
  operations, customer recovery, Google Review management, analytics, billing, platform administration, and Agentic AI.
- Persona needs (Business Owner, Corporate Admin, Regional/Branch Manager, CX Manager, Customer Service,
  Reputation Manager, Platform Admin) **MUST** be honored in scope decisions; see `docs/product/PERSONAS_BASELINE.md`.
- The core product **MUST** stay generic; pilot tenants (e.g. Klinik Gigi Daengtisia) **MUST NOT** narrow the core to one industry.

## Required checks
- `scripts/docs/check-version-consistency.sh` verifies product name and canonical identity strings.
- Foundation coverage matrix maps identity to `docs/product/PRODUCT_VISION.md` and this rule.

## Evidence
- `docs/product/PRODUCT_VISION.md`, `docs/product/PERSONAS_BASELINE.md`, coverage matrix row for identity.

## Related canonical sections
- Master Source §7-§14 (identity, description, positioning, vision, mission, target, persona), §63; PRD §2, §3, §7.

## Supersession
Superseded only by a higher-version Master Source update; positioning changes are major/minor version events.
