# Claude Project Setup — Aish Agentic AI

How Claude Code persistent context is organized in this repository. Rule: `.claude/rules/14`, `15`.

## Layout
- `CLAUDE.md` (root) — concise instruction index + authority map (loaded first).
- `.claude/rules/` — 16 modular enforceable rules; read the relevant file per area.
- `.claude/agents/` — 6 least-privilege review subagents (report-only).
- `.claude/skills/` — 4 project skills (`master-source-update`, `documentation-gate`, `release-evidence`, `graphify-refresh`).
- `.claude/settings.json` — permission policy (allow/ask/deny) + PreToolUse safety hook.
- `scripts/hooks/guard-dangerous-commands.sh` — blocks force-push, tag deletion/moving, secret reads, reckless deletes.
- `docs/status/` — current state, handoff, session checkpoints (updated each phase).

## Discovery & validation
- Installed Claude Code version at foundation time: **2.1.179** (see `../evidence/inventory/`).
- `CLAUDE.md` is discovered at repo root; rules are referenced from it and read on demand (Limit Saver, `.claude/rules/14`).
- Validate configuration: `scripts/docs/check-rule-frontmatter.sh`, `scripts/hooks/test-guard.sh`,
  and the aggregate `scripts/docs/validate.sh`.

## Principles
- Detailed instructions live in modular files, not in one large startup file (Limit Saver fallback).
- Skills perform no unsafe automatic mutation; subagents never merge/publish/tag/delete.
- No secrets anywhere (`SECURITY.md`, `.claude/rules/04`).

See also: `MCP.md`, `MCP_SKILLS_MANIFEST.md`, `GRAPHIFY.md`, `LIMIT_SAVER.md`.
