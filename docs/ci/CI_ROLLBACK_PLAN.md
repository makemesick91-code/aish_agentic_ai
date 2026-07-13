# CI Rollback Plan — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`, `13`. ADR 0042, 0046.

## Workflow rollback
The retired `documentation-foundation.yml` is preserved verbatim as non-executable evidence at
`docs/evidence/cicd-ctrl-1/workflows/documentation-foundation.yml.txt`. To revert to the single always-full
workflow: restore that file to `.github/workflows/documentation-foundation.yml`, remove `pr-ci.yml` /
`main-post-merge.yml`, and update the `main` ruleset's required check back to the old context. This is a recorded
decision; security/release gates MUST NOT be weakened in the process.

## Ruleset rollback
Before enforcement, the current settings are exported to `docs/evidence/cicd-ctrl-1/ruleset/`:
- `actions-permissions-before.json`, `rulesets-before.json`, `branch-protection-before.json`.
After enforcement, `*-after.json` are stored. To roll back, re-apply the before-state (main had **no** protection
and **empty** rulesets at baseline) via `gh api`. Admin bypass is never used.

## Tag rollback
Tags are immutable. A mismatched or wrong tag is **NO-GO** and is corrected only by creating a **new**, correctly
named/annotated tag on the correct commit — never by moving, deleting, or force-updating an existing tag.

## Migration invariants
- Never leave two full workflows active for the same event.
- Only one active required workflow (`pr-ci.yml`) may exist.
- Remove the obsolete workflow in the same controlled PR after the replacement's check name is verified on a live PR.
