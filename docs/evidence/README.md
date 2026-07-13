# Evidence — Aish Agentic AI Documentation Foundation

Rule: `.claude/rules/09`, `13`. Audit evidence for the documentation-foundation release. Tokens,
credentials, and PII are redacted. Machine- and human-readable outputs are stored here.

| Directory | Contents |
|-----------|----------|
| `source-checksums/` | SHA-256 of canonical sources (`SHA256SUMS.txt`) + import manifest. |
| `inventory/` | Claude version, skills/plugins/subagents/MCP inventory, Limit Saver + Graphify status. |
| `validation/` | Documentation-gate outputs and exit codes (version consistency, links, rule frontmatter, coverage, secret scan, hook tests, aggregate). |
| `graphify/` | Deterministic knowledge-index build manifest + query-smoke results. |
| `ci/` | CI run URLs and job conclusions. |
| `git-release/` | Remote verification (redacted), branch/commit history, PR, merge commit, tag object + peeled commit, exact-match verification, clean-worktree. |

## Truthfulness
Evidence is captured from real command output — never fabricated. Where a check is not applicable yet
(e.g. application test suites), it is marked NOT STARTED rather than claimed complete. See
`../release/DOCUMENTATION_FOUNDATION_GO_NO_GO.md` for the consolidated GO/NO-GO decision.
