# ADR 0053 — Tenant Membership and Context Model

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 6 SaaS Core Foundation; membership/context model IN PROGRESS, business/module features NOT STARTED
- **Owner:** Principal Architect / Security & Privacy Lead
- **Rule:** `.claude/rules/30`, `.claude/rules/03` · **Canonical:** Master Source §17, §34–§42, §43; PRD v1.3.0 §9, §14.2; rules 30, 03, 04, 07

## Context
Tenancy is shared-DB / shared-schema / row-level ownership keyed on `tenant_id` (+ `branch_id`) per ADRs
0011/0012/0015, with row-level security (RLS) remaining optional/future (OD-01, NOT implemented). Users must be
able to belong to a tenant with a clear lifecycle, be scoped to specific branches or the whole tenant, and never
gain access through a stale, ambiguous, or self-elevated path. Public self-service registration is a spam and
tenant-pollution risk and is out of MVP scope; users must arrive through a controlled, auditable path. The system
also needs an unambiguous, tamper-resistant notion of "the current tenant/branch" for every request and job so
isolation is fail-closed rather than best-effort.

## Decision
- **Global user identity.** `users` are global (one identity, unique email); users carry **no** tenant role or
  `tenant_id` column. A user gains tenant access **only** through an **active** membership. Users gain a `status`
  and `last_authenticated_at` column only. Public/self-service registration is **DISABLED**; users arrive via
  secure provisioning + invitation.
- **Explicit membership.** An explicit `tenant_memberships` pivot links users to tenants with states
  `invited | active | suspended | revoked`. An `all_branches` flag grants tenant-wide branch scope; branch-restricted
  members are scoped through `branch_access_grants`. **Invariant:** the last active owner of a tenant **cannot** be
  removed, revoked, suspended, or demoted (last-active-owner protection), enforced at the service/action layer.
- **Invitation-driven onboarding.** `tenant_invitations` carry a **one-time hashed token** (never stored or logged
  in plaintext), an expiry, tenant/branch scope, and a target role; acceptance is **race-safe** (single-use,
  atomic) and creates/activates a membership. Public route keys use **ULIDs** (anti-IDOR), not sequential ids.
- **Immutable tenant context.** `TenantContext` is an **immutable**, request/job-scoped value object resolved from
  the authenticated user's active membership (and selected branch). It has **no silent fallback to "first tenant"**;
  a tenant-required action without a validated context **fails closed**. Context is **cleared between requests and
  between jobs**; switching tenants requires an active membership for the target tenant.
- **Fail-closed scope.** A `TenantScope` global scope + `BelongsToTenant` trait apply `tenant_id` to every
  tenant-owned model query and **fail closed** (no context ⇒ no rows / explicit error, never all-tenant rows).
- **Tenant-scoped RBAC.** Authorization uses Spatie `laravel-permission` with `teams = true` keyed on `tenant_id`
  (per ADR 0013), so roles/permissions are tenant-scoped; platform roles are separate from tenant roles.
- **State enforcement & audit.** Suspended tenants/users and suspended/revoked memberships fail closed; revoked
  permissions must not survive as authorization via unsafe stale cache. Membership, invitation, provisioning,
  suspension, revocation, and role changes are audited to the append-only `audit_logs` (no updated_at; update/delete
  blocked at the model layer) with sanitized metadata (no tokens/secrets/passwords).

## Alternatives
- **`tenant_id` column on `users` (single-tenant-per-user)** — rejected: prevents a user serving multiple tenants
  (corporate admins, support), and couples identity to a tenant.
- **Implicit membership via role assignment only** — rejected: no explicit lifecycle state, harder to suspend/revoke
  and audit; ambiguous access.
- **Self-service public registration** — rejected for MVP: spam/tenant-pollution and privacy risk; onboarding is
  provisioning + invitation only.
- **Mutable/ambient global "current tenant" (e.g. mutable singleton)** — rejected: enables silent cross-tenant
  bleed on reuse across requests/jobs; context must be immutable and explicitly cleared.
- **RLS-first isolation now** — deferred (OD-01): application-layer fail-closed scope is the Step 6 baseline; RLS
  remains optional/future defense-in-depth.

## Consequences
Every tenant-scoped action runs under a validated, immutable context or fails closed; access is always via an
explicit, auditable, state-bearing membership; a tenant can never lose its last active owner; onboarding is
controlled and race-safe. Business modules (future) inherit this model and never re-implement it.

## Impacts
- **Security:** fail-closed context + global scope + hashed single-use invitations + last-owner protection reduce
  cross-tenant and privilege-escalation risk; anti-IDOR ULIDs on public keys.
- **Privacy:** invitation tokens are hashed and never logged; audit metadata is sanitized; minimal PII on `users`.
- **Tenant isolation:** context propagates to queries, jobs, cache, storage, and logs; no silent fallback.
- **Database:** adds `tenant_memberships`, `branch_access_grants`, `tenant_invitations`, `users.status`,
  `users.last_authenticated_at`; tenant-owned rows carry `tenant_id` (+ `branch_id` where branch-relevant).
- **Operational:** clear suspend/revoke lifecycle; kill-switch-compatible state enforcement; auditable onboarding.
- **Cost:** one pivot join per authorization path; negligible vs. isolation guarantees.

## Verification / fitness function
`tests/Feature/Auth/*` and `tests/Feature/Tenancy/*` assert: registration disabled; access only via active
membership; fail-closed context (no fallback); cleared context between requests/jobs; branch-restriction
enforcement; last-active-owner protection; single-use race-safe invitation acceptance; suspended/revoked fail-closed;
cross-tenant IDOR blocked. SC-01, SC-02, SC-03, SC-07, SC-08, SC-09, SC-10, SC-19.

## Related
Requirement: Master Source §17, §34–§42, §43; PRD v1.3.0 §9, §14.2. Application rules: AFR-134..AFR-141, AFR-152.
Rules: 30, 03, 04, 07, 13. ADRs: 0011, 0012, 0013, 0015, 0029, 0051, 0052.

## Evidence
`app/Models/{Tenant,Branch,TenantMembership,BranchAccessGrant,TenantInvitation,User}.php`, `app/Tenancy/*`,
`app/Actions/*`, `app/Policies/*`, `app/Audit/*` (forthcoming under Step 6);
`docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-6/` (forthcoming).

## Non-claims
This model does not create any business/feature module (`app/Modules/*` remains **NOT STARTED**), and does not
claim deployment, pilot, or production readiness. Sanctum token/API surface is installed and foundation-ready but
**not wired** this step. RLS is **not** implemented (OD-01). This ADR does not assert the Step 6 release is merged,
tagged, CI-green, or runtime-verified — those remain **PLANNED** until evidenced.

## Rollback
Membership states, last-owner protection, fail-closed context, and registration-disabled are permanent isolation
guarantees; loosening any of them requires an owner-approved Master Source update. Additive changes (new membership
metadata, additional context resolvers) are recorded decisions.
