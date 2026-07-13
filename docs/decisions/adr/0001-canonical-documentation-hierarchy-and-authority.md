# ADR 0001 — Canonical Documentation Hierarchy and Authority

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/00`, `12` · **Canonical:** Master Source §1, §66.3

## Context
Decisions were held in chat history and scattered drafts, risking drift and un-traceable changes.

## Decision
Adopt a single authority order (owner decision → Master Source → PRD → ADR/decision log → other docs →
derived artifacts → knowledge-graph). Store canonical product knowledge in the repository:
normalized `docs/canonical/MASTER_SOURCE.md` and `PRD.md`, with byte-preserved originals in
`docs/canonical/source/` and SHA-256 checksums. The Master Source is the living source of truth.

## Alternatives considered
- Chat-only / wiki-only knowledge — rejected: not versioned, not traceable, not enforceable.
- PRD as top authority — rejected: Master Source is the living superset; PRD is downstream.

## Consequences
Every material change updates the Master Source (versioned) and is traceable via the coverage matrix.
Derived docs must link, not duplicate. Adds a documentation-maintenance obligation.

## Security impact
Centralizes governance; preserved sources support audit. No secrets are stored in documentation.

## Migration impact
None (greenfield repository).

## Supersession
Only by a higher-version Master Source update that this ADR references.
