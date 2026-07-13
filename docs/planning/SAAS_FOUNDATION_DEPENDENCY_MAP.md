# SaaS Foundation Dependency Map (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. Dependencies describe a planned
  order, not executed work. Architecture (ADRs 0009–0032, the Application Architecture Baseline, the
  Application Foundation Rules) is cited by name/number in prose only.

---

## Purpose

This map records the hard ordering dependencies between the sixteen SaaS Foundation epics so the sequence in
[SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md](SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md) cannot be reordered by
accident. Epics are defined in [SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md); sprint
grouping is in [SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md).

A dependency here means: the dependent epic MUST NOT begin implementation until the prerequisite epic has
reached its Definition of Done with evidence.

---

## Dependency table

| Epic | Depends on | Reason |
|------|-----------|--------|
| EPIC-SF-01 Repository Runtime Bootstrap | — | Root of the sequence. |
| EPIC-SF-02 Local Development Environment | EPIC-SF-01 | Needs the app skeleton to boot locally. |
| EPIC-SF-03 CI Runtime Foundation | EPIC-SF-01, EPIC-SF-02 | CI runs the local runtime's build/test. |
| EPIC-SF-04 Authentication & Account Security | EPIC-SF-01, EPIC-SF-02, EPIC-SF-03, config/secret foundation | Needs runtime, CI evidence, and secure config before credentials. |
| EPIC-SF-05 Tenant & Branch Foundation | EPIC-SF-04 | Identity precedes tenant binding (principle 1). |
| EPIC-SF-06 Roles, Permissions & Scope | EPIC-SF-05 | Roles are tenant-scoped; RBAC before privileged surfaces (principle 2). |
| EPIC-SF-07 Audit & Security Events | EPIC-SF-05, EPIC-SF-06 | Audit is tenant-scoped and gated by RBAC; audit before sensitive mutation (principle 3). |
| EPIC-SF-08 Queue, Redis & Scheduler | EPIC-SF-05, EPIC-SF-07 | Jobs must carry tenant context and be auditable (principle 4). |
| EPIC-SF-09 Tenant-Scoped Storage | EPIC-SF-05, EPIC-SF-06 | Storage isolation needs tenant context + RBAC before uploads (principle 5). |
| EPIC-SF-10 Notification Foundation | EPIC-SF-08, EPIC-SF-05 | Notifications dispatch via the tenant-aware queue. |
| EPIC-SF-11 Subscription & Entitlement Skeleton | EPIC-SF-05, EPIC-SF-06, EPIC-SF-07 | Entitlements are tenant-scoped, RBAC-guarded, and audited. |
| EPIC-SF-12 Platform Admin Skeleton | EPIC-SF-06, EPIC-SF-07 | Privileged console needs RBAC + audit first. |
| EPIC-SF-13 Observability & Health Checks | EPIC-SF-08, EPIC-SF-10 | Observes queue + delivery health. |
| EPIC-SF-14 Backup & Restore Foundation | EPIC-SF-05, EPIC-SF-09, EPIC-SF-13 | Backs up tenant data + storage; restore verified with health checks. |
| EPIC-SF-15 Deployment & Rollback Foundation | EPIC-SF-03, EPIC-SF-13, EPIC-SF-14 | Deploy path needs CI, health gating, and a tested restore. |
| EPIC-SF-16 Security & Architecture Verification | EPIC-SF-01..15 | Verifies the entire foundation last. |

---

## Critical path

The critical path runs through the tenancy and security core:

EPIC-SF-01 → EPIC-SF-02 → EPIC-SF-03 → EPIC-SF-04 → EPIC-SF-05 → EPIC-SF-06 → EPIC-SF-07 → EPIC-SF-08 →
EPIC-SF-13 → EPIC-SF-14 → EPIC-SF-15 → EPIC-SF-16.

EPIC-SF-09 (storage) parallels EPIC-SF-08 once EPIC-SF-06 lands. EPIC-SF-10, EPIC-SF-11, and EPIC-SF-12 form a
parallelizable cluster once their prerequisites (queue/audit/RBAC) are met but are grouped into SPRINT-SF-05
for coherence.

---

## Parallelization opportunities (planning only)

- Within SPRINT-SF-00, EPIC-SF-02 and early EPIC-SF-03 scaffolding can proceed in parallel after EPIC-SF-01.
- Within SPRINT-SF-04, EPIC-SF-08 and EPIC-SF-09 can proceed in parallel after their shared prerequisites.
- Within SPRINT-SF-05, EPIC-SF-10, EPIC-SF-11, and EPIC-SF-12 can proceed in parallel; each still gates on its
  own Definition of Done.
- Within SPRINT-SF-06, EPIC-SF-13 precedes the restore verification portion of EPIC-SF-14.

Parallelization is a planning suggestion; it never relaxes an ordering dependency above.

---

## Cross-cutting dependencies

These concerns cut across all epics and are satisfied incrementally, not as a single epic:

- **Tenant isolation** — introduced by EPIC-SF-05 and re-verified on every surface through EPIC-SF-16; every
  epic that touches data depends on the tenant context primitive.
- **Audit** — introduced by EPIC-SF-07; every sensitive-mutation epic depends on it.
- **Secret handling** — the configuration/secret foundation (before EPIC-SF-04) is a prerequisite for any
  credential-bearing epic (EPIC-SF-04, EPIC-SF-09, EPIC-SF-14, EPIC-SF-15).
- **Manual-without-AI** — every workflow epic must remain usable when AI is unavailable; this is a standing
  constraint, not a dependency edge.

---

## Reordering control

Changing any dependency edge is a material architecture-adjacent change requiring a Master Source impact
analysis and, if material, a `MASTER SOURCE UPDATE` block with a semver bump (Rule 12). Superseded dependency
decisions are marked superseded, never deleted.

Application implementation: NOT STARTED. This map is a PLANNING BASELINE — NOT IMPLEMENTED.
