# Changelog — Aish Agentic AI (repository)

Repository-level changelog. The canonical **product** changelog lives inside the living Master Source
(`docs/canonical/MASTER_SOURCE.md`, sections 6 and 66) and is governed by `.claude/rules/12`.
This file records repository/documentation-foundation engineering changes.

Format follows [Keep a Changelog](https://keepachangelog.com/) principles; dates use `Asia/Makassar`.

## [Unreleased] — Step 2: Persona and Pilot Use Cases

Target release: annotated tag `aish-agentic-ai-step-2-persona-pilot-v1.0.0-go`. Base branch `main`;
feature branch `docs/step-2-persona-pilot-use-cases`.

### Added
- Imported canonical Step 2 sources and set living copies: Master Source **v2.2.0**
  (`docs/canonical/MASTER_SOURCE.md`), PRD **v1.1.0** (`docs/canonical/PRD.md`), and Persona & Pilot Use
  Cases **v1.0.0** (`docs/product/PERSONA_AND_PILOT_USE_CASES.md`). Originals preserved byte-for-byte in
  `docs/canonical/source/`; checksums + manifest updated.
- Pilot derived documentation: `docs/product/PILOT_*` (scope, persona matrix, use-case catalog, journeys,
  workflow states, success metrics, readiness checklist, GO/WATCH/NO-GO, RACI); `docs/security/PILOT_*`
  (data boundary, privacy rules, public-reply safety, threat & abuse cases); `docs/ai/PILOT_*` (human
  approval rules, evaluation plan, manual fallback); `docs/integrations/*` (DaengtisiaMS event contract,
  Google Business Profile pilot readiness, WhatsApp invitation baseline); `docs/testing/*` (Step 2 RTM,
  pilot acceptance test catalog, UAT plan).
- New enforceable rules `.claude/rules/16`–`19` (pilot persona/scope; invitation/survey/fallback;
  privacy/approval/review-safety; metrics/evidence/GO-WATCH-NO-GO); `CLAUDE.md` Step 2 index update.
- Step 2 coverage matrix (`docs/quality/STEP_2_COVERAGE_MATRIX.md`) and Step 2 validation gate
  (`scripts/docs/check-step2-coverage.sh`); extended version-consistency, query-smoke (8 Step 2 queries),
  `validate.sh`, and CI.
- ADR 0008 (Step 2 persona & pilot baseline); decision-log, version-matrix, and changelog updates.
- Step 2 release docs and evidence under `docs/release/STEP_2_*` and `docs/evidence/step-2/`.

### Notes
- Pilot operational targets are **hypotheses**, not results. First pilot tenant Klinik Gigi Daengtisia;
  recommended first branch Daengtisia Pusat (recommendation only, subject to readiness verification).
- **Application implementation, deployment, pilot readiness, and pilot runtime: NOT STARTED.**

## Documentation & Claude Rules Foundation — MERGED and GO TAGGED (Step 1)

Released as annotated tag `aish-agentic-ai-docs-foundation-v1.0.0-go` (peeled commit `ba1c80f`).

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
