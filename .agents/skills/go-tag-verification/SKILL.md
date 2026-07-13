---
name: go-tag-verification
description: Verify an annotated GO tag exact-matches the merged commit on local, remote, and default branch, and that prior immutable tags are unchanged. Read-only; never creates/moves/deletes tags.
---

# Skill: go-tag-verification

**Trigger:** After a GO tag is created/pushed, or when auditing tag integrity.
**Non-trigger:** Before merge (nothing to verify yet).
**Inputs:** Tag name, merged commit SHA.

## Workflow (read-only)
```bash
git rev-parse HEAD; git rev-parse origin/main
git rev-parse <tag>; git rev-parse <tag>^{}
git ls-remote origin refs/tags/<tag> refs/tags/<tag>^{}
git describe --tags --exact-match HEAD
```
Confirm local main = origin/main = merged commit = local tag peeled = remote tag peeled. Confirm baseline tags
`docs-foundation-v1.0.0-go` (ba1c80f) and `step-2-persona-pilot-v1.0.0-go` (abf1d00) are unchanged.

## Safety boundaries
Read-only. MUST NOT create, move, delete, or force tags; MUST NOT force-push (AFR-067).

## Required output
`docs/release/STEP_3_ARCHITECTURE_TAG_VERIFICATION.md` with tag object + peeled commit + match table.

## Evidence
`docs/evidence/step-3/git-release/`, `docs/release/STEP_3_ARCHITECTURE_TAG_VERIFICATION.md`.

## Failure behavior
On any mismatch, report `NO-GO: TAG MISMATCH`; never move a tag to fix evidence.
