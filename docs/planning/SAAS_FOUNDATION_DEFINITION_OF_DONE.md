# SaaS Foundation Definition of Done (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. This document defines the
  completion contract for a future implementation; nothing here has been executed. Architecture (ADRs
  0009–0032, the Application Architecture Baseline, the Application Foundation Rules) is cited by name/number in
  prose only.

---

## Purpose

The **Definition of Done** states what MUST be true, with **evidence**, before any SaaS Foundation story,
epic, or sprint is called complete. It enforces evidence-based completion (Master Source §59; PRD §30) and the
truthful-status vocabulary. It complements
[SAAS_FOUNDATION_DEFINITION_OF_READY.md](SAAS_FOUNDATION_DEFINITION_OF_READY.md), which governs starting.

No item is "done" on assertion. "Done" requires the corresponding **evidence** artifact to exist and be
referenced. A missing evidence artifact means the item is not done.

---

## Story-level Definition of Done

A user story is done only when:

1. The acceptance criteria are met and demonstrated.
2. Functional tests for the story pass, with test output captured as **evidence**.
3. Security and tenant-isolation implications are tested where relevant (no cross-tenant leakage; RBAC
   enforced).
4. Audit records are produced for any sensitive/privileged action the story adds.
5. No secret is committed; secret scan passes.
6. Documentation for the story is updated.
7. The truthful status recorded matches the real state (no "done"/"deployed"/"verified" without evidence).

---

## Epic-level Definition of Done

An epic is done only when, in addition to all its stories being done:

1. **Acceptance criteria** for the epic (as listed in
   [SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md)) are all met with **evidence**.
2. **Multi-tenant isolation** is proven for every surface the epic touches (queries, cache, queue, storage,
   notifications, admin, logs), where applicable.
3. **Security tests** relevant to the epic pass: broken access control, cross-tenant, privilege escalation,
   and any epic-specific category (upload, OAuth state, rate limiting, etc.).
4. **Audit and observability** are in place for the epic's sensitive actions.
5. **Rollback** for the epic is defined and, where the epic is operational (backup, deployment), demonstrated.
6. **Evidence** is archived under `docs/evidence/` (validation, ci, or git-release as appropriate), tenant-safe
   and containing no real customer PII.
7. The epic's **Definition of Done** field in the catalog is satisfied.
8. Traceability maps the epic to its ADRs, AFRs, rules, tests, and evidence with no critical orphan.

---

## Sprint-level Definition of Done

A sprint is done only when:

1. All in-scope epics/stories are done per the above.
2. All sprint **CI gates** are green with archived **evidence**.
3. The sprint's **GO/WATCH/NO-GO** decision is recorded with evidence; a NO-GO blocks progression until fixed
   and retested.
4. The **Master Source update rule** for the sprint is honored: a material change produces a
   `MASTER SOURCE UPDATE` block with a semver bump (Rule 12).
5. Status documents (`docs/status/CURRENT_STATE.md`, `HANDOFF.md`, `SESSION_CHECKPOINTS.md`) are updated.
6. No unresolved critical/high issue remains open.

---

## Release-gate Definition of Done (before any product-release GO)

Before any product-release GO built on this foundation, the Rule 09 release gates MUST pass with **evidence**:

- **Functional** — features behave as specified with captured test output.
- **Security** — the security battery (broken access control, cross-tenant, privilege escalation, OAuth
  leakage, CSRF/XSS/SQLi, file upload, webhook forgery, rate-limit bypass, IDOR/SSRF) passes.
- **Data** — audit immutability, retention, export/deletion tested; no cross-tenant data leakage.
- **AI** — where AI is involved: no PII/medical leakage on the test suite, valid structured output, active
  human approval, cost limit, kill switch, idempotent retry. Manual works without AI.
- **Integration** — external actions verified before success is claimed; no mock claimed as real integration.
- **Operational** — health checks, backups with tested restore, incident/rollback runbook present.

---

## Hard "not done" conditions

An item is **NOT done** — regardless of other progress — if any of these is true:

- Any cross-tenant data exposure exists.
- Any privileged action is unaudited.
- Any secret is committed or exposed in logs.
- A success/deployed/verified status is claimed without runtime **evidence**.
- A retry produces a duplicate external side effect.
- An external action is reported as succeeded before provider verification.
- A required evidence artifact is missing.

---

## Truthful-status mapping

Completion status uses only the approved vocabulary (`PLANNED`, `IN PROGRESS`, `CODE COMPLETE`, `TESTED`,
`MERGED`, `DEPLOYED`, `RUNTIME VERIFIED`, `PILOT READY`, `PRODUCTION READY`, `BLOCKED`, `NO-GO`, `GO`). A
documentation GO tag attests documentation/planning readiness only, never application implementation,
deployment, pilot readiness, pilot runtime, or production readiness.

Application implementation: NOT STARTED. This Definition of Done is a PLANNING BASELINE — NOT IMPLEMENTED.
