# MCP Governance — Aish Agentic AI (Step 3)

**Status:** Step 3 governance record · **Canonical:** Master Source v2.3.0 §66.8 · **Rules:** `.claude/rules/15`, `20` ·
**ADR:** [0031](../decisions/adr/0031-dependency-and-supply-chain-governance.md). Extends the Step 1 [MCP.md](MCP.md).

## Principles (AFR-064, AFR-070)
- **Least privilege + allowlist.** Add an MCP only when required, trusted, secret-safe, and relevant. An empty
  server set is valid when nothing is needed.
- **No secrets in config.** `.mcp.json` and `.codex/config.toml` contain no tokens/keys/OAuth secrets/private
  endpoints; credentials are env/secret-manager references only.
- **Loopback / auth for HTTP MCP.** HTTP MCP services bind to loopback by default or require authentication when
  exposed; data scope, tools, risk, and owner are documented.
- **Approval for mutation.** Any MCP/tool that can mutate production, billing, credentials, data deletion,
  deployment, or public content requires explicit additional approval and is default-excluded.
- **No redundant MCP.** MCP is not added merely to look complete. Prefer `gh` for GitHub mutation; GitHub MCP is
  default read-only.
- **Derived tooling never overrides canonical docs** (graph/index is derived only).

## Current decision
No MCP server is committed (`.mcp.json` empty). Session-connected MCP servers, if present, are environment-only
and outside the product trust boundary. Branded Graphify (host binary present) is **not** governance-verified in
this session and is **not** used (OD-05).

## Codex sandbox rationale
`.codex/config.toml` sets `sandbox_mode = "workspace-write"` intentionally: authoring/validation needs to write
within the workspace (docs, evidence). Writes are bounded by `approval_policy = "on-request"` (mutating/external
actions require approval) and by the `.codex/hooks/pre-tool-use.sh` guard + `.codex/rules/` deny-list (force-push,
tag deletion, secret reads, destructive/production commands are blocked). No network side effects occur without
approval. This is not a secret exposure and is governed by `.claude/rules/15` (AFR-070).

## Change process
Adding an MCP requires: provenance/license verification, a manifest row in [MCP_MANIFEST.md](MCP_MANIFEST.md),
a risk/owner note, secret-free config, and — for any mutating capability — explicit approval and a Master Source
impact check.
