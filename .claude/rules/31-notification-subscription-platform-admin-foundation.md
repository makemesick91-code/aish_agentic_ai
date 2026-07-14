---
id: "31"
title: Notification, Subscription, and Platform Admin Foundation
domain: notification-subscription-platform-admin
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §72; §17, §34–§42, §43, §46, §50, §51, §53, §54"
  - "PRD v1.3.0 §11, §14, §16, §18.2, §23"
  - "ADRs 0054, 0055, 0056; ADRs 0011–0013, 0016, 0029, 0051–0053; AFR-155..170; rules 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 28, 29, 30"
supersede: "Permanent for SF-05+. Fail-closed tenant-safe notification, truthful delivery states, notification idempotency, single authoritative entitlement resolver, distinct-and-precedent security state, platform/tenant plane separation, least privilege, no self-escalation, last-super-admin protection, no-impersonation, and append-only audit cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees."
---

# Rule 31 — Notification, Subscription, and Platform Admin Foundation

## Purpose
Keep the SPRINT-SF-05 substrate — notification delivery, subscription/entitlement, and the platform
operator plane — tenant-safe, fail-closed, truthful, audited, and free of privilege escalation or
cross-tenant leakage from a clean checkout onward, without weakening any security, privacy,
documentation, or release gate.

## Scope
Notification domain, delivery, preferences, and channels; subscription plans, entitlements, usage
metering, and reconciliation; the platform operator plane (roles, permissions, provisioning, tenant
administration, support notes, audit). Applies to `app/Notifications`, `app/Jobs/Notifications`,
`app/Services/Notifications`, `app/Mail`, `app/Subscriptions`, `app/Platform`,
`app/Http/Controllers/Platform`, `app/Http/Controllers/Tenancy/{Notification*,SubscriptionOverview*}`,
`app/Models/{Notification*,Plan*,TenantSubscription,SubscriptionEvent,UsageRecord,PlatformRoleAssignment,PlatformSupportNote}`,
`app/Enums/{Notification*,Plan*,Subscription*,FeatureType,PlatformRole}`, related `database/` and
`tests/Feature/{Notifications,Subscriptions,Platform,Security,Audit,Console}`. This is platform-core
infrastructure in top-level `app/` namespaces, not inside `app/Modules/`; business modules remain
**NOT STARTED**.

## Rules

### Notification foundation
- Tenant notifications **MUST** be tenant-scoped; the recipient's membership of the tenant **MUST** be
  verified before dispatch, and a tenant **MUST NOT** notify another tenant's members.
- Delivery state **MUST** be truthful: `queued` is **NOT** `sent`; `sent` means the channel adapter
  accepted the message (for email: accepted by the mail transport), never a proven end-user receipt.
- A logical event **MUST** produce exactly one delivery per recipient per channel; retries and duplicate
  dispatch **MUST NOT** create a duplicate logical delivery (globally-unique dedup key; terminal
  deliveries are a no-op on re-run).
- Retries **MUST** be bounded; a failed delivery **MUST** carry an explicit state and a sanitized failure
  code. Notifications **MUST** be enqueued only through the single dispatcher, and mail **MUST** be sent
  only through the mail channel adapter.
- Notification content and delivery records **MUST** minimize PII and **MUST NOT** contain tokens,
  secrets, passwords, or customer/medical content.
- Critical security notifications **MUST NOT** be silenced by a preference. Preference and quiet-hours
  evaluation **MUST** be timezone-aware; suppressed deliveries **MUST** be recorded truthfully.
- The in-app inbox **MUST** be scoped to the current tenant and the acting recipient; mark-as-read
  **MUST** re-verify ownership (no recipient-swap / delivery IDOR).

### Subscription and entitlement
- Subscription (commercial) state and payment state are **NOT** equivalent; no paid/collected state
  **MUST** be claimed without provider evidence. Payment, invoicing, tax, and dunning are out of scope.
- Plans and entitlement keys **MUST** be explicit and typed; an unknown entitlement key **MUST** fail
  closed. Entitlement decisions **MUST** use the single authoritative resolver — no duplicated plan logic.
- Plan versions **MUST NOT** silently change historical meaning; a retired plan **MUST NOT** be newly
  assigned but existing references **MUST** stay valid.
- Usage records **MUST** be tenant-scoped and idempotent; a repeated increment **MUST NOT** double-count;
  a negative quantity **MUST** be refused outside an explicit correction workflow; period boundaries
  **MUST** be timezone-aware.
