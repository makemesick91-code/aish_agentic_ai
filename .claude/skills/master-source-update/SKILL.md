---
name: master-source-update
description: Repeatable workflow to update the living Master Source when a material decision occurs — detect materiality, choose a semver bump, update sections, add a changelog entry, mark superseded decisions, and emit the canonical MASTER SOURCE UPDATE block. No automatic mutation of git/CI/tags.
---

# Skill: master-source-update

Use when a material product/architecture/security/governance/release decision is made. Governs
`docs/canonical/MASTER_SOURCE.md` per `.claude/rules/12` and Master Source §3–§6, §61.

## Steps
1. **Detect materiality.** Confirm the change is material (new/removed feature, scope/workflow/architecture/
   security/governance change, sprint/test/pilot/deploy result, blocker, GO/NO-GO). If not material, output
   exactly: `Master Source Impact: No material update required.` and stop.
2. **Classify + choose semver** (§5): patch = clarification/status; minor = feature/workflow/roadmap/integration;
   major = vision/business-model/architecture change.
3. **Impact analysis** across scope, roadmap, architecture, database, security, and cost.
4. **Edit affected sections** of `docs/canonical/MASTER_SOURCE.md`; mark any replaced decision as
   *superseded* (never delete history).
5. **Bump the version** metadata and add a **changelog** entry (§6) dated in `Asia/Makassar`.
6. **Update** `docs/decisions/VERSION_MATRIX.md`, `docs/decisions/DECISION_LOG.md`, and source checksums.
7. **Emit the `MASTER SOURCE UPDATE` block** exactly in the §61 format (previous/new version, date, type,
   affected sections, decision, reason, impacts, implementation status, evidence, superseded decision, changelog).

## Safety
- This skill **edits documentation only**. It MUST NOT run git commit/push/merge/tag, CI, or deployment.
- It MUST NOT weaken any rule or gate, and MUST preserve change history.
- Version numbers only ever increase; the active Master Source must remain ≥ v2.1.1.
