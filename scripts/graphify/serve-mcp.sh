#!/usr/bin/env bash
# serve-mcp.sh — serve the knowledge graph over MCP WHEN a trusted Graphify MCP is installed.
# In this environment no branded Graphify MCP is installed (status: BLOCKED-OPTIONAL). This is a safe,
# explicit stub: it does NOT fabricate an MCP server. Rule: .claude/rules/15. Docs: docs/tooling/GRAPHIFY.md
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

cat <<'MSG'
[graphify serve-mcp] Branded Graphify MCP server is NOT installed in this environment (BLOCKED-OPTIONAL).
No MCP server is started (a real server is not fabricated). The derived documentation index is available
via the deterministic build/query scripts instead:

    scripts/graphify/build.sh        # rebuild the deterministic index manifest
    scripts/graphify/query-smoke.sh  # run the canonical query-smoke set

To enable a real Graphify MCP later:
  1) install a trusted, pinned Graphify build (record source, version/commit, license, checksum);
  2) add its server to .mcp.json (no secrets; env-var credentials; loopback or authenticated);
  3) update docs/tooling/GRAPHIFY.md status and re-run the query-smoke set.
MSG
exit 0
