# Skill: ci-local-gate

Run the CICD-CTRL-1 local gates before pushing or marking a PR ready. Rule: `.claude/rules/28`. Read/validate only.

## Trigger
- Before opening a draft PR, before marking a PR ready-for-review, or when asked to "run local CI".

## Non-trigger
- Do not use to merge, tag, deploy, or change repository settings.

## Workflow (read-only)
1. During development: `scripts/ci/fast-local.sh` (classifier + gate tests, topology, workflow-security, secret
   scan, rule frontmatter, hook tests, classify current diff).
2. Before ready-for-review: `scripts/ci/full-local.sh` (adds the full documentation-as-code aggregate
   `scripts/docs/validate.sh`).
3. Report PASS/FAIL per gate with the evidence log paths under `docs/evidence/cicd-ctrl-1/local-validation/`.

## Safety boundaries
No repository mutation. Never weaken, skip, or fake a gate. Never mark a failing gate as passing.

## Required output
Per-gate PASS/FAIL, the failing log path if any, and whether the tree is ready for a draft push or ready-for-review.

## Failure behavior
On any FAIL, stop and surface the exact failing gate and log; do not proceed to push/ready.
