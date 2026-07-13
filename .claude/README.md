# `.claude/` — Claude Code project configuration

This directory is the persistent, version-controlled Claude project memory for **Aish Agentic AI**.
It exists so decisions survive across sessions and are never held only in chat history.

## Contents

| Path | Purpose |
|------|---------|
| `../CLAUDE.md` | Concise root instruction index + source-authority map (loaded first). |
| `rules/` | 16 modular, enforceable rule files (`MUST`/`MUST NOT`/`SHOULD`/`MAY`) covering every permanent foundation. Read the file for the area you are working in. |
| `agents/` | 6 least-privilege review subagents (product, architecture, security, AI governance, QA/traceability, release governance). They review and report; they never merge, publish, tag, or run destructive operations. |
| `skills/` | 4 project skills for repeatable workflows: `master-source-update`, `documentation-gate`, `release-evidence`, `graphify-refresh`. |
| `settings.json` | Validated permission policy (allow/ask/deny) + PreToolUse safety hook. |

## How the pieces relate

1. `CLAUDE.md` is the entry point and points to `rules/`.
2. `rules/` distills the canonical Master Source (`docs/canonical/MASTER_SOURCE.md`) and PRD
   (`docs/canonical/PRD.md`) into enforceable behavior. Every permanent decision is traceable via
   `docs/quality/FOUNDATION_COVERAGE_MATRIX.md`.
3. `skills/` and `agents/` operationalize repeatable governance and review.
4. `settings.json` + `../scripts/hooks/guard-dangerous-commands.sh` enforce the highest-risk denials
   (force-push, tag deletion/moving, secret reads, destructive deletes).

## Safety invariants

- No secrets are stored here or anywhere in the repository (`SECURITY.md`, `rules/04`, `rules/15`).
- Skills perform **no** unsafe automatic mutation; subagents are read/report-only.
- The knowledge graph (Graphify) is derived and never overrides canonical documents.

Validate this configuration with `scripts/docs/check-rule-frontmatter.sh`, `scripts/hooks/test-guard.sh`,
and the aggregate `scripts/docs/validate.sh`.
