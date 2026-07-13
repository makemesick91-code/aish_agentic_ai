# Next Implementation Sprint — SPRINT-SF-00 (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. This sprint has **NOT** been
  executed; it is the recommended first sprint to run after the Step 4 GO tag. Estimates are planning signals,
  not commitments. Architecture (ADRs 0009–0032, the Application Architecture Baseline, the Application
  Foundation Rules) is cited by name/number in prose only.

---

## Recommendation

The recommended first implementation sprint after the Step 4 GO tag is **SPRINT-SF-00 — Runtime Bootstrap &
Local/CI Environment**. It is selected because it is the root of the fixed sequence in
[SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md](SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md): nothing else can be built or
validated with evidence until a runnable Laravel 12 skeleton, a reproducible local runtime, and green CI exist.

This document details SPRINT-SF-00. The full roadmap is in
[SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md); the epics are in
[SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md).

**Implementation of SPRINT-SF-0 has NOT STARTED.** This is a plan for when it does.

---

## Sprint identity

- **Sprint ID:** SPRINT-SF-00
- **Goal:** A runnable Laravel 12 skeleton with a reproducible local runtime and green CI, so every subsequent
  sprint can be validated with evidence.
- **Epics:** EPIC-SF-01 (Repository Runtime Bootstrap), EPIC-SF-02 (Local Development Environment),
  EPIC-SF-03 (CI Runtime Foundation).

---

## Entry criteria (Definition of Ready satisfied)

Before SPRINT-SF-00 starts, the foundation-level **Definition of Ready** in
[SAAS_FOUNDATION_DEFINITION_OF_READY.md](SAAS_FOUNDATION_DEFINITION_OF_READY.md) MUST be satisfied,
specifically:

- Step 4 GO tag verified exact-match on local, remote, and default branch.
- Origin resolves to `makemesick91-code/aish_agentic_ai` (NO-GO on mismatch).
- Dependency baseline (Laravel 12, PHP 8.3+, PostgreSQL, Redis) documented.
- Local dev strategy and CI strategy available.
- Secret matrix available; no secret committed.
- Environment matrix approved.
- Sprint backlog, acceptance criteria, and test strategy available.
- Traceability has no critical orphan.
- Working tree clean; branch strategy (Rule 13) confirmed.

---

## Scope

- Laravel 12 (PHP 8.3+) application skeleton that boots.
- Module directory shells for the 17 module boundaries (empty, no logic).
- Reproducible local runtime (container/local stack) with PostgreSQL and Redis reachable.
- Environment templates with **placeholder-only** values; secret manager referencing documented.
- CI pipeline: dependency install, static analysis, unit smoke tests, secret scanning, evidence capture; wired
  as required checks compatible with branch protection.

## Out-of-scope (explicit)

- Authentication, tenancy, RBAC, audit, queue/storage isolation — later sprints.
- Any business module logic (survey, feedback, recovery, Google Review, AI, analytics, billing).
- Deployment to any environment; production or pilot cutover.
- Any AI feature or external integration.

---

## Acceptance criteria

1. A clean checkout boots the app locally following documented steps.
2. `php artisan` command surface runs; app boots reproducibly.
3. The 17 module boundary shells exist per the modular-monolith layout (ADR 0009, ADR 0010).
4. CI runs on PR and main; required checks are visible and green on a real change.
5. Secret scanning runs in CI and passes; push protection confirmed enabled; no secret committed.
6. A deliberately failing test causes CI to fail red (proving the gate works).
7. Evidence for boot, CI, and secret scan is archived under `docs/evidence/`.

---

## Test and evidence plan

Per [SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md](SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md):

- **Functional:** boot smoke test; fresh-clone boot verification; lint.
- **Security:** CI secret scanning; confirm no committed secrets; debug disabled outside local.
- **Gate proof:** failing-case run showing CI fails red.
- **Evidence:** boot transcript, CI run logs, secret-scan output under `docs/evidence/validation/` and
  `docs/evidence/ci/`. Evidence is tenant-safe with no real PII and no secrets.

Tenant isolation is not yet implemented in this sprint (it begins at SPRINT-SF-02); SPRINT-SF-00 establishes
only the runnable base and the CI gate that later isolation tests will run in.

---

## CI gates

- Dependency install succeeds.
- Static analysis passes.
- Unit smoke tests pass.
- Secret scan passes.
- Evidence archived.

No gate may be skipped or weakened (Rule 09, Rule 13).

---

## Rollback

Revert the SPRINT-SF-00 branch; the repository returns to its documentation-only baseline. Low blast radius —
no runtime data or business feature exists to strand.

---

## GO / WATCH / NO-GO

- **GO:** App boots reproducibly and CI is green on a real change, all with archived evidence; no secret
  committed.
- **WATCH:** App boots but CI is flaky, or local boot needs undocumented manual steps — proceed only with a
  recorded remediation plan.
- **NO-GO:** A secret is committed, CI cannot run, push protection is disabled, or origin does not resolve to
  `makemesick91-code/aish_agentic_ai`.

---

## Master Source update rule

On completion, record the foundation runtime start with a `MASTER SOURCE UPDATE` block and the appropriate
semver bump (patch for status, minor if materially changing foundation state) per Rule 12.

---

## Status

Application implementation: **NOT STARTED.** SPRINT-SF-0 (SPRINT-SF-00) is the recommended next sprint and has
not been executed. This is a PLANNING BASELINE — NOT IMPLEMENTED.
