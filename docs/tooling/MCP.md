# MCP Governance — Aish Agentic AI

Canonical: Master Source §66.8. Rule: `.claude/rules/15`. GO Tag Prompt v1.0.1 (MCP requirements).

## Policy
Use the **smallest sufficient set** of MCP servers; never add an MCP merely because it exists; never commit
secrets. Credentials are env-var references. HTTP MCP binds to loopback by default or requires authentication.
Any MCP that can mutate production, billing, credentials, data deletion, deployment, or public content
requires explicit additional approval.

## Project-scoped `.mcp.json`
This repository commits a **minimal, secret-free** `../../.mcp.json` (validated against the installed Claude
Code schema). For the documentation foundation, **no project MCP server is required** to be committed — the
work uses built-in file tools and authenticated `gh`. The committed `.mcp.json` therefore defines an empty
`mcpServers` set plus documented allow/deny governance, so no unnecessary or secret-bearing server is added.

## Environment MCP inventory (not committed; developer-local)
Observed at foundation time (see `../evidence/inventory/mcp-inventory.txt`):

| Server | Transport | Use | Committed? | Risk / control |
|--------|-----------|-----|-----------|----------------|
| context-mode | local/stdio (plugin) | Local context/search efficiency | No | Local-only; read/analyze; no repo secrets |
| GitHub (via `gh` CLI) | authenticated CLI | Branch/PR/CI/merge/release evidence | No (uses `gh` auth, not `.mcp.json`) | Token in keyring, never committed |
| Supabase | remote HTTP | Not used by this foundation | No | Excluded — not needed |
| Higgsfield | remote HTTP | Not used by this foundation | No | Excluded — not needed |

## Preferred capability set (when actually needed later)
Graphify MCP (knowledge retrieval) · GitHub MCP or `gh` · Browser/Playwright (doc/OAuth validation) ·
Context7 (official docs) · PostgreSQL (read-only schema) · Filesystem (only if built-ins insufficient) ·
Observability (only with a real platform + secure creds). Each requires justification + governance entry.

See the consolidated `MCP_SKILLS_MANIFEST.md`.
