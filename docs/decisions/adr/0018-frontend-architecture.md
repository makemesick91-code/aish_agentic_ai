# ADR 0018 — Frontend Architecture

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Frontend / Product Architect
- **Rule:** `.claude/rules/10`, `20` (AFR-047) · **Canonical:** Master Source v2.3.0 §34, §52, §53; PRD v1.2.0 §16, §21

## Context
The MVP console must be usable, accessible, mobile-responsive, and truthful, with the least operational burden
for the pilot.

## Decision
Initial frontend is **Blade + Tailwind CSS + Alpine.js**, server-rendered with progressive enhancement. Every
screen provides empty/loading/failure/permission-denied states and uses the canonical truthful-state
vocabularies; basic UI does not depend on AI. A richer SPA/Inertia/API-first frontend is a future ADR. See
[Frontend Architecture](../../architecture/FRONTEND_ARCHITECTURE.md).

## Alternatives
- **SPA (React/Vue) from day one** — rejected for MVP: build/ops overhead; no pilot benefit yet (OD-04).

## Consequences
Fast, accessible console with minimal tooling; migration path preserved via API-first backend.

## Impacts
- **Security:** server-authorized rendering; UI is not a trust boundary.
- **Privacy:** UI shows only authorized, scoped data; no sample-as-real data.
- **Tenant isolation:** reflects caller's tenant/branch scope.
- **Database:** none new.
- **Operational:** no separate frontend build/deploy for MVP.
- **Cost:** low.

## Verification / fitness function
FF-DOC-03 (truthful states in UI vocabulary). Implementation: state-rendering + AI-independence tests.

## Related
Requirement: Master Source §52, §53; PRD §16, §21. Application rule: AFR-047. ADRs: 0010, 0028.

## Evidence
`docs/architecture/FRONTEND_ARCHITECTURE.md`.

## Non-claims
No views, components, or assets are implemented in Step 3.

## Rollback / supersession
Superseded by a frontend ADR + Master Source update; truthful-state requirements are permanent.
