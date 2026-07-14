---
id: "30"
title: SaaS Core Foundation
domain: saas-core-tenancy-authz-isolation
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §34–§42 (stack/tenancy/RBAC), §36 (data), §43 (security), §50, §54 (tests/gates)"
  - "PRD v1.3.0 §9, §11, §14.2, §23"
  - "ADRs 0011, 0012, 0013, 0015, 0029, 0051, 0052, 0053; AFR-134..154; rules 03, 04, 05, 07, 20, 26, 29"
supersede: "Permanent for Step 6+. Fail-closed tenant context, membership-only access, defense-in-depth authorization, append-only audit, and cross-tenant isolation cannot be weakened; superseded only by a higher-version Master Source update preserving these guarantees."
---

# Rule 30 — SaaS Core Foundation

## Purpose
Keep the SaaS core substrate — identity, tenant/branch context, membership, tenant-scoped authorization, audit, and
cache/queue/storage/log isolation — fail-closed, auditable, and free of cross-tenant leakage from a clean checkout
onward, without weakening any security, privacy, documentation, or release gate.

## Scope
Global user identity, tenant and branch context, membership and invitation, authorization, database/cache/queue/storage
isolation, audit, logging, and state enforcement for the platform-core namespaces. Applies to `app/Models`,
`app/Tenancy`, `app/Audit`, `app/Http`, `app/Actions`, `app/Policies`, `app/Notifications`, `app/Support`,
`database/`, and `tests/Feature/{Auth,Tenancy}/`. The SaaS core is platform-core infrastructure in top-level `app/`
namespaces, **not** inside `app/Modules/` (ADR 0052); business modules remain **NOT STARTED**.

## Rules

### Global user identity and authentication
- Users **MUST** be global identities; a user **MUST NOT** carry a `tenant_id` column or a tenant role, and **MUST**
  gain tenant access only through an **active** membership (ADR 0053).
- Public/self-service registration **MUST** be disabled; users **MUST** arrive via secure provisioning + invitation.
- Authentication **MUST NOT** enumerate users (no response/timing oracle distinguishing existing vs. non-existing
  accounts); credentials, tokens, and reset artifacts **MUST NOT** be logged.

### Tenant context
- A tenant-required action **MUST NOT** run without a validated `TenantContext`; there **MUST NOT** be a silent
  fallback to the first/any tenant — absence of context **MUST** fail closed.
- `TenantContext` **MUST** be immutable and request/job-scoped, and **MUST** be cleared between requests and between
  jobs; a tenant switch **MUST** require an active membership for the target tenant.

### Branch context
- A selectable branch **MUST** belong to the current tenant; a branch-restricted user **MUST NOT** access another
  branch's data; an inactive branch **MUST NOT** be selectable.

### Authorization
- UI hiding **MUST NOT** be treated as sufficient authorization; policies **and** service/action-layer checks
  **MUST** both enforce access (defense in depth).
- Platform roles and tenant roles **MUST** be kept separate; tenant-scoped RBAC uses Spatie `laravel-permission`
  with `teams = true` keyed on `tenant_id` (ADR 0013).
- A user **MUST NOT** self-escalate privileges; the last active owner of a tenant **MUST NOT** be removed, revoked,
  suspended, or demoted.

### Database isolation
- Every tenant-owned record **MUST** carry `tenant_id` (and `branch_id` where branch-relevant); relationships
  **MUST NOT** cross tenant boundaries.
- A cross-tenant read or write (IDOR) **MUST** be treated as a release blocker; public route keys **MUST** use ULIDs,
  not sequential ids.

### Cache
- Cache keys **MUST** be tenant-namespaced and **MUST NOT** collide across tenants; a broad Redis `flush`
  **MUST NOT** be used as application behavior.

### Queue
- Queued jobs handling tenant data **MUST** carry a validated tenant context envelope; context **MUST** be cleared
  after each job; a retry **MUST NOT** switch or drop the tenant, and **MUST NOT** create duplicate side effects.

### Storage
- Tenant files **MUST** be stored under a tenant (and branch) path prefix (`tenants/{id}/branches/{id}/...`); path
  traversal **MUST** be prevented; tenant storage **MUST** default to private (no public listing).

### Audit
- Security- and admin-relevant actions (provisioning, membership/invitation changes, role/permission changes,
  suspension/revocation, sensitive mutation) **MUST** be audited; audit metadata **MUST** be sanitized and
  **MUST NOT** contain secrets, tokens, or passwords.
- The audit log **MUST** be append-oriented (no `updated_at`; update/delete blocked at the model layer); audit
  history **MUST NOT** be deletable.

### Logging
- Log context **MUST** be structured and carry tenant context; logs **MUST NOT** contain PII, secrets, or tokens,
  and a worker/request **MUST NOT** leak another tenant's context into logs.

### State enforcement
- A suspended tenant, suspended user, or suspended/revoked membership **MUST** fail closed on every surface.
- A revoked permission **MUST NOT** survive as effective authorization through an unsafe stale cache; permission
  caches **MUST** be invalidated on change.

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These permanent decisions are recorded and remain binding, but their **application** implementation is scheduled in
later steps (rules 05, 06, 18, 07); the SaaS core foundation **MUST NOT** be read as delivering them:
- Human approval for public/high-risk actions (rules 05, 18).
- Google Review anti-gating and review-reply safety (rules 06, 18).
- Prompt-injection defense and tool allowlisting for untrusted feedback/reviews (rules 04, 05).
- AI tracing, prompt/model versioning, and tool-call logging (rules 05, 07).
- AI cost logging and cost limits (rules 05, 07).
- Google credential/OAuth encryption at rest and rotation (rules 04, 06).
- Knowledge-base / RAG retrieval tenant-and-branch scoping (rules 03, 05, 07).
- Reliable-before-autonomous (manual → semi-automated → approved automation → limited autonomy) (rules 02, 05).
- Manual workflow usable without AI (rules 05, 17).

## Required checks
- `tests/Feature/Tenancy/*` and `tests/Feature/Auth/*` (fail-closed context, membership-only access, branch
  restriction, last-owner protection, cross-tenant IDOR blocked, cache/queue/storage isolation, suspension/revocation
  fail-closed); the cross-tenant security test matrix; the consolidated Step 6 GO/WATCH/NO-GO gate; a clean-checkout
  SaaS-core verification on the merged SHA; `scripts/docs/secret-scan.sh`; the `backend-runtime-ci` gate (rule 29, 28).

## Evidence
- `app/Tenancy/*`, `app/Audit/*`, `app/Models/*`, `app/Http/Middleware/*`, `app/Policies/*`, `app/Support/*`;
  `tests/Feature/Tenancy/*`, `tests/Feature/Auth/*`; `docs/governance/foundation-coverage-matrix.md`;
  `docs/evidence/step-6/` (forthcoming).

## Related canonical sections
- Master Source §17, §34–§42 (stack/tenancy/RBAC), §36 (data), §43 (security), §50, §54 (tests/gates); PRD v1.3.0
  §9, §11, §14.2, §23; ADRs 0011, 0012, 0013, 0015, 0029, 0051, 0052, 0053; AFR-134..154; rules 03, 04, 05, 07, 20,
  26, 29.

## Supersession
Permanent for Step 6+. Fail-closed tenant context, membership-only access, registration-disabled onboarding,
defense-in-depth authorization, last-active-owner protection, append-only audit, and cross-tenant isolation are
permanent; superseded only by a higher-version Master Source update that preserves these guarantees.
