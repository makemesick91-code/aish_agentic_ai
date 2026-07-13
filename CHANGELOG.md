# Changelog — Aish Agentic AI (repository)

Repository-level changelog. The canonical **product** changelog lives inside the living Master Source
(`docs/canonical/MASTER_SOURCE.md`, sections 6 and 66) and is governed by `.claude/rules/12`.
This file records repository/documentation-foundation engineering changes.

Format follows [Keep a Changelog](https://keepachangelog.com/) principles; dates use `Asia/Makassar`.

## Documentation & Claude Rules Foundation — GO TAGGED (2026-07-13)

Released as annotated tag `aish-agentic-ai-docs-foundation-v1.0.0-go` on merged commit `ba1c80f`
(PR #1; CI run 29214927784 = success). Exact-match verified on local + remote. This tag attests
documentation/tooling readiness only — application implementation is NOT STARTED.

### Added
- Repository bootstrap: `README.md`, `.gitignore`, `SECURITY.md`, `CONTRIBUTING.md`.
- Root `CLAUDE.md` instruction index and source-authority map.
- Modular `.claude/rules/` (16 enforceable rule files), `.claude/settings.json`, `.claude/README.md`.
- Project review subagents (`.claude/agents/`) and project skills (`.claude/skills/`).
- MCP governance (`.mcp.json`, `docs/tooling/MCP.md`, `docs/tooling/MCP_SKILLS_MANIFEST.md`).
- Knowledge-graph (Graphify) configuration, build/query scripts, and evidence
  (`graphify.yaml`, `scripts/graphify/`, `docs/tooling/GRAPHIFY.md`).
- Canonical documentation architecture under `docs/` (canonical, product, architecture, security, ai,
  integrations, quality, operations, tooling, decisions, status, release).
- ADRs, Foundation Coverage Matrix, Requirements Traceability Matrix.
- Documentation-as-code validation scripts (`scripts/docs/`) and CI workflow
  (`.github/workflows/documentation-foundation.yml`).
- Audit evidence under `docs/evidence/` (source checksums, import manifest, inventory, validation, CI, git-release).

### Notes
- Preserved canonical source originals in `docs/canonical/source/` (Master Source v2.1.1, PRD v1.0.x,
  historical Master Source v2.0.0).
- **Application implementation status: NOT STARTED.** This foundation does not claim the product is
  built, deployed, pilot-ready, or production-ready.
