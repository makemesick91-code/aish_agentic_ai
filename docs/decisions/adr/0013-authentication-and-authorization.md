# ADR 0013 — Authentication and Authorization

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Security and Privacy Architect
- **Rule:** `.claude/rules/03`, `04`, `16`, `20` (AFR-021, AFR-022) · **Canonical:** Master Source v2.3.0 §34, §43

## Context
The app needs enterprise-grade auth that is tenant/branch-scoped, MFA-ready, and independent of AI availability,
covering the minimum pilot roles.

## Decision
Authenticate via **Laravel Fortify (web) / Sanctum (API)**; authorize via **Spatie Permission wrapped in
tenant+branch scope policies**. Effective access = permission ∧ tenant scope ∧ branch scope. High-risk abilities
require explicit permission **and** human approval where mandated. See
[Identity & Access Architecture](../../architecture/IDENTITY_AND_ACCESS_ARCHITECTURE.md).

## Alternatives
- **Roles without tenant/branch scoping** — rejected: permits cross-tenant/branch access.
- **Custom auth stack** — rejected: reinvents audited framework primitives.

## Consequences
Least-privilege, scoped access with MFA-ready identity. Requires scope checks at policy **and** query layers.

## Impacts
- **Security:** MFA-ready; least privilege; OAuth state validated (ADR 0022).
- **Privacy:** access to PII gated by scope + permission.
- **Tenant isolation:** user scope ⊆ tenant/branch on every request.
- **Database:** roles/permissions/sessions are tenant-owned tables.
- **Operational:** standard framework tooling; auditable auth events.
- **Cost:** low.

## Verification / fitness function
FF-TEN-11 (API scope), FF-SEC-03 (approval on high-risk). Implementation: authz/privilege-escalation tests.

## Related
Requirement: Master Source §34, §43; `.claude/rules/16` role coverage. Application rule: AFR-021, AFR-022. ADRs: 0012, 0022, 0028.

## Evidence
`docs/architecture/IDENTITY_AND_ACCESS_ARCHITECTURE.md`.

## Non-claims
No auth, RBAC, MFA, or policy code runs in Step 3.

## Rollback / supersession
Superseded only by a security ADR + Master Source update; scoping and approval requirements are permanent.
