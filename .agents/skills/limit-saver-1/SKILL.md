---
name: limit-saver-1
description: PROJECT FALLBACK context/token-saving protocol. NOT the external or branded Limit Saver 1. Reduces context cost without weakening security, tests, evidence, or gates.
---

# Skill: limit-saver-1 — PROJECT FALLBACK (NOT THE EXTERNAL OR BRANDED LIMIT SAVER 1)

> The external/branded "Limit Saver 1" is **not installed** in this repo/user scope (verified). This is a
> project-owned fallback per `.claude/rules/14` and `docs/tooling/LIMIT_SAVER.md`. Do not claim the branded
> skill is installed (OD-06).

**Trigger:** Long multi-phase sessions; large audits; before compaction; when context is filling.
**Non-trigger:** Small single-file edits; when full-file reading is required for an exact Edit.
**Inputs:** Task ledger, authority map, changed-file list.

## Workflow
1. Read the authority map first (`docs/canonical/DOCUMENT_AUTHORITY.md`, `CLAUDE.md`, `AGENTS.md`).
2. Query the derived index / graph before bulk-reading files; read only relevant sections.
3. Avoid re-reading unchanged files; batch related reads/tool-calls.
4. Maintain a task ledger; checkpoint each phase to `docs/status/`.
5. Use focused subagents for large audits (return concise findings + paths, not full dumps).
6. Emit concise progress; record paths + decisions in status docs.
7. Resume from checkpoints on a new session.

## Safety boundaries
MUST NOT reduce test coverage, skip security review, drop evidence, remove audit, or weaken any gate
(`.claude/rules/14`, AFR-066). Read-only guidance; makes no mutations itself.

## Required output
Updated task ledger + checkpoint; concise progress note referencing file paths.

## Evidence
`docs/status/*`, `docs/tooling/LIMIT_SAVER.md`, `docs/evidence/step-3/inventory/tooling-inventory.txt`.

## Failure behavior
If the protocol would require weakening a gate or dropping evidence, STOP and do the full work instead.
