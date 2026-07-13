# ADR 0039 — SaaS Foundation Implementation Sequence

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **application implementation NOT STARTED**
- **Owner:** Technical Program Manager / Laravel SaaS Architect
- **Rule:** `.claude/rules/26` (AFR-099, AFR-100, AFR-101) · **Canonical:** Master Source v2.4.0 §62; §68; PRD v1.3.0

## Context
The SaaS Foundation must be built in an order that minimizes rework and never exposes tenant data, so the epics
and sprints need a fixed dependency sequence before implementation begins.

## Decision
Implementation sequence: runtime bootstrap → local/CI environment → configuration & secret foundation →
authentication → tenant & branch context → RBAC & authorization → audit → queue/cache/storage isolation →
notification foundation → subscription skeleton → platform admin skeleton → observability → backup/restore →
deployment & rollback → security & architecture verification. Ordering principles: tenant context before business
features; RBAC before privileged console; audit before sensitive mutation; queue isolation before external async;
storage isolation before tenant upload; observability + tested restore before pilot; runtime evidence before any
deployment claim; manual operation must work without AI. Sixteen epics (EPIC-SF-01..16) map to nine sprints
(SPRINT-SF-00..08). See [SaaS Foundation Implementation Plan](../../planning/SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md),
[Epic Catalog](../../planning/SAAS_FOUNDATION_EPIC_CATALOG.md), and [Sprint Roadmap](../../planning/SAAS_FOUNDATION_SPRINT_ROADMAP.md).

## Alternatives
- **Feature-first (business modules before tenancy/RBAC)** — rejected: would require rework and risk isolation gaps.
- **One monolithic foundation sprint** — rejected: unreviewable, no incremental GO/WATCH/NO-GO.
- **Autonomous agents before stable workflow** — rejected: manual-before-automation (Master Source §62).

## Consequences
Each sprint delivers a verifiable increment with its own release gate; the first sprint after Step 4 is SPRINT-SF-00.

## Impacts
- **Security:** authentication, RBAC, audit, and isolation land before any privileged or external surface.
- **Privacy:** tenant/branch context and data classification precede business data handling.
- **Tenant isolation:** tenant context (EPIC-SF-05) precedes all business features by design.
- **Database:** migrations are expand/contract and module-owned per ADR 0014; introduced in sequence.
- **Operational:** observability and backup/restore precede pilot; deployment/rollback before any live claim.
- **Cost:** phased build spreads cost; each sprint sized independently — planning cost categories only.

## Verification / fitness function
`check-step4-coverage.sh` asserts the sequence, EPIC-SF-01..16 presence, sprint roadmap with GO/WATCH/NO-GO, and
the "NOT STARTED" status (V4-SF-01). No code is produced in Step 4.

## Related
Requirement: Master Source v2.4.0 §62, §68; PRD v1.3.0 §22, §23. Application rules: AFR-099, AFR-100, AFR-101.
Rules: 26, 03, 08. ADRs: 0009–0032 (architecture), 0040.

## Evidence
`docs/planning/*` (implementation plan, epic catalog, sprint roadmap, dependency map, DoR/DoD, test/evidence plan).

## Non-claims
No Laravel application, migration, model, controller, route, job, or UI is created. No sprint is executed.
Application implementation: **NOT STARTED**.

## Rollback
The sequence is planning guidance; reordering before implementation is a recorded decision. Once implementation
starts, sprint-level release gates govern change.
