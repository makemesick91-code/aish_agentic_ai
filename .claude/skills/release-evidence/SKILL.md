---
name: release-evidence
description: Gathers release evidence — branch and clean-worktree state, commit SHAs, PR and CI status, merge commit, annotated tag object and peeled commit, and local/remote/default-branch exact-match verification — into docs/evidence/ and docs/release/. Read-only inspection; never merges, tags, or force-pushes.
---

# Skill: release-evidence

Use during PR, merge, and GO-tag phases. Implements `.claude/rules/13` and Master Source §66.10–§66.11.

## Collect (read-only)
```bash
git remote -v                                 # redact any credential in URL
git status --porcelain                        # clean-worktree evidence
git rev-parse HEAD                            # current commit
git log --oneline -n 20
gh pr view --json number,url,state,mergeable,reviewDecision
gh run list --branch <branch> --limit 10
gh pr view --json mergeCommit                 # merge commit SHA after merge
git rev-parse "aish-agentic-ai-docs-foundation-v1.0.0-go"      # tag object SHA
git rev-parse "aish-agentic-ai-docs-foundation-v1.0.0-go^{commit}"  # peeled commit
git ls-remote origin refs/tags/aish-agentic-ai-docs-foundation-v1.0.0-go
git ls-remote origin refs/heads/main
```

## Verify
- Normalized origin = `makemesick91-code/aish_agentic_ai`.
- Tag is **annotated**, points to the merged commit, and exact-matches local + remote + default-branch HEAD.
- CI required jobs are green; PR review evidence recorded; no unresolved critical/high issue.
- Prior tags unchanged; no force-push occurred.

## Output
Write `docs/release/TAG_VERIFICATION.md` and machine-readable evidence under `docs/evidence/git-release/`
and `docs/evidence/ci/`. Redact tokens/credentials/PII.

## Safety
Inspection only. This skill MUST NOT run `git push`, `git merge`, `git tag`, `gh pr merge`, `gh release`,
force-push, or tag deletion/moving. It reports the highest truthful state and exact blockers.
