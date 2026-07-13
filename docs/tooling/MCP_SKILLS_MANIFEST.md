# MCP, Skills, Subagents, and Hooks Manifest — Aish Agentic AI

Canonical: Master Source §66.7–§66.9. Rule: `.claude/rules/15`, `14`. Single source of the tooling inventory.

## MCP servers (committed governance)
`../../.mcp.json` commits an **empty** `mcpServers` set with allow/deny governance and no secrets. Rationale
and environment inventory: `MCP.md`. No MCP is required to be committed for the documentation foundation.

## Project skills (`.claude/skills/`)
| Skill | Purpose | Mutating? |
|-------|---------|-----------|
| `master-source-update` | Versioned living Master Source updates | Edits docs only; no git/CI/tag |
| `documentation-gate` | Runs all documentation-as-code gates | Validation only |
| `release-evidence` | Gathers branch/PR/CI/merge/tag evidence | Read-only inspection |
| `graphify-refresh` | Rebuilds derived knowledge index + smoke tests | Build/validate; no merge/tag |

## Review subagents (`.claude/agents/`) — least privilege, report-only
`product-requirements-reviewer`, `architecture-reviewer`, `security-privacy-reviewer`,
`ai-governance-reviewer`, `qa-traceability-reviewer` → tools: Read, Grep, Glob.
`release-governance-reviewer` → tools: Read, Grep, Glob, Bash (**read-only** git/gh inspection only).
None may merge, publish, tag, force-push, or run destructive operations.

## Hooks (`.claude/settings.json`)
- PreToolUse(Bash) → `scripts/hooks/guard-dangerous-commands.sh`: blocks force-push, remote-ref/tag
  deletion, tag moving, history rewrite, `.env`/private-key reads via shell, and reckless recursive delete.
- Validated by `scripts/hooks/test-guard.sh` (positive + negative cases).

## Token-saving skill
`Limit Saver 1` / `usage-limit-reducer` — **not installed** in this environment; documented fallback in
`LIMIT_SAVER.md` (`.claude/rules/14`). Recorded as a missing optional dependency, not silently installed.

## Knowledge graph
Graphify branded skill/CLI/plugin/MCP — **not installed**; realized via a deterministic documentation index
(`GRAPHIFY.md`, `scripts/graphify/`). Marked `BLOCKED-OPTIONAL` for the branded product.
