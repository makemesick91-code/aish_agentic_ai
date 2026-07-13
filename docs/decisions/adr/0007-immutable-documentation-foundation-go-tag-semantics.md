# ADR 0007 — Immutable Documentation-Foundation GO Tag Semantics

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/13` · **Canonical:** Master Source §66.11

## Context
A release marker is needed for the documentation foundation without implying application readiness.

## Decision
Use a single **annotated, immutable** tag `aish-agentic-ai-docs-foundation-v1.0.0-go`, created only on the
merged commit after `main` contains the complete change, and exact-matching that commit on local, remote,
and default branch. The tag attests canonical docs, Claude rules, tooling governance, validation, CI, and
evidence readiness **only** — not that the application is implemented, deployed, pilot-ready, or production-ready.

## Alternatives considered
- Lightweight/movable tag — rejected: not immutable, not auditable.
- Tag the bootstrap commit — rejected: bootstrap is not the foundation and gets no GO tag.
- GitHub Release with app-readiness claims — rejected: would be a false completion claim.

## Consequences
Clear, verifiable release point. Tags are never moved/deleted; force-push is prohibited (`.claude/rules/13`).

## Security impact
No security claim beyond documentation/tooling gates; prevents overclaiming production readiness.

## Migration impact
Future application releases use their own gates and tags (Master Source §54).

## Supersession
The tag is immutable. A superseding foundation would use a new, higher version tag recorded in a Master Source update.
