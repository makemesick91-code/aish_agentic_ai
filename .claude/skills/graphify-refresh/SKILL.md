---
name: graphify-refresh
description: Safely rebuilds the derived knowledge graph/index over documentation and rules, enforces exclusions (no secrets/PII), runs the canonical query-smoke tests, and records graph metadata and source revision. Never commits secrets or oversized generated data.
---

# Skill: graphify-refresh

Use when documentation/rules change and the knowledge index should be refreshed. Implements
`.claude/rules/15` and Master Source §66.7.

## Steps
1. **Rebuild** the graph/index over the allowed scope only: `CLAUDE.md`, `.claude/rules/`, `.claude/agents/`,
   `.claude/skills/`, `docs/`, application source (when it exists), migrations/schema, tests, CI, and ops scripts.
2. **Enforce exclusions:** `.env*`, secrets, credentials, private keys, token files, vendor deps, generated
   build dirs, customer/credential logs, backups/dumps, `node_modules`, and large binaries.
3. **Run query smoke tests** proving retrieval of: product name; mandatory tenant-isolation surfaces;
   actions requiring human approval; Google Review prohibitions; gates required before a GO tag; and which
   document is canonical when the PRD conflicts with the Master Source. Each answer MUST resolve to canonical file paths.
4. **Record metadata:** graph/index version, source git revision, node/edge counts (or file/section counts
   for the deterministic fallback), and exclusion confirmation under `docs/evidence/graphify/`.

## Fallback (branded Graphify unavailable)
When no trusted Graphify skill/CLI/plugin/MCP is installed, use the deterministic documentation index
(`scripts/graphify/build.sh` + `query-smoke.sh`) and mark branded Graphify `BLOCKED-OPTIONAL` in
`docs/tooling/GRAPHIFY.md`. Do not falsely claim the branded product ran.

## Safety
- Source Markdown stays authoritative; the graph is derived and never overrides it.
- MUST NOT index or commit secrets/PII; MUST NOT commit oversized generated graphs (commit config, scripts,
  manifest, and compact evidence instead). MUST NOT run git merge/tag or deployment.
