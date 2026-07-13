# SaaS Foundation Implementation Plan (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102 (implementation-planning foundation rules), building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created by this plan. All estimates are
  planning estimates, not commitments. No sprint in this plan has been executed. Architecture references
  (ADRs 0009–0032, the Application Architecture Baseline, and the Application Foundation Rules) are cited by
  name and number in prose only.

---

## 1. Purpose

This document is the executable **planning baseline** for the Aish Agentic AI SaaS Foundation. It converts the
approved Step 3 architecture contract into an ordered, evidence-gated implementation **sequence** that a future
engineering effort can execute sprint by sprint. It is a plan — it is **NOT STARTED** as implementation and
creates no runnable code.

The plan exists so that when implementation begins, no fundamental architecture decision is reopened, tenant
isolation and human-approval guarantees are enforced from the first commit, and every claim of completion is
backed by runtime evidence rather than assertion.

This file is the top-level index for the Step 4 planning set. Sibling planning documents:

- [SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md)
- [SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md)
- [SAAS_FOUNDATION_DEPENDENCY_MAP.md](SAAS_FOUNDATION_DEPENDENCY_MAP.md)
- [SAAS_FOUNDATION_DEFINITION_OF_READY.md](SAAS_FOUNDATION_DEFINITION_OF_READY.md)
- [SAAS_FOUNDATION_DEFINITION_OF_DONE.md](SAAS_FOUNDATION_DEFINITION_OF_DONE.md)
- [SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md](SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md)
- [SAAS_FOUNDATION_RISK_REGISTER.md](SAAS_FOUNDATION_RISK_REGISTER.md)
- [SAAS_FOUNDATION_COST_MODEL.md](SAAS_FOUNDATION_COST_MODEL.md)
- [NEXT_IMPLEMENTATION_SPRINT.md](NEXT_IMPLEMENTATION_SPRINT.md)

---

## 2. Scope and boundary

**In scope (planning only):** the runtime, tenancy, security, and operational foundation on which every later
business capability (survey/CSAT, feedback inbox, recovery ticketing, Google Review, agentic AI orchestration,
analytics, billing) will be built. This foundation is the Master Source §62 "SaaS Foundation" stage and the
first block of the implementation order.

**Out of scope for the SaaS Foundation:** survey builder, CSAT/NPS/CES capture, feedback analysis, recovery
tickets, Google Review connection and reply, AI agent orchestration, analytics dashboards, billing charges,
and the pilot runtime. These depend on the foundation and are planned separately after it lands.

**Out of scope for this document entirely:** writing application code, running migrations, provisioning cloud
infrastructure, or making any deployment. Those actions remain **NOT STARTED**.

The core product stays generic. No pilot-tenant (Klinik Gigi Daengtisia) specifics are hard-coded into the
domain model; clinic mapping lives only at the integration/configuration boundary.

---

## 3. Reference architecture (cited, not restated)

This plan is subordinate to the Step 3 architecture contract. It does not restate it; it implements it in
order. Key references, cited by name and number only:

- **Architecture style:** Laravel 12 modular monolith (ADR 0009). Microservices are not the default; any
  module→service extraction requires ADR 0020 evidence.
- **Module ownership:** the 17 module boundaries own their data; cross-module work goes through a contract,
  application service, or domain event (ADR 0010).
- **Tenancy:** shared-DB / shared-schema / row-level ownership with tenant and branch context propagated to
  every surface including jobs and events (ADR 0011, 0012, 0015).
- **External side effects:** transactional outbox, idempotency, bounded retry, dead-letter, no success before
  provider verification (ADR 0016, 0017).
- **AI control plane:** provider abstraction with structured output, redaction, guardrails, human approval,
  prompt/model versioning, cost/trace, manual fallback, and kill switch (ADR 0019, 0023, 0028). MED-classified
  data must not reach AI or public output.
- **Foundation rules:** the Application Foundation Rules catalog AFR-001..072, extended for implementation
  planning by AFR-099..102, remains the canonical mapped rule set with no orphan.
- **Canonical documents referenced in prose:** the Application Architecture Baseline, the Application
  Foundation Rules, the Tenant Isolation Control Matrix, the Event Catalog, the Domain Map, and the Human
  Approval Matrix.

---

## 4. Guiding principles for the implementation sequence

