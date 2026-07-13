---
id: "14"
title: Limit Saver, Context, and Handoff
domain: tooling
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §66.6"
  - "GO Tag Prompt v1.0.1 (Execution Mode: Limit Saver 1)"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 14 — Limit Saver, Context, and Handoff

## Purpose
Reduce token/context cost across sessions without ever weakening quality, security, or evidence.

## Scope
Context management, checkpoints, handoffs, and use of token-saving skills.

## Rules
- A token-saving skill (`Limit Saver 1`, `limit-saver-1`, `usage-limit-reducer`, or a clearly equivalent
  installed skill) **SHOULD** be used when available; Claude **MUST** invoke the exact installed skill name
  and **MUST NOT** invent a slash command or silently install an unknown package.
- When no such skill is installed, the **fallback token-saving protocol MUST** be applied and the missing
  optional dependency recorded (see `docs/tooling/LIMIT_SAVER.md`).
- Token saving **MUST NOT** reduce test coverage, skip security review, drop evidence, remove audit, weaken
  a release gate, or cause false completion claims (Master Source §66.6).
- Root `CLAUDE.md` **SHOULD** stay concise (< ~200 lines); detailed rules live in `.claude/rules/`, skills,
  and topic docs. Related reads/tool calls **SHOULD** be batched; large audits **SHOULD** use focused
  subagents returning concise findings + file paths, not full file dumps.
- `docs/status/CURRENT_STATE.md`, `HANDOFF.md`, and `SESSION_CHECKPOINTS.md` **MUST** be updated after each
  phase and before any compaction or session exit. Chat history **MUST NOT** be the only record of a decision.

## Required checks
- Status docs exist and are current; `docs/tooling/LIMIT_SAVER.md` records the skill status and fallback.

## Evidence
- `docs/tooling/LIMIT_SAVER.md`, `docs/status/*`, `docs/evidence/inventory/` skill inventory.

## Related canonical sections
- Master Source §66.6 (Limit Saver); GO Tag Prompt v1.0.1 Execution Mode.

## Supersession
Superseded only by a higher-version Master Source update; the "no weakening of gates" constraint is permanent.
