# MCP Manifest — Aish Agentic AI

**Status:** Step 3 governance record · **Rules:** `.claude/rules/15`, `20` · **ADR:** [0031](../decisions/adr/0031-dependency-and-supply-chain-governance.md) ·
Companion: [MCP.md](MCP.md), [MCP_SKILLS_MANIFEST.md](MCP_SKILLS_MANIFEST.md), [MCP_GOVERNANCE.md](MCP_GOVERNANCE.md).

## Committed MCP set
`.mcp.json` = **empty server set** (`{ "mcpServers": {} }`). This is a **valid** least-privilege configuration:
no MCP server is required for the Step 3 documentation/architecture work, so none is added (AFR-070).

| Server | Source | Purpose | Tools | Permission | Secret handling | Decision |
|--------|--------|---------|-------|------------|-----------------|----------|
| (none committed) | — | — | — | — | — | Empty set retained — nothing needed |

## Session-connected MCP (not committed; environment-provided)
Some MCP servers may be available in a developer's session (e.g. read-only documentation servers). These are
**not** committed to the repository and are **not** part of the product's trust boundary. Governance:
- No MCP may access the tenant data plane.
- Any MCP that could mutate production/billing/credentials/deletion/deploy/public content **requires explicit
  additional approval** and is default-excluded.
- Prefer `gh` for GitHub mutation; GitHub MCP (if any) is default read-only.
- No token/OAuth secret/private endpoint is committed via MCP config.

## Decision
Add MCP only when required, trusted, least-privilege, secret-safe, and relevant. The empty set is correct for
Step 3. See [MCP_GOVERNANCE.md](MCP_GOVERNANCE.md).

## Step 4 note (2026-07-13)
No MCP server was added for Step 4. Domain-availability (RDAP) and dependency-version research used read-only
network tools only; no MCP server, registrar/cloud credential, or secret was committed. The committed `.mcp.json`
empty server set and least-privilege governance are unchanged (rule 15; AFR-070).

## CICD-CTRL-1 note (2026-07-13)
No MCP server was added for CICD-CTRL-1. GitHub CI/workflow/ruleset operations use the `gh` CLI (clearer audit
trail than an MCP mutation surface). The committed `.mcp.json` empty server set and least-privilege governance are
unchanged (rule 15, 28; AFR-070). No MCP is granted branch/tag deletion, deployment, or secret access.
