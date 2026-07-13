---
id: "13"
title: Git, CI, Release, and GO Tag
domain: release
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §66.2, §66.10, §66.11"
  - "PRD §24; GO Tag Prompt v1.0.1"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 13 — Git, CI, Release, and GO Tag

## Purpose
Enforce a safe branch → PR → CI → review → merge → immutable annotated GO-tag workflow.

## Scope
Branching, commits, pull requests, CI gates, merge, and release tagging.

## Rules
- Normalized `origin` **MUST** be verified as `makemesick91-code/aish_agentic_ai` before any git write.
- Work **MUST NOT** occur directly on the protected default branch, except the documented one-time minimal
  empty-repository bootstrap needed to establish a PR base (which **MUST NOT** receive a GO tag).
- Foundation work **MUST** use branch `chore/aish-agentic-ai-documentation-foundation` and be delivered via PR.
- CI (`.github/workflows/documentation-foundation.yml`) **MUST** run docs validation, link checks, version
  consistency, foundation coverage, rule validation, secret scanning, Graphify build/query smoke (when
  CI-safe), generated-artifact drift, and shell lint. Existing CI gates **MUST NOT** be removed or weakened.
- Merge is permitted **only** when: required CI is green, branch protection satisfied, no unresolved
  critical/high issue, all foundation gates pass, working tree clean, and PR review evidence recorded.
- The GO tag **MUST** be the annotated tag `aish-agentic-ai-docs-foundation-v1.0.0-go`, created **only** on
  the merged commit after the default branch contains the complete change, and **MUST** exact-match that commit
  on local, remote, and default branch.
- `git push --force`, `git tag -f`, tag deletion/moving, and history rewriting **MUST NOT** be used.
  An existing tag of the same name pointing elsewhere is `NO-GO`.
- If remote/PR/merge/CI/tag permission is unavailable, report the highest truthful state + exact blocker;
  **MUST NOT** fabricate a local GO claim.

## Required checks
- `release-evidence` skill gathers branch, commit, PR, CI, merge, and tag object/peeled-commit evidence.
- Tag verification recorded in `docs/release/TAG_VERIFICATION.md`.

## Evidence
- `docs/release/DOCUMENTATION_FOUNDATION_GO_NO_GO.md`, `RELEASE_MANIFEST.md`, `TAG_VERIFICATION.md`;
  `docs/evidence/ci/`, `docs/evidence/git-release/`.

## Related canonical sections
- Master Source §66.2, §66.10 (doc gates), §66.11 (GO tag scope); GO Tag Prompt v1.0.1.

## Supersession
The GO tag is immutable. Release process changes require an owner-approved Master Source update.
