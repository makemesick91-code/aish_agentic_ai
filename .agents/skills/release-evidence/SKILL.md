---
name: release-evidence
description: Gather release evidence — branch/clean-worktree, commit SHAs, PR/CI status, merge commit, annotated tag object + peeled commit, and local/remote/default-branch exact-match verification. Read-only; never merges, tags, or force-pushes.
---

# Skill: release-evidence

**Trigger:** PR, merge, and GO-tag phases.
**Non-trigger:** Early authoring.
**Inputs:** Branch, PR number, tag name.

## Workflow (read-only)
```bash
git remote -v                 # redact credentials
git status --porcelain
git rev-parse HEAD; git log --oneline -n 20
gh pr view --json number,url,state,mergeable,reviewDecision
gh run list --branch <branch> --limit 10
gh pr view --json mergeCommit
git rev-parse <tag>; git rev-parse <tag>^{}
git ls-remote origin refs/tags/<tag> refs/heads/main
```

## Safety boundaries
Inspection only. MUST NOT run `git push`, `git merge`, `git tag`, `gh pr merge`, `gh release`, force-push, or
tag deletion/moving (AFR-067). Redact tokens/credentials/PII.

## Required output
Release manifest + tag verification + machine-readable evidence.

## Evidence
`docs/release/STEP_3_ARCHITECTURE_RELEASE_MANIFEST.md`, `STEP_3_ARCHITECTURE_TAG_VERIFICATION.md`, `docs/evidence/step-3/git-release/`.

## Failure behavior
On missing permission or a mismatch, report the highest truthful state + exact blocker; never fabricate a GO.
