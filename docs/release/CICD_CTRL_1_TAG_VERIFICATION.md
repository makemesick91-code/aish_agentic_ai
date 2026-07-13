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

## Result (filled at release time)
| Field | Value |
|-------|-------|
| Tag | `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go` |
| local main | _recorded at release_ |
| origin/main | _recorded at release_ |
| merge commit | _recorded at release_ |
| tag object | _recorded at release_ |
| tag peeled commit | _recorded at release_ |
| remote tag peeled | _recorded at release_ |
| exact_match | _recorded at release_ |
| prior tags unchanged | _recorded at release_ |

Until the tag exists, the truthful state is "release execution IN PROGRESS"; a GO/exact-match claim is made only
with the recorded evidence above. Tags are never moved, deleted, or recreated.
