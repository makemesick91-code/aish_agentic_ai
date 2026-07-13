---
name: session-checkpoint
description: Persist a concise, truthful checkpoint (current state, handoff, session log) before compaction or session exit, so no decision lives only in chat history.
---

# Skill: session-checkpoint

**Trigger:** Before context compaction, session exit, or after completing a phase.
**Non-trigger:** Mid-edit within a single small task.
**Inputs:** Current branch, phase status, blockers, next action.

## Workflow
1. Update `docs/status/CURRENT_STATE.md` (truthful state + completed/remaining).
2. Update `docs/status/HANDOFF.md` (what the next session must do).
3. Append to `docs/status/SESSION_CHECKPOINTS.md` (dated phase log).
4. Ensure status language is truthful (no false implementation/deployment claims, AFR-068).

## Safety boundaries
Read/write to `docs/status/` only. MUST NOT merge, tag, push, or mutate git history.

## Required output
Three updated status docs with consistent, truthful state.

## Evidence
`docs/status/CURRENT_STATE.md`, `HANDOFF.md`, `SESSION_CHECKPOINTS.md`.

## Failure behavior
If state is uncertain, record the uncertainty explicitly rather than guessing.
