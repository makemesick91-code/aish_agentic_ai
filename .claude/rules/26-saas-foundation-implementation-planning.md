---
id: "26"
title: SaaS Foundation Implementation Planning
domain: architecture
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §62; §68 (implementation order/plan)"
  - "PRD v1.3.0 §22, §23"
  - "ADRs 0039, 0040; AFR-099..102"
supersede: "Only via a versioned Master Source update; the sequence-before-features and runtime-evidence rules are permanent."
---

# Rule 26 — SaaS Foundation Implementation Planning

## Purpose
Fix the SaaS Foundation build order so implementation minimizes rework and never exposes tenant data or false status.

## Scope
Epic/sprint planning, implementation sequence, Definition of Ready/Done, deployment-target class, and readiness gates.

## Rules
- The SaaS Foundation **MUST** follow the fixed implementation sequence (runtime bootstrap → local/CI →
  config/secrets → auth → tenant/branch context → RBAC → audit → queue/cache/storage isolation → notification →
  subscription skeleton → admin skeleton → observability → backup/restore → deployment/rollback → verification);
  tenant context **MUST** precede business features (AFR-099).
- RBAC **MUST** precede a privileged console; audit **MUST** precede sensitive mutation; queue/storage isolation
  **MUST** precede external async work and tenant upload (AFR-100).
- Observability and tested restore **MUST** precede pilot; runtime evidence **MUST** precede any deployment claim
  (AFR-101). The pilot/production deployment-target **MUST** be a dedicated isolated class and **MUST NOT** share
  DB/redis/pool/secrets with DaengtisiaMS or Aish POS by default (AFR-102).
- Implementation **MUST NOT** begin before the Definition of Ready is satisfied and the Step 4 GO tag is verified.
  Each sprint **MUST** have its own GO/WATCH/NO-GO gate and a Master Source update rule.
- No application code, migration, or runtime **MUST** be claimed as created during planning; status **MUST** stay
  `NOT STARTED` until evidenced.

## Required checks
- `scripts/docs/check-step4-coverage.sh` verifies the sequence, EPIC-SF-01..16, the sprint roadmap with
  GO/WATCH/NO-GO, Definition of Ready/Done, and the deployment-target class.

## Evidence
- `docs/planning/*`.

## Related canonical sections
- Master Source v2.4.0 §62, §68; PRD v1.3.0 §22, §23; ADRs 0039, 0040; rules 03, 08, 11, 19, 27.

## Supersession
Sequence-before-features and runtime-evidence-before-claims are permanent; sprint scope may be re-planned via a
recorded decision and, when material, a Master Source update.
