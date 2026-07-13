# SaaS Foundation Definition of Ready (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. This document defines readiness
  preconditions for a future implementation; it does not start it. Architecture (ADRs 0009–0032, the
  Application Architecture Baseline, the Application Foundation Rules) is cited by name/number in prose only.

---

## Purpose

The **Definition of Ready** states the preconditions that MUST all be satisfied before the SaaS Foundation
implementation — or any individual foundation sprint — may begin. It prevents starting implementation on an
unstable or under-specified base. It complements
[SAAS_FOUNDATION_DEFINITION_OF_DONE.md](SAAS_FOUNDATION_DEFINITION_OF_DONE.md), which governs completion.

Readiness is evidence-based. A checklist item is "ready" only when the referenced source exists and is
approved, not when it is merely intended.

---

## Foundation-level Definition of Ready

The SaaS Foundation cannot start until **all** of the following are true:

1. **Domain strategy documented** — the product/domain naming and canonical-identity strategy is documented and
   approved.
2. **Brand baseline documented** — product identity/brand baseline (Aish Agentic AI) is documented and stable.
3. **Environment matrix approved** — the local/CI/staging/production environment matrix and target classes are
   approved.
4. **Dependency baseline available** — the framework and package dependency baseline (Laravel 12, PHP 8.3+,
   PostgreSQL, Redis, S3-compatible storage) is documented.
5. **Local dev strategy available** — a reproducible local development strategy exists (EPIC-SF-02 scope).
6. **CI strategy available** — a CI runtime strategy with required checks and evidence capture exists
   (EPIC-SF-03 scope).
7. **Secret matrix available** — a secret/credential matrix (what secrets exist, where they are referenced,
   how they rotate) is available; no secret is committed.
8. **Tenant-isolation implementation sequence available** — the order in which tenant isolation is enforced on
   each surface is documented and traces to the Tenant Isolation Control Matrix.
9. **Sprint backlog available** — the sprint roadmap (SPRINT-SF-00..08) and its epic mapping are available.
10. **Acceptance criteria available** — each epic has defined acceptance criteria in the epic catalog.
11. **Test strategy available** — the test and evidence plan (functional, multi-tenant isolation, AI
    evaluation, security, performance) is available.
12. **Deployment-target class available** — the deployment target class and rollback plan are documented.
13. **Rollback plan available** — a rollback plan exists for each epic and for deployment.
14. **Required rules available** — the governing Claude rules (including Rule 26 and Rules 03, 04, 05, 07, 08,
    09, 11, 12, 13, 20) are present and current.
15. **Traceability has no critical orphan** — the requirement traceability shows no critical unmapped
    requirement.
16. **Step 4 GO tag verified** — the Step 4 documentation GO tag is created and verified exact-match on local,
    remote, and default branch, and origin resolves to `makemesick91-code/aish_agentic_ai`.

If any item is unmet, the foundation is **not ready** and implementation MUST NOT start.

---

## Sprint-level Definition of Ready

Before any single sprint (SPRINT-SF-00..08) starts, additionally:

- The prior sprint (if any) has reached **GO** (or an owner-approved **WATCH** with a recorded remediation
  plan) with evidence.
- The sprint's entry criteria in [SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md) are
  satisfied.
- The sprint's epics have their dependencies satisfied per
  [SAAS_FOUNDATION_DEPENDENCY_MAP.md](SAAS_FOUNDATION_DEPENDENCY_MAP.md).
- The working tree is clean and the branch strategy (Rule 13) is confirmed.
- No unresolved critical/high issue from a prior sprint blocks the new scope.

---

## Story-level Definition of Ready

Before a user story enters a sprint:

- It has a clear objective, scope, and explicit out-of-scope.
- It has testable acceptance criteria.
- Its security, privacy, and tenant-isolation implications are identified.
- Its evidence requirement is identified per
  [SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md](SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md).
- It is small enough to complete and evidence within the sprint.

---

## Readiness verification

Readiness is verified by inspecting the referenced artifacts, not by assertion. The verification is recorded
before a sprint's first implementation commit. A failed readiness check is a **NO-GO for start** until the gap
is closed.

Application implementation: NOT STARTED. This Definition of Ready is a PLANNING BASELINE — NOT IMPLEMENTED.
