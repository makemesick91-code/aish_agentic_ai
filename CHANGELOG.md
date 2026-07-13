# Changelog — Aish Agentic AI (repository)

Repository-level changelog. The canonical **product** changelog lives inside the living Master Source
(`docs/canonical/MASTER_SOURCE.md`, sections 6 and 66) and is governed by `.claude/rules/12`.
This file records repository/documentation-foundation engineering changes.

Format follows [Keep a Changelog](https://keepachangelog.com/) principles; dates use `Asia/Makassar`.

## [Unreleased] — CICD-CTRL-1: Safe CI Runtime Control and Single-Final-Head Release Gate

Target release: annotated tag `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go`.
Base branch `main`; feature branch `chore/cicd-ctrl-1-safe-ci-runtime-control`.

### Added
- Master Source **v2.5.0** (§69 CICD-CTRL-1; NFR-CI-001..006); PRD unchanged at **v1.3.0**. ADRs **0042–0046**,
  **AFR-105..126**, **Claude rule 28**.
- Unified `pr-ci.yml` (draft⇒fast CI, ready⇒one full release CI on the final head, internal fail-closed change
  routing, stable `pr-ci / Required Gate` with `if: always()`, per-PR concurrency cancellation, pinned actions,
  least-privilege `contents: read`).
- `main-post-merge.yml` (lightweight integrity only) and `full-ci-manual.yml` (`workflow_dispatch`); old
  `documentation-foundation.yml` retired and preserved as non-executable evidence.
- `scripts/ci/`: `classify-changes.sh` + tests, `required-gate-decision.sh` + tests, `validate-ci-topology.sh`,
  `validate-workflow-security.sh`, `fast-local.sh`, `full-local.sh`, `audit-ci-runs.sh`; `scripts/release/verify-immutable-tag.sh`.
- CI docs (`docs/ci/*`), CICD-CTRL-1 quality docs (traceability, validation catalog, GO/WATCH/NO-GO), release docs,
  and baseline/evidence under `docs/evidence/cicd-ctrl-1/`.

### Changed
- `check-version-consistency.sh` (active Master Source v2.5.0 + snapshot), `check-agents.sh` (v2.5.0),
  `check-adr.sh` (sequence 0001..0046). Query-smoke extended with CICD-CTRL-1 questions.

### Truthful status
- CI/release-process governance only. A CI PASS is valid only for the exact tested SHA; reruns after failures are
  legitimate and reported truthfully. Application implementation, deployment, pilot readiness, pilot runtime, and
  production readiness remain **NOT STARTED**. No domain owned, no package installed, nothing deployed.

## [Unreleased] — Step 4: Domain, Branding, Environment, and SaaS Foundation Planning

Target release: annotated tag `aish-agentic-ai-step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go`.
Base branch `main`; feature branch `docs/step-4-domain-branding-environment-saas-foundation-planning`.

### Added
- Canonical version bumps: Master Source **v2.4.0** and PRD **v1.3.0** (Step 4 planning baseline; §68).
- Domain strategy docs (`docs/domain/`) with point-in-time RDAP availability evidence (all 7 candidates AVAILABLE
  2026-07-13; **no domain owned/claimed**); preferred `aishagentic.ai` + fallbacks; DNS/TLS/email + OAuth redirect plan.
- Brand foundation (`docs/brand/`) + machine-readable planning tokens (`docs/brand/tokens/brand-tokens.v1.json`,
  WCAG 2.2 AA target, not implemented in UI); working tagline (APPROVED WORKING BASELINE).
- Environment strategy (`docs/environments/`): six environments, isolation, synthetic-default data policy,
  configuration/secret matrix, promotion policy, local/CI/staging/pilot/production plans.
- Dependency baseline + supply-chain governance (`docs/dependencies/`); nothing installed, no lock file.
- SaaS Foundation implementation plan (`docs/planning/`): 16 epics (EPIC-SF-01..16), 9 sprints (SPRINT-SF-00..08),
  DoR/DoD, test/evidence plan, risk register, cost model, first sprint SPRINT-SF-00.
- **ADRs 0033–0041** (9 decisions) with required sections + non-claims; **AFR-073..104**; **Claude rules 21–27**.
- Step 4 gates: `check-step4-coverage.sh`, `check-brand-tokens.sh`; new query-smoke queries; wired into
  `validate.sh` + CI. Operations Step 4 plans; Step 4 quality docs (RTM, rule coverage, validation catalog, GO/WATCH/NO-GO).

### Truthful status
- Planning/documentation baseline only. No domain owned, no package installed, nothing deployed. Application
  implementation, deployment, pilot readiness, pilot runtime, and production readiness remain **NOT STARTED**.

## [Unreleased] — Step 3: Application Architecture and ADR Foundation

Target release: annotated tag `aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go`. Base branch
`main`; feature branch `docs/step-3-application-architecture-adr-foundation`.

### Added
- Canonical version bumps: Master Source **v2.3.0** and PRD **v1.2.0** (Step 3 architecture baseline).
- 20 architecture documents under `docs/architecture/` (application baseline, repository layout, module
  boundaries, dependency + data-ownership matrices, tenancy, identity/access, database, event-driven, outbox/
  idempotency/retry, API/webhook, AI service boundary, frontend, environment, deployment topology,
  observability, backup/restore/rollback, fitness functions, open decisions, Application Foundation Rules).
- **ADRs 0009–0032** (24 architecture decisions) with required sections, impacts, fitness functions, and
  explicit non-claims.
- **Application Foundation Rules** `AFR-001..072` + machine-checkable traceability (ADR ↔ AFR ↔ AGENTS ↔ rule ↔
  fitness function ↔ evidence), rule coverage, and 41-item fitness-function catalog (no orphan).
- Security/AI/integration/operations Step 3 docs (threat model, tenant-isolation control matrix, data
  classification, secrets architecture, AI control plane/guardrail/observability, integration boundary +
  Google + DaengtisiaMS, environment/observability/backup baselines).
- New Claude rule `.claude/rules/20`; `AGENTS.md` chain (root + nested); minimal `app/`/`tests/` scaffold with
  explicit `FUTURE IMPLEMENTATION SCAFFOLD` markers.
- Codex foundation: `.codex/config.toml`, `.codex/rules/*.rules` (prefix_rule with positive/negative tests),
  `.codex/hooks.json` + hooks + tests, `.codex/README.md`; `.agents/skills/` (12 skills incl. project-fallback
  `limit-saver-1`); MCP manifest + governance.
- Step 3 validation gates (`check-step3-coverage.sh`, `check-adr.sh`, `scripts/codex/check-agents.sh`,
  `check-codex.sh`), 14 new query-smoke queries, `validate.sh` + CI wiring; version-matrix, decision-log, and
  status updates. Documentation/architecture baseline only — application implementation NOT STARTED.

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
