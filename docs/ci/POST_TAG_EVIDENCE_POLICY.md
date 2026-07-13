# Post-Tag Evidence Policy — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.4. Rule: `.claude/rules/28`, `13`. ADR 0044. AFR-116,117.

## Default: GitHub Release artifact
Tag creation runs **no full CI**. After the annotated immutable GO tag is pushed and verified,
post-tag evidence is published as a **GitHub Release** with attached verification artifacts:
```
cicd-ctrl-1-tag-verification.txt / .json
cicd-ctrl-1-final-ci.json
cicd-ctrl-1-ruleset-before.json / -after.json
cicd-ctrl-1-run-budget-report.json
cicd-ctrl-1-release-manifest.json
```
This replaces the previous pattern of a second full-CI "evidence-only" pull request, which re-ran the full suite for
no new signal.

## Verification (no full CI)
`scripts/release/verify-immutable-tag.sh <TAG>` proves:
```
local main = origin/main = merge commit = local tag peeled = remote tag peeled
```
and that all prior immutable tags are unchanged. It never moves or deletes a tag.

## Exception
If repository policy later requires evidence committed into Git, it is added to the **next normal planned PR**,
clearly marked as historical release metadata — never via a full-CI run and never by moving the tag. A documented
exception decision explains why the GitHub Release artifact was insufficient.

## Prohibited
- Full CI on tag push or on post-tag evidence (AFR-116, AFR-117).
- Moving, deleting, or recreating a tag to make evidence "look correct" — a mismatch is NO-GO and is corrected only
  by a new, correctly-created tag.