- Invalid subscription state transitions **MUST** be rejected; reconciliation **MUST** be idempotent and
  safe to rerun, emitting a transition/notification at most once.
- A commercial restriction is distinct from a security suspension; **security suspension always takes
  precedence** and a commercial state **MUST NOT** override a tenant/user/membership security state.

### Platform Admin
- Platform roles and tenant roles **MUST** be kept separate; a platform role **MUST NOT** grant
  tenant-data access and a tenant role **MUST NOT** grant platform access. There **MUST NOT** be a
  universal hidden tenant bypass or a `Gate::before` that elevates platform users.
- Least privilege is mandatory. Every platform mutation **MUST** be authorized by a specific platform
  permission and **MUST** be audited. Self-escalation is prohibited; only a Super Admin **MAY** grant
  Super Admin; the last Platform Super Admin **MUST NOT** be removed, revoked, or demoted.
- Platform operator provisioning **MUST** be secure: no fixed password, no plaintext password in logs,
  onboarding via a reset/invitation flow; duplicate invocation **MUST** fail safely.
- Tenant operational status changes (suspend, reactivate, mark deletion-pending) **MUST** require a
  reason (except reactivation), **MUST** be audited, **MUST** notify the tenant's owners, and **MUST NOT**
  hard-delete or silently change status.
- Platform metrics **MUST** be truthful (no fabricated revenue, MRR, AI usage, or business KPIs). The
  platform tenant directory **MUST NOT** expose tenant business/customer/medical data.
- **Impersonation is prohibited.** No developer **MAY** introduce impersonation without a dedicated ADR,
  explicit product-owner approval, strong audit, a visible banner, reason/ticket, time limit, kill
  switch, no secret access, and security tests.

### Append-only and audit
- Subscription events and platform support notes **MUST** be append-oriented (no `updated_at`;
  update/delete blocked at the model layer). Audit metadata **MUST** be sanitized and **MUST NOT** contain
  secrets, tokens, passwords, message bodies, or customer/medical content, and **MUST** distinguish
  platform from tenant context.

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These remain binding but their application is scheduled later; SF-05 **MUST NOT** be read as delivering
them: WhatsApp/SMS/Slack/Teams/push/webhook notification channels; a production email provider; payment
gateway, invoicing, tax, and dunning; human approval and Google Review anti-gating (rules 05, 06, 18);
prompt-injection defense and AI tracing/cost (rules 04, 05); impersonation (prohibited until an approving
ADR exists).

## Required checks
- `tests/Feature/Notifications/*`, `tests/Feature/Subscriptions/*`, `tests/Feature/Platform/*`,
  `tests/Feature/Security/Sf05CrossTenantMatrixTest.php`, `tests/Feature/Audit/Sf05AuditTest.php`,
  `tests/Feature/Sf05MigrationIntegrityTest.php`, `tests/Feature/Console/Sf05CommandsTest.php`;
  `tests/Architecture/Sf05BoundariesTest.php` and `tests/Architecture/TenancyBoundariesTest.php`; the
  consolidated Step-6-style GO/WATCH/NO-GO gate; a clean-checkout SF-05 verification on the merged SHA
  (`scripts/runtime/verify-sf-05.sh`); `scripts/docs/secret-scan.sh`; the `backend-runtime-ci` gate
  (rules 28, 29).

## Evidence
- `app/Notifications/*`, `app/Jobs/Notifications/*`, `app/Services/Notifications/*`, `app/Mail/*`,
  `app/Subscriptions/*`, `app/Platform/*`, `app/Http/Controllers/Platform/*`; `tests/Feature/*`;
  `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/sprint-sf-05/` (forthcoming).

## Related canonical sections
- Master Source §72; §17, §34–§42, §43, §46, §50, §51, §53, §54; PRD v1.3.0 §11, §14, §16, §18.2, §23;
  ADRs 0054–0056; ADRs 0011–0013, 0016, 0029, 0051–0053; AFR-155..; rules 03, 04, 05, 06, 07, 09, 10, 11,
  18, 20, 26, 28, 29, 30.

## Supersession
Permanent for SF-05+. Fail-closed tenant-safe notification, truthful delivery states, notification
idempotency, single authoritative entitlement resolver, distinct-and-precedent security state,
platform/tenant plane separation, least privilege, no self-escalation, last-super-admin protection,
no-impersonation, and append-only audit are permanent; superseded only by a higher-version Master Source
update that preserves these guarantees.
