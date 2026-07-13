# Skill: post-tag-release-evidence

Produce lightweight post-tag evidence and a GitHub Release without running full CI. Rule: `.claude/rules/28`, `13`.
ADR 0044.

## Trigger
- After an annotated immutable GO tag has been pushed and merged into `main`.

## Non-trigger
- Do not use to move/delete/recreate a tag, run full CI, or open a second evidence-only full-CI PR.

## Workflow (read-only inspection + release publish)
1. `scripts/release/verify-immutable-tag.sh <TAG>` — prove local main = origin/main = merge = local tag peeled =
   remote tag peeled, and prior tags unchanged; write `tag-verification.txt`/`.json`.
2. Assemble artifacts (tag-verification, final-ci, ruleset before/after, run-budget, release-manifest).
3. `gh release create <TAG> <artifacts> --title ... --notes-file ...`.

## Safety boundaries
No full CI on tag/evidence (AFR-116, AFR-117). Never move/delete a tag. A tag mismatch is NO-GO — corrected only by
a new correctly-created tag.

## Required output
Tag name, exact-match result, prior-tags-unchanged result, and the GitHub Release URL + attached artifact list.

## Failure behavior
On exact-match failure or a changed prior tag, stop and report NO-GO; do not publish a release claiming success.
