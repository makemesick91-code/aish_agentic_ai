# .codex/ — Codex Project Configuration (Aish Agentic AI)

Project-scoped Codex configuration, command-safety rules, and hooks. Aligned with `CLAUDE.md`, `.claude/rules/`,
and `AGENTS.md` — **one** source of truth (AFR-069). No secrets are stored here.

> **Status:** The Codex CLI is **not installed** in the authoring environment. These artifacts are authored and
> **statically validated** (`scripts/codex/check-codex.sh`). `codex execpolicy check ...` commands are
> **documented, not executed** — no runtime output is fabricated (tracked as OD-07).

## Contents
| Path | Purpose |
|------|---------|
| `config.toml` | project-safe config: `approval_policy=on-request`, `sandbox_mode=workspace-write`, `web_search=cached` |
| `rules/*.rules` | `prefix_rule()` command-safety policies (forbidden / prompt / safe) with inline tests |
| `hooks.json` | hook registration (SessionStart, PreToolUse, PostToolUse, PreCompact, Stop) |
| `hooks/*.sh` | deterministic, secret-safe, network-free hook scripts |

## Precedence & trust
1. Project config applies **only after the project is trusted** in Codex.
2. Authority order and rules come from `AGENTS.md` → `.claude/rules/` → `.codex/rules/` (execution safety).
3. `.codex/rules/` enforces command execution; `AGENTS.md` provides semantic instructions; both defer to the
   canonical Master Source / PRD / ADRs.

## Sandbox & approval
- `workspace-write` sandbox: writes limited to the workspace; no network side effects without approval.
- `on-request` approval: mutating/external actions (push, tag, PR create/merge, installs) require explicit approval.

## AGENTS.md / rules / hooks / MCP
- Semantic instructions: [../AGENTS.md](../AGENTS.md) and nested `docs/*/AGENTS.md`.
- Command safety: `rules/` (below). Hooks: `hooks.json`. MCP governance: [../docs/tooling/MCP_GOVERNANCE.md](../docs/tooling/MCP_GOVERNANCE.md).

## Validation
```bash
scripts/codex/check-codex.sh     # static: config parse, rules present, positive/negative test annotations, hook parse
# When Codex is installed (OD-07), additionally run per rule file:
#   codex execpolicy check --pretty --rules .codex/rules/<file>.rules -- <command>
```

## Troubleshooting
- If Codex ignores project config: confirm the project is **trusted**.
- If a safe command is blocked: check `.codex/rules/` prefix match; adjust the rule + add a positive test.
- Never disable a safety rule to unblock a task; escalate via a governance change (owner-approved).