The order below is fixed. Each principle is a hard ordering constraint, not a preference.

1. **Tenant context before business features.** No table that carries tenant data is written before the
   tenant/branch context primitive exists and is enforced.
2. **RBAC before privileged console.** No platform-admin or privileged surface is exposed before roles,
   permissions, and branch scoping are enforced.
3. **Audit before sensitive mutation.** No sensitive or privileged mutation path ships before the audit and
   security-event trail can record it immutably.
4. **Queue isolation before external async.** No queued or asynchronous work runs before tenant context is
   guaranteed to propagate into jobs and events.
5. **Storage isolation before tenant upload.** No tenant file upload path exists before tenant-scoped storage
   prefixes and access controls exist.
6. **Observability and backup before pilot.** No pilot runtime is attempted before health checks, structured
   logging/tracing, and a tested backup/restore exist.
7. **Runtime evidence before deployment claims.** No "deployed", "verified", or "GO" status is claimed before
   the corresponding runtime evidence is captured.
8. **Manual works without AI.** Every foundational workflow remains usable when the AI provider or an external
   provider is unavailable; basic functions never depend on AI.

---

## 5. The fixed implementation sequence

The SaaS Foundation is delivered in this exact **sequence**. Each stage maps to one or more epics defined in
[SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md) and to one or more sprints in
[SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md).

| # | Stage | Epics | Ordering rationale |
|---|-------|-------|--------------------|
| 1 | Repository runtime bootstrap | EPIC-SF-01 | Nothing runs until the Laravel 12 skeleton, autoloading, and app boot exist. |
| 2 | Local development & CI environment | EPIC-SF-02, EPIC-SF-03 | Reproducible local + CI runtime must exist before any code can be validated with evidence. |
| 3 | Configuration & secret foundation | (config foundation, feeds EPIC-SF-04) | Environment/config and secret referencing must exist before any credential-bearing feature. |
| 4 | Authentication & account security | EPIC-SF-04 | Identity precedes tenancy; users authenticate before tenant context is bound. |
| 5 | Tenant & branch context | EPIC-SF-05 | Tenant context before any business feature (principle 1). |
| 6 | RBAC & authorization | EPIC-SF-06 | RBAC before any privileged console (principle 2). |
| 7 | Audit & security events | EPIC-SF-07 | Audit before sensitive mutation (principle 3). |
| 8 | Queue / cache / storage isolation | EPIC-SF-08, EPIC-SF-09 | Queue isolation before external async; storage isolation before tenant upload (principles 4, 5). |
| 9 | Notification foundation | EPIC-SF-10 | Depends on queue + tenant context; precedes any tenant-facing messaging. |
| 10 | Subscription & entitlement skeleton | EPIC-SF-11 | Entitlement gating skeleton before plan-aware features; no billing charges yet. |
| 11 | Platform admin skeleton | EPIC-SF-12 | Privileged console after RBAC + audit exist. |
| 12 | Observability & health checks | EPIC-SF-13 | Observability before pilot (principle 6). |
| 13 | Backup & restore foundation | EPIC-SF-14 | Tested restore before pilot (principle 6). |
| 14 | Deployment & rollback foundation | EPIC-SF-15 | Deployment path with rollback before any deployment claim (principle 7). |
| 15 | Security & architecture verification | EPIC-SF-16 | Final foundation gate: isolation, security, and fitness functions verified with evidence. |

The sequence is dependency-driven and is elaborated in
[SAAS_FOUNDATION_DEPENDENCY_MAP.md](SAAS_FOUNDATION_DEPENDENCY_MAP.md). Stages may not be reordered without a
Master Source update and an updated dependency map.

---

## 6. Mapping sequence to sprints

The sequence is executed by nine sprints (SPRINT-SF-00 through SPRINT-SF-08). The full sprint definitions are
in [SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md). The recommended first sprint after
the Step 4 GO tag is **SPRINT-SF-00 (Runtime Bootstrap & Local/CI Environment)**, detailed in
[NEXT_IMPLEMENTATION_SPRINT.md](NEXT_IMPLEMENTATION_SPRINT.md).

