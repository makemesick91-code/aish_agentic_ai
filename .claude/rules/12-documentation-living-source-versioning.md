---
id: "12"
title: Documentation, Living Master Source, and Versioning
domain: documentation
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §3, §4, §5, §6, §61"
  - "PRD §31"
supersede: "This rule governs its own supersession process; changes require an owner-approved Master Source update."
---

# Rule 12 — Documentation, Living Master Source, and Versioning

## Purpose
Keep the Master Source a living, versioned, auditable source of truth and prevent silent decision drift.

## Scope
Master Source, PRD, ADRs, decision log, version matrix, and all canonical documentation.

## Rules
- The Master Source **MUST** be treated as a living document and updated for every material decision
  (Master Source §3): new/removed features, scope/workflow/architecture/security/governance changes,
  sprint/test/security/pilot/deploy results, blockers, and GO/NO-GO decisions.
- Every material change **MUST** follow the §4 protocol and produce a `MASTER SOURCE UPDATE` block in the
  §61 format (previous/new version, date, type, affected sections, decision, reason, impacts, status, evidence, changelog).
- Semantic versioning **MUST** be applied (Master Source §5): patch = clarification/status, minor =
  feature/workflow/roadmap/integration, major = vision/business-model/architecture change.
- Superseded decisions **MUST** be marked superseded, **MUST NOT** be deleted; change history is permanent.
- Derived documents **MUST NOT** duplicate all canonical content; they link to it and use coverage matrices.
- The active canonical Master Source **MUST** be ≥ v2.1.1 and accurately record the canonical repository
  and completed foundation state.

## Required checks
- `scripts/docs/check-version-consistency.sh` verifies Master Source/PRD versions and cross-references.
- `master-source-update` skill produces the update block; `documentation-gate` skill runs doc gates.

## Evidence
- `docs/canonical/MASTER_SOURCE.md`, `docs/canonical/PRD.md`, `docs/decisions/DECISION_LOG.md`,
  `docs/decisions/VERSION_MATRIX.md`, `CHANGELOG.md`.

## Related canonical sections
- Master Source §3-§6 (living/versioning/changelog), §61 (update format); PRD §31.

## Supersession
Changes to this process require an owner-approved Master Source update recorded in the version matrix.
