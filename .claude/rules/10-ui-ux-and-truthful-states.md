---
id: "10"
title: UI/UX and Truthful System States
domain: product
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §15.7, §52, §53"
  - "PRD §16, §21"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 10 — UI/UX and Truthful System States

## Purpose
Ensure the UI is professional, accessible, and never shows false or unverified status.

## Scope
All UI surfaces, status indicators, and system-state modeling.

## Rules
- The UI **MUST** be professional, clean, enterprise-ready, usable by SMEs, mobile-responsive, accessible,
  multi-language capable, and **MUST** provide empty, loading, failure, and permission-denied states (Master Source §52).
- The system **MUST NOT** display a success state when the underlying action has not actually succeeded
  or an external action is not yet verified (Master Source §15.7, §53).
- Connection, AI, reply, and ticket states **MUST** use the truthful vocabularies in Master Source §53 /
  PRD §16 (e.g. reply states: no draft → draft generated → under review → changes requested → approved →
  publishing → published → publication failed → moderation pending → policy issue → removed).
- Basic UI functions **MUST NOT** depend on AI availability (see `.claude/rules/05`).
- Timelines **MUST** be audit-friendly; fabricated/sample data **MUST NOT** be shown as real.

## Required checks
- Truthful-state vocabulary in derived docs matches Master Source §53 / PRD §16.

## Evidence
- `docs/architecture/DOMAIN_MAP.md` (state models), `CLAUDE.md` §5 status vocabulary.

## Related canonical sections
- Master Source §15.7, §52 (UI/UX), §53 (truthful states); PRD §16, §21.

## Supersession
Truthful-state requirements are permanent; superseded only by a higher-version Master Source update.
