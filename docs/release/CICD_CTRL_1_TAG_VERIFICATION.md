# CICD-CTRL-1 — Tag Verification

Canonical: Master Source v2.5.0 §69.4. Rule: `.claude/rules/28`, `13`. ADR 0044.
Target tag `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go`.

## Verification method
`scripts/release/verify-immutable-tag.sh <TAG>` proves exact-match and prior-tag immutability, writing
`tag-verification.txt` / `.json` to `docs/evidence/cicd-ctrl-1/release/`. Required equalities:
```
local main = origin/main = merge commit = local tag peeled commit = remote tag peeled commit
```
The tag MUST be **annotated** (tag object ≠ commit) and created only after merge and the lightweight post-merge
verification. No full CI runs on the tag.

## Result (verified 2026-07-13, Asia/Makassar)
| Field | Value |
|-------|-------|
| Tag | `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go` |
| local main | `8cbf564321c44a5fb3d89826b0a895c9aae27c70` |
| origin/main | `8cbf564321c44a5fb3d89826b0a895c9aae27c70` |
| merge commit (PR #9) | `8cbf564321c44a5fb3d89826b0a895c9aae27c70` |
| tag object (annotated) | `abf0dbe0d9e8108fd79c9af5d20bbe7b5ae6366e` |
| tag peeled commit | `8cbf564321c44a5fb3d89826b0a895c9aae27c70` |
| remote tag peeled | `8cbf564321c44a5fb3d89826b0a895c9aae27c70` |
| exact_match | **true** |
| prior tags unchanged | **true** (docs-foundation, step-2, step-3, step-4 peeled SHAs match recorded known-good) |
| full CI on tag | **none** (verified `total_count=0` for the tag object) |

GO exact-match verified. Tag object ≠ peeled commit confirms it is annotated. Tags are never moved, deleted, or
recreated. Machine-readable copy: `docs/evidence/cicd-ctrl-1/release/tag-verification.json`.
