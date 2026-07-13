---
id: "00"
title: Document Authority and Canonical Repository
domain: governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §1, §66.2, §66.3"
  - "PRD §1 Tujuan Dokumen"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 00 — Document Authority and Canonical Repository

## Purpose
Fix the single source-of-truth hierarchy and the single canonical repository so that decisions are
traceable, cross-session, and never invented from chat history alone.

## Scope
All work in this repository: documentation, configuration, code, tooling, CI, and release.

## Rules
- Work **MUST** target the canonical repository `makemesick91-code/aish_agentic_ai`
  (`https://github.com/makemesick91-code/aish_agentic_ai`).
- Before any commit, push, PR, merge, or tag, Claude **MUST** verify the normalized `origin` resolves to
  `makemesick91-code/aish_agentic_ai`. On mismatch, **MUST** stop with `NO-GO: WRONG REPOSITORY`.
- Claude **MUST NOT** create, initialize, or treat another repository as canonical. A separate component
  repository **MAY** exist only through an explicit versioned ADR + Master Source update.
- Source conflicts **MUST** be resolved by this authority order: (1) latest explicit owner decision,
  (2) highest-version Master Source, (3) newest approved PRD, (4) approved ADRs / decision log,
  (5) other repository docs, (6) derived artifacts, (7) knowledge-graph indexes.
- Knowledge-graph (Graphify) output **MUST NOT** override or silently replace canonical documents.
- Remote URLs containing credentials **MUST** be recorded redacted; `origin` **MUST NOT** be silently changed.

## Required checks
- `scripts/docs/check-version-consistency.sh` confirms the active Master Source is ≥ v2.1.1 and that the
  canonical repository identity matches everywhere it appears.
- Preflight records `git remote -v` (redacted), current branch, existing branches, and tags.

## Evidence
- `docs/evidence/git-release/` remote verification; `docs/decisions/DECISION_LOG.md` authority decisions.

## Related canonical sections
- Master Source §1 (Status/Authority), §66.2 (Canonical repo), §66.3 (Source hierarchy); PRD §1.

## Supersession
Superseded only by a higher-version Master Source update that this rule links to. Historical decisions
are marked superseded, never deleted.
