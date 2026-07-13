---
name: master-source-update
description: Update the living Master Source on a material decision — detect materiality, pick a semver bump, update sections/changelog, mark superseded decisions, and emit the canonical MASTER SOURCE UPDATE block. No automatic git/CI/tag mutation.
---

# Skill: master-source-update

**Trigger:** A material decision (scope/workflow/architecture/security/governance/version).
**Non-trigger:** Non-material clarifications with no scope impact.
**Inputs:** The decision, current Master Source/PRD versions.

## Workflow
1. Assess materiality (Master Source §3/§4); choose patch/minor/major (§5).
2. Update the Master Source sections + changelog; bump `**Versi:**`; update PRD if affected.
3. Update `docs/decisions/VERSION_MATRIX.md`, `DECISION_LOG.md`, and `CHANGELOG.md`.
4. Emit the `MASTER SOURCE UPDATE` block (§61 format). Mark superseded decisions SUPERSEDED (never delete).
5. Preserve originals byte-for-byte in `docs/canonical/source/` with updated checksums.

## Safety boundaries
Edits canonical docs only. MUST NOT merge, tag, push, or run CI. Historical versions are never deleted.

## Required output
Updated Master Source/PRD + version matrix + changelog + MASTER SOURCE UPDATE block.

## Evidence
`docs/canonical/MASTER_SOURCE.md`, `docs/decisions/VERSION_MATRIX.md`, `CHANGELOG.md`.

## Failure behavior
If materiality is unclear, record the analysis and choose the more conservative (higher-impact) bump.
