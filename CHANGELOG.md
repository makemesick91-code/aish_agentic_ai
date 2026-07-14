# Changelog — Aish Agentic AI (repository)

Repository-level changelog. The canonical **product** changelog lives inside the living Master Source
(`docs/canonical/MASTER_SOURCE.md`, sections 6 and 66) and is governed by `.claude/rules/12`.
This file records repository/documentation-foundation engineering changes.

Format follows [Keep a Changelog](https://keepachangelog.com/) principles; dates use `Asia/Makassar`.

## [2026-07-15] — Step 8 (Master Source v2.10.0): Feedback Operations Foundation — MERGED & GO TAGGED

Target release: annotated tag `aish-agentic-ai-step-8-feedback-operations-foundation-v1.0.0-go`.
Base branch `main`, feature branch `feature/step-8-feedback-operations-foundation`. Second customer-experience
capability — an operable Feedback Inbox on the SaaS core + SF-05 + Step 7 substrate.

### Added
- Master Source **v2.10.0** (§74 Step 8); PRD unchanged at **v1.3.0**. ADRs **0060–0062**, **AFR-188..210**,
  **Claude rule 33**.
- Feedback projection: an after-commit `SurveyResponseCompleted` domain event + a queued idempotent listener/job that
  create one `FeedbackItem` per source via a DB unique `(tenant_id, source_type, source_id)` constraint (replay/retry
  safe), plus an idempotent `aish:feedback-reconcile` back-fill command.
- Explicit feedback lifecycle (`new → triaged → assigned → in_progress → resolved → closed → archived`) via a guarded
  transition service; `resolved`/`closed` are operational feedback states, not a recovery outcome.
- Scope-validated assignment (active membership + branch scope; membership-revocation fail-close) with append-only
  assignment history; tenant-isolated manual tags; append-only internal notes; and an append-only, sanitized,
  immutable feedback timeline.
- Private tenant-prefixed feedback attachments with content-based MIME allowlist validation, path-traversal
  prevention, no public disk, and a recorded remove-state.
- Permission-aware search: native PostgreSQL FTS (`tsvector`/GIN) with a portable `LIKE` fallback; content search
  gated by the `feedback.view-content` permission; metadata search for any list-viewer.
- Bounded bulk operations (hard item cap, per-action re-authorization, tenant/branch scope, timelined) and a queued,
  entitlement-gated, metered secure CSV export written to a private+expiring location with requester-scoped
  re-authorized download and CSV formula-injection neutralization.
- Feedback entitlement gate (`EnsureFeedbackEnabled`) over the single authoritative resolver, idempotent tenant-scoped
  usage metering, internal feedback notifications via the SF-05 dispatcher, and sanitized append-only audit; Google
  Review anti-gating preserved (a feedback state/score never gates review access).
- Independent **Step 8 security review** completed — PASS after fixes (F-1 HIGH export-download re-authorization,
  F-2/F-3 LOW hardening fixed; 14/14 other vectors PASS); evidence
  `docs/evidence/step-8-independent-security-review.md`.
- Tests: feedback projection, lifecycle, assignment, tag/note, timeline, attachment, search, bulk, export,
  notification, cross-tenant security matrix (`Sf08CrossTenantMatrixTest`), architecture boundaries
  (`Sf08BoundariesTest`), PostgreSQL migration integrity (`Sf08MigrationIntegrityTest`), audit (`Sf08AuditTest`), and
  console suites — full hermetic suite **352 passing**; `aish:verify-step-8` 18 checks pass.

### Status
CODE COMPLETE and TESTED locally (Pint + PHPStan clean; `aish:verify-step-8` 18 checks on SQLite; independent
security review PASS after fixes); **NOT merged, NOT tagged, NOT CI-green-on-CI, NOT clean-checkout-verified against
real PostgreSQL 17 + Redis 7** at authoring time. The GO tag will attest feedback-operations foundation readiness
only — not AI/recovery/Google, not deployment, pilot, or production.

## [2026-07-14] — Step 7 (Master Source v2.9.0): Survey & CSAT Foundation — MERGED & GO TAGGED

Released as annotated tag `aish-agentic-ai-step-7-survey-csat-foundation-v1.0.0-go` (merge `1b1ba86`).
Base branch `main`. First customer-experience capability on the SaaS core + SF-05 substrate.

### Added
- Master Source **v2.9.0** (§73 Step 7); PRD unchanged at **v1.3.0**. ADRs **0057–0059**, **AFR-171..187**, **Claude rule 32**.
- Survey domain: eight tenant-owned tables (survey, version, question, option, campaign, invitation, response,
  answer) with fail-closed tenant scope, opaque ULID public ids, hashed one-time invitation tokens, tenant-leading
  idempotency, and a partial unique index enforcing one completed response per invitation.
- Immutable versioning: race-safe idempotent publisher, published-content model guards, new-draft-from-published,
  exact-version response binding, write-once answers, completed-response immutability, no hard-delete of published.
- Deterministic CSAT/NPS/CES calculator (single service, versioned config, explicit 2-decimal rounding, null on
  empty) + tenant/branch/version-scoped summaries.
- Services: survey/campaign/invitation lifecycle, response validator, public survey gateway (opaque resolution,
  no-enumeration, membership-less context, one-time submission, usage + audit), invitation mailer + internal
  completion notification via the SF-05 dispatcher.
- HTTP: survey permissions + role assignments; survey/campaign/invitation/response policies with branch scoping;
  tenant-aware FormRequests; tenant builder controllers + Blade (create/questions/options/publish/pause/resume/
  archive/new-version/preview/results/campaigns/invitations); public survey plane + QR endpoint (bacon/qr-code);
  per-token+IP rate limiters; payload caps; entitlement/usage enforcement via one guard over the authoritative
  resolver.
- Independent **SF-05 security review** completed — PASS (no critical/high/medium); evidence
  `docs/evidence/sf-05-independent-security-review.md`.
- Tests: domain, scoring (16 boundary), lifecycle, public flow, summary, notification, HTTP authz, cross-tenant
  security matrix, Step-7 architecture boundaries, PostgreSQL migration integrity, and audit suites — full suite
  green against real PostgreSQL 17 + Redis 7.
- `bacon/bacon-qr-code` promoted to a direct dependency (already resolved via Fortify) for URL-only QR SVG.

### Status
CODE COMPLETE and TESTED locally; **NOT merged, NOT tagged, NOT CI-green-on-CI, NOT clean-checkout-verified** at
authoring time. The GO tag attests survey & CSAT foundation readiness only — not deployment, pilot, or production.

## [Unreleased] — SPRINT-SF-05 (Master Source v2.8.0): Notification, Subscription, and Platform Admin Skeletons

Target release: annotated tag `aish-agentic-ai-sprint-sf-05-notification-subscription-platform-admin-skeletons-v1.0.0-go`.
Base branch `main`. Adds three platform-core foundations (notification, subscription/entitlement, platform-admin).

### Added
- Master Source **v2.8.0** (§72 SPRINT-SF-05); PRD unchanged at **v1.3.0**. ADRs **0054–0056**, **AFR-155..170**, **Claude rule 31**.
- Notification foundation: single tenant-safe dispatcher, per-(recipient,channel) globally-unique dedup, truthful
  delivery state machine (pending→queued→sending→sent/failed/cancelled/suppressed), bounded idempotent retry,
  in-app + email channels only, timezone-aware preferences/quiet-hours, critical-notification bypass, ownership-checked
  in-app inbox, queued `FoundationNotificationMail`.
- Subscription & entitlement skeleton: plan `(code,version)` catalog, typed allowlisted entitlements, guarded
  subscription state machine, single authoritative fail-closed `EntitlementResolver`, idempotent tenant-scoped usage
  metering, idempotent `aish:subscription-reconcile`. No payment/invoicing — commercial state ≠ payment.
- Platform-admin plane (`/platform-admin/*`): platform roles distinct from tenant roles (no `Gate::before` bypass),
  per-permission authorization, secure `aish:platform-admin-provision`, reason-required audited tenant
  suspend/reactivate/deletion-pending, append-only support notes, truthful metrics; impersonation prohibited.
- New audit events; DB constraints (notification dedup, usage idempotency, one subscription per tenant, plan
  `(code,version)`, one platform role per user); architecture fitness tests (`tests/Architecture/Sf05BoundariesTest.php`);
  and `scripts/runtime/verify-sf-05.sh` + `aish:verify-sf-05` real-infra verification wired into `backend-runtime-ci`.

### Changed
- `.claude/rules/` extended to rule 31; `CLAUDE.md`, `AGENTS.md`, coverage matrix, and `VERSION_MATRIX` bumped to
  reflect v2.8.0 / AFR-170 / ADR-0056.

### Status
- **CODE COMPLETE**, **TESTED locally** (182 tests; Pint/PHPStan clean; composer/npm audits clean; verified against
  real PostgreSQL 17 + Redis 7). **IN PROGRESS toward GO** — NOT yet merged on CI, NOT tagged, NOT
  clean-checkout-verified on the merged SHA. Business/module implementation, deployment, pilot, production: **NOT STARTED**.

## [Unreleased] — Step 6: SaaS Core Foundation

Target release: annotated tag `aish-agentic-ai-step-6-saas-core-foundation-v1.0.0-go`.
Base branch `main`. Consolidates canonical **SPRINT-SF-01..SF-04** (**EPIC-SF-04..09**) into one release.

### Added
- Master Source **v2.7.0** (§71 Step 6); PRD unchanged at **v1.3.0**. ADRs **0051–0053**, **Claude rule 30**.
- Secure authentication (Fortify; self-service registration disabled; Sanctum installed; email verification;
  login throttling; suspended-user rejection without account enumeration) and a global user identity separated
  from per-tenant membership.
- Tenant and branch lifecycle; explicit tenant memberships (invited / active / suspended / revoked) with
  last-active-owner protection and one-time hashed invitation tokens.
- Immutable, fail-closed request/job tenant context (absence of a valid context denies access; context
  propagates to queued jobs).
- Tenant-scoped RBAC (Spatie Permission teams keyed on `tenant_id`) with policies; append-only, non-deletable
  audit trail.
- Tenant isolation across DB (row-level `tenant_id`), cache (namespaced keys), queue (context envelope),
  storage (path prefix + traversal-safe), and logging.
- `docs/canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.7.0.md` snapshot + SHA256SUMS entry.

### Changed
- CLAUDE.md / AGENTS.md / Master Source active version bumped to **v2.7.0**; VERSION_MATRIX updated; Claude rules
  index gains rule 30; version-consistency check aligned to v2.7.0.

### Status
SaaS core foundation **CODE COMPLETE** and **TESTED locally**; **IN PROGRESS toward GO** — NOT merged, NOT
tagged, NOT CI-green-on-CI, and NOT clean-checkout-verified. Business modules, deployment, pilot, and production
**NOT STARTED**; no domain owned, nothing deployed. Merge / CI / tag evidence forthcoming under
`docs/evidence/step-6/`.

## [Unreleased] — Step 5: Runtime & Repository Bootstrap

Target release: annotated tag `aish-agentic-ai-step-5-runtime-repository-bootstrap-v1.0.0-go`.
Base branch `main`; feature branch `feature/step-5-runtime-repository-bootstrap`.

### Added
- Master Source **v2.6.0** (§70 Step 5); PRD unchanged at **v1.3.0**. ADRs **0047–0050**, **AFR-127..133**,
  **Claude rule 29**.
- Bootable **Laravel 12** application (PHP 8.4 / PostgreSQL 17 / Redis 7 / Node 22): modular skeleton,
  `bootstrap/app.php` routing + middleware, `app/Support` health/runtime foundation, `app/Console/Commands`
  (`aish:preflight`, `aish:heartbeat`, `aish:queue-smoke`).
- Truthful health probes `GET /live` and `GET /ready` (503 on dependency failure, no leakage); `SecurityHeaders`
  middleware; `config/health.php`, `config/security.php`.
- `.env.example` environment contract; `docker-compose.yml` (Postgres 17 + Redis 7); `Makefile` and
  `scripts/runtime/{preflight,bootstrap-local,verify-runtime}.sh`.
- Test suite (Architecture/Unit/Feature/Security, 24 tests); Pint + PHPStan/Larastan (level 6); predis + larastan
  dev deps; frontend (Vite 7 + Tailwind 4) bootstrap surface.
- Real `backend-runtime-ci` job in `pr-ci.yml` (Postgres + Redis service containers), wired into
  `pr-ci / Required Gate`; classifier + gate-decision + tests updated.
- Runtime docs: `docs/architecture/runtime-bootstrap.md`, `docs/getting-started/local-development.md`,
  `docs/operations/runtime-verification.md`, `docs/governance/foundation-coverage-matrix.md`,
  `docs/evidence/step-5-runtime-repository-bootstrap.md`.

### Changed
- `scripts/hooks/guard-dangerous-commands.sh`: implementation-phase supply-chain safeguard — package **install**
  permitted (supersedes Step-4 planning no-install, AFR-096); publish/deploy/DNS still blocked. Guard test updated.
- CLAUDE.md / AGENTS.md / Master Source bumped to v2.6.0; scaffolding AGENTS/README updated to Step 5 (runtime
  implemented, business modules NOT STARTED).

### Status
Runtime foundation CODE COMPLETE + RUNTIME VERIFIED locally (real PostgreSQL 17 + Redis 7). Deployment, pilot,
production, and business modules **NOT STARTED**.

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