| Sprint | Sequence stages covered | Epics |
|--------|-------------------------|-------|
| SPRINT-SF-00 | 1, 2 | EPIC-SF-01, EPIC-SF-02, EPIC-SF-03 |
| SPRINT-SF-01 | 3, 4 | config foundation, EPIC-SF-04 |
| SPRINT-SF-02 | 5 | EPIC-SF-05 |
| SPRINT-SF-03 | 6, 7 | EPIC-SF-06, EPIC-SF-07 |
| SPRINT-SF-04 | 8 | EPIC-SF-08, EPIC-SF-09 |
| SPRINT-SF-05 | 9, 10, 11 | EPIC-SF-10, EPIC-SF-11, EPIC-SF-12 |
| SPRINT-SF-06 | 12, 13 | EPIC-SF-13, EPIC-SF-14 |
| SPRINT-SF-07 | 14 | EPIC-SF-15 |
| SPRINT-SF-08 | 15 | EPIC-SF-16 |

---

## 7. Gate model

No stage is considered complete on assertion. Each stage passes through the shared gates:

- **Definition of Ready** must be satisfied before a sprint starts — see
  [SAAS_FOUNDATION_DEFINITION_OF_READY.md](SAAS_FOUNDATION_DEFINITION_OF_READY.md).
- **Definition of Done** must be satisfied, with evidence, before a stage is called complete — see
  [SAAS_FOUNDATION_DEFINITION_OF_DONE.md](SAAS_FOUNDATION_DEFINITION_OF_DONE.md).
- **Test and evidence** requirements per epic are in
  [SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md](SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md), including the
  multi-tenant isolation, security, AI-evaluation, and performance categories.
- **Release gates** (functional, security, data, AI, integration, operational) apply per Rule 09 before any
  product-release GO.

The documentation GO tag for Step 4 attests planning readiness only. It is not a claim that the application is
implemented, deployed, pilot-ready, or production-ready — all of which remain **NOT STARTED**.

---

## 8. Risk and cost

Planning-level risks (with likelihood, impact, mitigation, and owner) are tracked in
[SAAS_FOUNDATION_RISK_REGISTER.md](SAAS_FOUNDATION_RISK_REGISTER.md). Planning-level cost categories are in
[SAAS_FOUNDATION_COST_MODEL.md](SAAS_FOUNDATION_COST_MODEL.md); no price in that model is a committed cost.

---

## 9. Ownership roles

The plan assigns work to roles, not named individuals, so ownership is stable across staffing:

- **Technical Program Manager (TPM)** — owns the sequence, sprint cadence, and GO/WATCH/NO-GO decisions.
- **Laravel SaaS Architect** — owns runtime, tenancy, queue/storage, and fitness functions.
- **Security-Privacy Owner** — owns auth, RBAC, audit, isolation, and the security battery.
- **Release/CI Owner** — owns CI runtime, deployment/rollback, and release evidence.
- **Operations Owner** — owns observability, backup/restore, and incident/rollback runbooks.
- **QA-Traceability Owner** — owns test coverage mapping and no-critical-orphan verification.
- **Product** — owns scope boundaries and entitlement/plan decisions.

Multiple roles may be held by one person during early sprints, provided a combination never removes meaningful
approval on a high-risk action (mirroring the Rule 16 role-combination constraint).

## 10. Assumptions and non-claims

- All estimate classes (S/M/L/XL) are planning signals; no calendar commitment is implied.
- No infrastructure is provisioned and no cost is committed (see
  [SAAS_FOUNDATION_COST_MODEL.md](SAAS_FOUNDATION_COST_MODEL.md)).
- The plan assumes the Step 4 GO tag is verified and origin resolves to `makemesick91-code/aish_agentic_ai`
  before any implementation commit.
- Nothing in this plan constitutes application code, migration, or deployment; the status remains
  **NOT STARTED** until an executed, evidence-backed sprint changes it.

## 11. Change control

Any change to the fixed sequence, epic set, or sprint mapping is a material change requiring a Master Source
impact analysis and, if material, a `MASTER SOURCE UPDATE` block with a semver bump (Rule 12). Superseded
planning decisions are marked superseded, never deleted.

**Current truthful state:** Step 4 SaaS Foundation implementation planning — this PLANNING BASELINE — NOT
IMPLEMENTED. Application implementation, deployment, pilot readiness, pilot runtime, and production readiness:
**NOT STARTED**.
