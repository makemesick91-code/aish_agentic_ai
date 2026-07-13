# ADR 0004 — Minimal MCP Allowlist and Secrets Policy

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/15`, `04` · **Canonical:** Master Source §66.8

## Context
MCP servers expand tool surface and risk. Several exist in the environment (context-mode, Supabase,
Higgsfield); GitHub operations are available via authenticated `gh`.

## Decision
Use the smallest sufficient MCP set. Commit a project `.mcp.json` with an **empty** `mcpServers` set and
documented allow/deny governance — no MCP server is required to be committed for the documentation
foundation. Never commit secrets; credentials are env-var references. HTTP MCP binds loopback or requires
auth. Any MCP that can mutate production/billing/credentials/data/public content requires explicit approval.

## Alternatives considered
- Commit all environment MCPs — rejected: unnecessary surface, redundant, risks secrets.
- Add a GitHub MCP now — rejected: `gh` (keyring token) already covers branch/PR/CI/merge/release safely.

## Consequences
Minimal attack surface; `.mcp.json` is safe to commit and review. New MCPs require a governance entry.

## Security impact
No secrets in the repo; least privilege by default; push-protection secret scanning enabled.

## Migration impact
Adding a real MCP later updates `.mcp.json` + `docs/tooling/MCP.md` with scope/risk/owner.

## Supersession
Superseded by a Master Source update that adds a justified MCP with governance.
