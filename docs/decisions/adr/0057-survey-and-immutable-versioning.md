# ADR 0057 — Survey and Immutable Versioning Architecture

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 7 Survey & CSAT Foundation; survey capability IN PROGRESS toward GO, other business modules NOT STARTED
- **Owner:** Principal Architect / Survey Platform Architect
- **Rule:** `.claude/rules/32`, `.claude/rules/03`, `.claude/rules/30` · **Canonical:** Master Source §47, §62; PRD v1.3.0 §10.4, §10.6 (FR-SUR-*, FR-RES-*); rules 32, 03, 20, 27

## Context
Step 7 delivers the first customer-experience capability: tenants author surveys and collect responses. A survey's
content must be **stable for every response already collected** — changing a question after responses exist would
silently corrupt historical CSAT/NPS/CES. At the same time authors must be able to evolve a survey. The failure
modes to prevent are: mutating published content, responses that can no longer be resolved to the exact answered
content, cross-tenant survey access, hard-deleting a survey that has history, and placing survey logic where another
module could couple to it.

## Decision
- **Survey as identity, versions as content.** A `Survey` is a tenant-owned stable identity (`app/Models/Survey`);
  its editable/immutable content lives in `SurveyVersion` rows. `branch_id` null = tenant-wide; a branch-owned
  survey cannot be used by another branch.
- **Immutable-once-published versions.** A version is authored as `draft`, published exactly once (becoming
  immutable), and marked `superseded` when a newer version is published. Publishing (`SurveyVersionPublisher`) is
  transactional and **race-safe** — the survey row is locked so concurrent publishes serialize and cannot create two
  current versions — validates the version (≥1 question, unique order/key, choice ≥2 options, valid CSAT/NPS/CES
  scales), and is idempotent. Editing published content creates a **new draft version** copying questions/options;
  it never mutates the published one. Model-layer guards (`SurveyVersion`, `SurveyQuestion`, `SurveyOption`) block
  content mutation after publish as defense in depth.
- **Responses bind the exact version.** `SurveyResponse` carries `survey_version_id` (restrict-on-delete); answers
  always resolve against the exact answered version. A completed response is immutable except an authorized
  invalidation; answers are write-once.
- **Fail-closed tenancy.** Every survey table is tenant-owned via `BelongsToTenant`/`TenantOwned` with the
  fail-closed `TenantScope`; public route keys are opaque ULIDs, never sequential ids. A published survey cannot be
  hard-deleted.
- **Placement.** Survey code is platform-core in top-level `App\Surveys\*` + `App\Models\*` (consistent with ADR
  0052's SaaS-core placement), not `app/Modules/`; formal module extraction remains deferred (ADR 0020 criteria).

## Alternatives
- **Mutable surveys with a single content row** — rejected: any edit retroactively changes historical metrics.
- **Copy-on-write per response** — rejected: storage blow-up and no clean authoring model; a version pointer is
  sufficient and exact.
- **Soft-delete/hard-delete of published surveys** — rejected: destroys auditable history; archive instead.
- **A first `app/Modules/Survey` module now** — rejected for this step: introduces untested module infrastructure;
  top-level placement matches every prior step and is revisited under ADR 0020 when extraction is justified.

## Consequences
Historical responses always resolve to the exact content answered; authors evolve surveys via new draft versions
without touching published ones; concurrent publishes cannot corrupt the current-version pointer; and no survey data
crosses a tenant or branch boundary.

## Impacts
- **Security:** fail-closed tenant scope on all eight tables; opaque ULID public keys (no sequential ids); published
  content frozen at the model layer; no hard-delete of a published survey.
- **Privacy:** free text is untrusted and never AI-fed or logged in Step 7; responses carry only minimized metadata.
- **Tenant isolation:** every survey table is tenant-owned; branch scope enforced in policies; no cross-tenant survey
  reference.
- **Database:** eight tenant-owned tables (`surveys`, `survey_versions`, `survey_questions`, `survey_options`,
  `survey_campaigns`, `survey_invitations`, `survey_responses`, `survey_answers`) with unique version/question/option
  keys+orders and a deferred FK for the survey↔version pointer.
- **Operational:** truthful version (`draft/published/superseded`) and survey (`draft/published/paused/archived`)
  states; race-safe idempotent publish.
- **Cost:** negligible; no new providers.

## Verification / fitness function
`tests/Feature/Surveys/SurveyDomainSmokeTest.php`, `SurveyLifecycleTest.php`,
`tests/Feature/Sf07MigrationIntegrityTest.php`, `tests/Architecture/Sf07BoundariesTest.php`,
`tests/Feature/Security/Sf07CrossTenantMatrixTest.php` assert immutable published content, new-draft-from-published,
race-safe idempotent publish, one current version, tenant/branch isolation, and no hard-delete. AFR-171..AFR-175.

## Related
Requirement: Master Source §47, §62; PRD v1.3.0 §10.4, §10.6. Rules: 32, 03, 20, 27. ADRs: 0011, 0012, 0015, 0052.

## Evidence
`app/Models/Survey*.php`, `app/Surveys/SurveyService.php`, `app/Surveys/SurveyVersionPublisher.php`,
`database/migrations/2026_07_14_0700*`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-7/`.

## Non-claims
Does not implement feedback analysis, AI, recovery, or Google integration (all NOT STARTED), does not create an
`app/Modules/*` module, and does not claim deployment/pilot/production readiness.

## Rollback
Immutable-published-content, exact-version binding, fail-closed tenancy, and no-hard-delete are permanent; loosening
any requires an owner-approved Master Source update.
