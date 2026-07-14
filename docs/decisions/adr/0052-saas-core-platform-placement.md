# ADR 0052 — SaaS Core Platform Placement (Top-Level `app/`, Not `app/Modules/`)

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 6 SaaS Core Foundation; platform-core placement IN PROGRESS, business modules NOT STARTED
- **Owner:** Principal Architect / Laravel SaaS Architect
- **Rule:** `.claude/rules/30`, `.claude/rules/20` · **Canonical:** Master Source §34–§42; PRD v1.3.0 §11; rules 20, 03, 30

## Context
ADR 0009 fixes a Laravel 12 modular monolith with 17 business modules under `app/Modules/`, each owning its data
(ADR 0010). The SaaS core foundation delivered in Step 6 — authentication, tenant/branch context, membership,
invitation, tenant-scoped RBAC, audit, and cache/queue/storage isolation — is not a business capability; it is the
cross-cutting substrate that every business module depends on. Placing it inside a business module (or inventing a
pseudo-module for it) would invert the dependency direction (business modules would depend on a sibling module's
internals), couple the substrate to premature module boundaries, and make the isolation primitives (tenant context,
global scope, audit) harder to apply uniformly across all modules.

## Decision
The SaaS core foundation is implemented as **platform-core infrastructure in top-level `app/` namespaces**, not
inside `app/Modules/`. The core lives in `app/Models` (tenants, branches, memberships, invitations, users,
audit_logs), `app/Tenancy` (TenantContext, TenantScope, BelongsToTenant, resolvers), `app/Audit` (audit recorder
and append-only guard), `app/Http` (middleware, controllers for the SaaS-core surface), `app/Actions`
(provisioning, invitation, membership, branch actions), `app/Policies`, `app/Notifications`, and `app/Support`
(cache-key, storage-path, log-context, queue-envelope helpers). The 17 business feature modules under
`app/Modules/` remain **NOT STARTED**; when they are built they **layer on** this platform-core foundation and
**MUST NOT** re-implement tenancy, RBAC, audit, or isolation primitives. The platform core **MUST NOT** depend on
any business module (dependency direction is business-module → platform-core only).

## Alternatives
- **Place the core inside a `Modules/Platform` or `Modules/Core` module** — rejected: makes the substrate a sibling
  of business modules, inverts dependency direction, and couples cross-cutting primitives to a module boundary.
- **Distribute tenancy/audit primitives into each business module** — rejected: guarantees divergent, unauditable
  isolation and violates single-source-of-truth for the isolation contract.
- **A separate package/repository for the core** — rejected: premature; violates the single canonical repository
  (rule 00) with no ADR justifying extraction.

## Consequences
Every business module builds on one uniform tenancy/RBAC/audit/isolation substrate. The 17-module boundary model
(ADR 0010) is preserved for business features; the core is a dependency-free foundation those modules import. No
business module exists yet, so no module coupling is introduced.

## Impacts
- **Security:** one authoritative implementation of tenant context, global scope, RBAC, and audit reduces the
  isolation attack surface and centralizes review.
- **Privacy:** sanitized audit/log context is applied uniformly from a single `app/Support` location.
- **Tenant isolation:** `app/Tenancy` primitives are applied consistently across all future modules.
- **Database:** core tables are owned by the platform core; business-module tables (future) reference them but the
  core owns tenancy/audit schema.
- **Operational:** core namespaces are stable substrate; module churn does not destabilize isolation primitives.
- **Cost:** avoids rework of re-homing cross-cutting code once business modules begin.

## Verification / fitness function
`scripts/codex/check-agents.sh` / architecture fitness checks assert no `app/Modules/*` dependency from
`app/Tenancy`, `app/Audit`, or core namespaces, and that business-module scaffolding remains absent. SC-13, SC-20.

## Related
Requirement: Master Source §34–§42; PRD v1.3.0 §11. Application rules: AFR-136, AFR-146, AFR-069. Rules: 30, 20,
03. ADRs: 0009, 0010, 0011, 0012, 0015, 0051, 0053.

## Evidence
`app/Tenancy/*`, `app/Audit/*`, `app/Models/*`, `app/Support/*`, `app/Http/Middleware/*`, `app/Policies/*`
(forthcoming under Step 6); `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-6/` (forthcoming).

## Non-claims
This placement decision does not create or start any business module; `app/Modules/*` business features remain
**NOT STARTED**. It does not claim the core is deployed, pilot-ready, or production-ready. It does not assert the
Step 6 release is merged, tagged, CI-green, or runtime-verified — those remain **PLANNED** until evidenced.

## Rollback
If a future ADR justifies extracting a module (ADR 0020 criteria), core primitives may be repackaged then. Moving
the core into a business module or a separate repository requires an owner-approved ADR + Master Source update.
