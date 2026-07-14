# ADR 0056 — Platform Admin Trust Boundary

- **Status:** Accepted (2026-07-14, Asia/Makassar) — SPRINT-SF-05 Platform Admin skeleton; operator plane IN PROGRESS toward GO, business/module features NOT STARTED
- **Owner:** Principal Architect / Security & Privacy Lead
- **Rule:** `.claude/rules/31`, `.claude/rules/03` · **Canonical:** Master Source §72; §17, §18, §43; PRD v1.3.0 §9, §14.2; rules 31, 03, 04, 07

## Context
Operating a multi-tenant SaaS needs an operator plane (provision operators, suspend/reactivate tenants, read
platform audit) that is **separate** from any tenant's console and can never be reached through a tenant role — and
through which a tenant role can never reach tenant business data. The dangerous failure modes are a universal hidden
tenant bypass (e.g. a `Gate::before` that elevates platform users into every tenant), self-escalation to Super
Admin, removal of the last Super Admin (lock-out), insecure operator provisioning (fixed/plaintext passwords), silent
or unaudited tenant status changes, fabricated business metrics, and impersonation. This ADR fixes the platform trust
boundary so none of these are possible in the foundation.

## Decision
- **Separate plane.** The platform operator plane lives under `/platform-admin/*` and is distinct from every tenant
  surface. Platform roles (`SuperAdmin | Admin | Support | Finance | Auditor | ReadOnly`) are **fully separate** from
  tenant Spatie roles: a platform role grants **no** tenant-data access and a tenant role grants **no** platform
  access.
- **No universal bypass.** There is **no** `Gate::before` that elevates platform users and **no** hidden universal
  tenant bypass. Every platform mutation is authorized by a **specific** platform permission (per-permission, least
  privilege) and is **audited**.
- **No self-escalation; last-Super-Admin protection.** A user **cannot** self-escalate; only a Super Admin **may**
  grant Super Admin; the **last** Platform Super Admin **cannot** be removed, revoked, or demoted.
- **Secure provisioning.** `aish:platform-admin-provision` uses reset/invitation onboarding — **no** fixed password,
  **no** plaintext password in logs — and duplicate invocation fails safely.
- **Reason-required tenant status changes.** Suspend, reactivate, and mark-deletion-pending **require a reason**
  (except reactivation), are **audited**, **notify the tenant's owners**, and **never** hard-delete or silently
  change status. The platform tenant directory **must not** expose tenant business/customer/medical data, and
  platform metrics are truthful (no fabricated revenue, MRR, AI usage, or business KPIs).
- **Impersonation prohibited.** Impersonation is **prohibited**; introducing it requires a dedicated ADR, explicit
  product-owner approval, strong audit, a visible banner, reason/ticket, time limit, kill switch, no secret access,
  and security tests. Support uses **append-only** platform support notes instead.

## Alternatives
- **`Gate::before` super bypass for platform admins** — rejected: a single elevation point that grants access to
  every tenant is the exact cross-tenant risk the platform boundary must prevent.
- **Reuse tenant roles for platform operators** — rejected: couples the operator plane to tenant RBAC and leaks
  authority across the boundary; planes must be separate.
- **Fixed initial operator password** — rejected: insecure and unauditable; onboarding must be reset/invitation-based
  with no logged password.
- **Silent/hard tenant deletion** — rejected: status changes must be reason-required, audited, owner-notified, and
  reversible (deletion-pending, not hard delete).
- **Impersonation "for support"** — rejected here: only permissible behind a dedicated approving ADR with full
  safeguards; support notes cover the foundation need.

## Consequences
The operator plane can administer tenants and read platform audit without ever crossing into tenant business data or
being reachable from a tenant role; no operator can self-escalate or lock the platform out of its last Super Admin;
every operator mutation is least-privilege and audited; and no impersonation path exists in the foundation.

## Impacts
- **Security:** plane separation + no `Gate::before` bypass + per-permission authorization + last-Super-Admin
  protection remove the platform's highest-severity escalation and cross-tenant paths.
- **Privacy:** the tenant directory and metrics exclude tenant business/customer/medical data; audit metadata is
  sanitized (no secrets/tokens/passwords).
- **Tenant isolation:** a platform role cannot read tenant data and a tenant role cannot reach the platform plane;
  the boundary is enforced by policy and route separation.
- **Database:** adds platform role-assignment and append-only platform support-note tables; no tenant business tables.
- **Operational:** secure operator provisioning, reason-required audited tenant status changes, owner notification,
  and a truthful platform audit view.
- **Cost:** negligible; a small operator plane with per-permission checks, no additional providers.

## Verification / fitness function
`tests/Feature/Platform/*`, `tests/Feature/Console/Sf05CommandsTest.php`,
`tests/Feature/Security/Sf05CrossTenantMatrixTest.php`, and `tests/Architecture/Sf05BoundariesTest.php` assert:
platform/tenant role separation; no `Gate::before` bypass; per-permission authorization; self-escalation blocked;
last-Super-Admin protected; secure duplicate-safe provisioning (no logged password); reason-required audited tenant
status changes with owner notification; impersonation absent; append-only support notes. AFR-166, AFR-167, AFR-168,
AFR-169; SC-33, SC-34, SC-35, SC-36.

## Related
Requirement: Master Source §72; §17, §18, §43; PRD v1.3.0 §9, §14.2. Application rules: AFR-166..AFR-169.
Rules: 31, 03, 04, 07. ADRs: 0013, 0029, 0051, 0052, 0053, 0054, 0055.

## Evidence
`app/Platform/*`, `app/Http/Controllers/Platform/*`, `app/Models/{PlatformRoleAssignment,PlatformSupportNote}*`,
`app/Console/Commands/*PlatformAdminProvision*`, `app/Policies/Platform/*` (forthcoming under SPRINT-SF-05);
`docs/governance/foundation-coverage-matrix.md`; `docs/evidence/sprint-sf-05/` (forthcoming).

## Non-claims
This ADR does not introduce impersonation, does not create any business/feature module (`app/Modules/*` remains
**NOT STARTED**), and does not expose tenant business/customer/medical data. It does not claim deployment, pilot, or
production readiness, and does not assert the SPRINT-SF-05 release is merged, tagged, CI-green, or
clean-checkout-verified — those remain **PLANNED** until evidenced.

## Rollback
Plane separation, no-`Gate::before`-bypass, per-permission least privilege, no self-escalation, last-Super-Admin
protection, secure provisioning, reason-required audited tenant status changes, and no-impersonation are permanent
guarantees; loosening any of them requires an owner-approved Master Source update, and impersonation additionally
requires a dedicated approving ADR.
