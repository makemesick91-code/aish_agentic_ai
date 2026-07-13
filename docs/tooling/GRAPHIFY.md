# Graphify — Derived Knowledge Graph — Aish Agentic AI

Canonical: Master Source §66.7. Rule: `.claude/rules/15`. GO Tag Prompt v1.0.1 (Graphify requirements).

## Status: branded Graphify = BLOCKED-OPTIONAL; deterministic index = IMPLEMENTED
Detection found **no** installed Graphify skill, CLI, plugin, or MCP server, and **no verified/pinned
Graphify source** to install from (installing an unknown package is prohibited by policy). Per the GO Tag
Prompt, this is recorded as `BLOCKED-OPTIONAL` for the branded product, and Graphify's required role — a
**derived** knowledge graph over documentation and rules — is realized with a deterministic, reproducible
documentation index. We do **not** claim the branded Graphify product ran.

## Deterministic index (fallback realization)
- Config: `../../graphify.yaml` (scope, exclusions, canonical query set).
- Build: `../../scripts/graphify/build.sh` → emits a compact manifest of nodes (files/sections) and edges
  (links/canonical references) under `../../scripts/graphify/out/` (git-ignored) + committed evidence in `../evidence/graphify/`.
- Query smoke: `../../scripts/graphify/query-smoke.sh` — deterministic grep/index retrieval.
- MCP serve: `../../scripts/graphify/serve-mcp.sh` — present but a no-op stub unless a real Graphify MCP is installed.

## Scope (Master Source §66.7)
Included: `CLAUDE.md`, `.claude/rules/`, `.claude/agents/`, `.claude/skills/`, `docs/`, application source
(when it exists), migrations/schema, tests, CI/CD, ops scripts.
Excluded: `.env*`, secrets, credentials, private keys, token files, vendor deps, generated build dirs,
customer/credential logs, backups/dumps, `node_modules`, large binaries.

## Canonical query smoke (must resolve to canonical file paths)
1. Official product name → `docs/canonical/MASTER_SOURCE.md` §7 / `CLAUDE.md`.
2. Mandatory tenant-isolation surfaces → `.claude/rules/03` / `docs/security/TENANT_ISOLATION.md`.
3. Actions requiring human approval → `docs/ai/HUMAN_APPROVAL_MATRIX.md` / `.claude/rules/05`.
4. Prohibited in Google Review workflows → `docs/integrations/google/GOOGLE_REVIEW_POLICY.md` / `.claude/rules/06`.
5. Gates required before a GO tag → `docs/quality/RELEASE_GATES.md` / `.claude/rules/13`.
6. Canonical doc when PRD conflicts with Master Source → `docs/canonical/DOCUMENT_AUTHORITY.md` (Master Source wins).

## Governance
Source Markdown stays authoritative; the index is derived and never overrides it. No secrets/PII indexed;
large generated graphs are not committed (config, scripts, manifest, and compact evidence are).
To install real Graphify later: record source repo, pinned version/commit, license, install command,
checksum/lockfile, and files/permissions touched (`.claude/rules/15`), then flip this status.
