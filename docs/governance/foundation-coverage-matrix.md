# Foundation Coverage Matrix — Aish Agentic AI

Maps permanent product/engineering foundations to the rule that governs them, how they are enforced at runtime
and in tests/CI, current implementation status, and evidence. Derived, auditable, non-authoritative (authority
order: [CLAUDE.md](../../CLAUDE.md) §2). Truthful-status vocabulary per CLAUDE.md §5.

Status legend: `RULE ESTABLISHED — LATER STEP` = permanent rule recorded now, implementation scheduled in a later
step; `IMPLEMENTED (FOUNDATION)` = runtime foundation exists and is verified; `IN PROGRESS (FOUNDATION)` = foundation
being implemented in the current step with code/test paths defined, verification/evidence forthcoming (not yet
verified, merged, or tagged); `PLANNED` = defined, execution pending; `NOT STARTED` = not begun.

## Step 5 runtime foundation (AFR-127..133; rule 29; ADRs 0047–0050)

| Foundation | Source | Decision | Rule | Runtime enforcement | Test/CI enforcement | Status | Evidence | Gap |
|-----------|--------|----------|------|---------------------|---------------------|--------|----------|-----|
| Pinned runtime versions | MS §70.1; AFR-127 | Laravel 12 / PHP 8.4(^8.3) / PG17 / Redis7 / Node22, identical local/CI/docs | [29](../../.claude/rules/29-runtime-bootstrap-and-operations.md), 25 | `composer.json` platform pin; `preflight.sh` PHP≥8.3 | `backend-runtime-ci` PHP 8.4; `composer validate` | IMPLEMENTED (FOUNDATION) | ADR [0047](../decisions/adr/0047-runtime-version-and-support-policy.md); `composer.lock` | — |
| Reproducible bootstrap | MS §70.2; AFR-128 | Idempotent, fail-fast, no-root, no-`.env`-overwrite, no-secret | 29, 24 | `scripts/runtime/bootstrap-local.sh` | clean-checkout verify on merge SHA | IMPLEMENTED (FOUNDATION) | ADR [0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md) | — |
| Env contract / no secrets | MS §70.3; AFR-131 | `.env.example` placeholders only; no debug in prod | 29, 04, 24 | `RuntimePreflight`; `ConfigurationHealthCheck` | `secret-scan.sh`; `PreflightCommandTest` | IMPLEMENTED (FOUNDATION) | `.env.example`; `secret-scan.log` | — |
| Truthful health/readiness | MS §70.4; AFR-129 | `/live` no external dep; `/ready` 503 on failure; no leak | 29, 10, 11 | `Liveness/ReadinessController`, `config/health.php` | `tests/Feature/Health/*`; `verify-runtime.sh` neg path | IMPLEMENTED (FOUNDATION) | ADR [0049](../decisions/adr/0049-health-and-readiness-contract.md); `runtime/live.json`,`ready.json` | — |
| Proven connectivity | MS §70.5; AFR-131 | `select 1`, cache round-trip, queue dispatch+process | 29, 08 | health checks; `RuntimeSmokeJob` | `DatabaseConnectivityTest`,`CacheConnectivityTest`,`QueueSmokeJobTest`; `verify-runtime.sh` | IMPLEMENTED (FOUNDATION) | `docs/evidence/step-5/runtime/` | — |
| Queue/scheduler foundation | MS §70.5; AFR-132 | Foundation-only; retry no dup; failed-job path | 29, 05, 02 | `routes/console.php` heartbeat; smoke job | `SchedulerTest`; `verify-runtime.sh` | IMPLEMENTED (FOUNDATION) | ADR [0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md) | — |
| Security baseline | MS §70.5; AFR-131 | Security headers, trust-none proxy, prod-safe errors | 29, 04 | `SecurityHeaders` middleware; `bootstrap/app.php` | `SecurityHeadersTest` | IMPLEMENTED (FOUNDATION) | `app/Http/Middleware/SecurityHeaders.php` | — |
| Real runtime CI gate | MS §70.6; AFR-130 | Real PG+Redis job, required on ready PRs, no fake gate | 29, 28 | `.github/workflows/pr-ci.yml` | `validate-ci-topology.sh`, `test-required-gate.sh` | IMPLEMENTED (FOUNDATION) | ADR [0050](../decisions/adr/0050-backend-runtime-ci-under-cicd-ctrl-1.md) | — |
| Runtime-evidence-before-claims | MS §70.7; AFR-133 | Clean-checkout verify on merged SHA before GO tag | 29, 13, 27 | `verify-runtime.sh` | clean-checkout run; release evidence | IMPLEMENTED (FOUNDATION) | `docs/evidence/step-5/` | — |

## Step 6 SaaS core foundation (AFR-134..154; rule 30; ADRs 0011, 0012, 0013, 0015, 0029, 0051–0053)

SaaS core substrate — identity, tenant/branch context, membership, tenant-scoped RBAC, audit, and
cache/queue/storage/log isolation — placed as platform-core in top-level `app/` namespaces, not `app/Modules/`
(ADR 0052). Consolidated SPRINT-SF-01..SF-04 (EPIC-SF-04..09) delivered under one planned Step 6 GO tag (ADR 0051).
Status is `IN PROGRESS (FOUNDATION)` / `PLANNED`: code and test paths are defined; verification, merge, and the GO
tag are **not yet done** — evidence is forthcoming under `docs/evidence/step-6/`.

| Foundation | Source | Decision | Rule | Runtime enforcement | Test/CI enforcement | Status | Evidence | Gap |
|-----------|--------|----------|------|---------------------|---------------------|--------|----------|-----|
| Secure authentication foundation | MS §34, §43; AFR-134 | Fortify web session; Sanctum installed foundation-ready (not wired); no user enumeration | [30](../../.claude/rules/30-saas-core-foundation.md), 04 | Fortify config; login throttle; `app/Http/Middleware/*` | `tests/Feature/Auth/AuthenticationTest.php`; `backend-runtime-ci` | IN PROGRESS (FOUNDATION) | ADR [0013](../decisions/adr/0013-authentication-and-authorization.md), [0053](../decisions/adr/0053-tenant-membership-and-context-model.md); `docs/evidence/step-6/` (forthcoming) | Step 6 verification pending |
| Registration disabled | MS §34; AFR-135 | Provisioning + invitation only; no self-service signup | 30 | Fortify registration disabled; `app/Actions/*` | `tests/Feature/Auth/RegistrationDisabledTest.php` | IN PROGRESS (FOUNDATION) | ADR [0053](../decisions/adr/0053-tenant-membership-and-context-model.md) | Step 6 verification pending |
| Global user identity | MS §17; AFR-136 | Users global, no `tenant_id`/tenant role; access via active membership | 30, 03 | `app/Models/User.php`; `app/Tenancy/*` | `tests/Feature/Tenancy/MembershipAccessTest.php` | IN PROGRESS (FOUNDATION) | ADR [0053](../decisions/adr/0053-tenant-membership-and-context-model.md), [0052](../decisions/adr/0052-saas-core-platform-placement.md) | Step 6 verification pending |
| Tenant lifecycle | MS §36; AFR-137 | active/suspended/deletion_pending; no hard delete | 30, 07 | `app/Models/Tenant.php`; `app/Actions/*` | `tests/Feature/Tenancy/TenantLifecycleTest.php` | IN PROGRESS (FOUNDATION) | ADR [0053](../decisions/adr/0053-tenant-membership-and-context-model.md), 0029 | Step 6 verification pending |
| Tenant settings (typed) | MS §36; AFR-138 | Typed, tenant-scoped `tenant_settings` | 30 | `app/Models/TenantSetting.php`; `app/Support/*` | `tests/Feature/Tenancy/TenantSettingsTest.php` | IN PROGRESS (FOUNDATION) | ADR 0011, 0053 | Step 6 verification pending |
| Branch lifecycle | MS §17; AFR-139 | Branch belongs to one tenant; inactive not selectable | 30, 03 | `app/Models/Branch.php` | `tests/Feature/Tenancy/BranchScopeTest.php` | IN PROGRESS (FOUNDATION) | ADR 0011, 0012 | Step 6 verification pending |
| Membership + states + last-owner protection | MS §17; AFR-140 | Pivot invited/active/suspended/revoked, `all_branches`, `branch_access_grants`; last active owner protected | 30 | `app/Models/TenantMembership.php`; `app/Actions/*`; `app/Policies/*` | `tests/Feature/Tenancy/LastOwnerProtectionTest.php` | IN PROGRESS (FOUNDATION) | ADR [0053](../decisions/adr/0053-tenant-membership-and-context-model.md) | Step 6 verification pending |
| Invitation (hashed one-time token) | MS §43; AFR-141 | Hashed single-use token, expiry, tenant/branch+role scope, race-safe; ULID public keys | 30, 04 | `app/Models/TenantInvitation.php`; `app/Actions/*` | `tests/Feature/Auth/InvitationAcceptanceTest.php` | IN PROGRESS (FOUNDATION) | ADR 0053, 0013 | Step 6 verification pending |
| Tenant context (fail-closed) | MS §37; AFR-142 | Immutable, request/job-scoped, no silent fallback, cleared between requests/jobs | 30, 03 | `app/Tenancy/TenantContext.php`; `app/Http/Middleware/*` | `tests/Feature/Tenancy/TenantContextTest.php` | IN PROGRESS (FOUNDATION) | ADR [0012](../decisions/adr/0012-tenant-and-branch-context-propagation.md), 0053 | Step 6 verification pending |
| Branch context | MS §17; AFR-143 | Branch belongs to current tenant; restricted user cannot cross branches | 30, 03 | `app/Tenancy/*`; `app/Http/Middleware/*` | `tests/Feature/Tenancy/BranchAccessTest.php` | IN PROGRESS (FOUNDATION) | ADR 0012, 0015 | Step 6 verification pending |
| Tenant-scoped RBAC (Spatie teams) | MS §34; AFR-144 | `teams = true` on `tenant_id`; platform vs tenant roles separate | 30 | Spatie permission config; `app/Tenancy/*` | `tests/Feature/Tenancy/RbacScopeTest.php` | IN PROGRESS (FOUNDATION) | ADR [0013](../decisions/adr/0013-authentication-and-authorization.md) | Step 6 verification pending |
| Authorization defense-in-depth | MS §43; AFR-145 | Policies + service/action layer; UI hiding insufficient; no self-escalation | 30 | `app/Policies/*`; `app/Actions/*` | `tests/Feature/Auth/AuthorizationTest.php` | IN PROGRESS (FOUNDATION) | ADR 0013 | Step 6 verification pending |
| Database isolation (tenant-leading keys) | MS §37; AFR-146 | `tenant_id` (+`branch_id`) on every tenant-owned record; no cross-tenant relations; IDOR = release blocker | 30, 03 | `app/Tenancy/TenantScope.php`; `BelongsToTenant` trait | `tests/Feature/Tenancy/CrossTenantIsolationTest.php` | IN PROGRESS (FOUNDATION) | ADR 0011, 0012 | Step 6 verification pending |
| Cache isolation | MS §37; AFR-147 | Tenant-namespaced keys; no collision; no broad flush as app behavior | 30 | `app/Support/*` cache-key helper | `tests/Feature/Tenancy/CacheIsolationTest.php` | IN PROGRESS (FOUNDATION) | ADR [0015](../decisions/adr/0015-queue-cache-storage-search-export-analytics-isolation.md) | Step 6 verification pending |
| Queue context propagation | MS §35; AFR-148 | Validated tenant envelope; cleared per job; retry cannot switch tenant or duplicate | 30, 03 | `app/Support/*` queue envelope; job middleware | `tests/Feature/Tenancy/QueueContextTest.php` | IN PROGRESS (FOUNDATION) | ADR 0012, 0015 | Step 6 verification pending |
| Storage isolation | MS §37; AFR-149 | `tenants/{id}/branches/{id}/...` prefix; no traversal; private default | 30, 04 | `app/Support/*` storage-path helper | `tests/Feature/Tenancy/StorageIsolationTest.php` | IN PROGRESS (FOUNDATION) | ADR 0015 | Step 6 verification pending |
| Tenant-aware logging | MS §51; AFR-150 | Structured tenant context; no PII/secrets; no cross-tenant worker/request leak | 30, 04 | `app/Support/*` log-context helper | `tests/Feature/Tenancy/LogContextTest.php` | IN PROGRESS (FOUNDATION) | ADR 0015, 0029 | Step 6 verification pending |
| Audit append-only | MS §37; AFR-151 | Security/admin actions audited; sanitized metadata; no `updated_at`; update/delete blocked; not deletable | 30, 07 | `app/Audit/*`; `app/Models/AuditLog.php` | `tests/Feature/Tenancy/AuditAppendOnlyTest.php` | IN PROGRESS (FOUNDATION) | ADR [0029](../decisions/adr/0029-data-classification-retention-export-deletion.md), 0053 | Step 6 verification pending |
| Suspension/revocation enforcement | MS §43; AFR-152 | Suspended tenant/user/membership fail closed; revoked permission cannot survive unsafe stale cache | 30, 04 | `app/Http/Middleware/*`; `app/Tenancy/*` | `tests/Feature/Tenancy/SuspensionEnforcementTest.php` | IN PROGRESS (FOUNDATION) | ADR 0053, 0013 | Step 6 verification pending |
| Cross-tenant security test matrix | MS §50; AFR-153 | read/write/list/export/cache/queue/storage/log across tenants; breach = release blocker | 30, 03 | Enforced by test suite | `tests/Feature/Tenancy/CrossTenantSecurityMatrixTest.php`; `backend-runtime-ci` | IN PROGRESS (FOUNDATION) | ADR 0011, [0051](../decisions/adr/0051-step-6-consolidated-saas-core-foundation.md) | Step 6 verification pending |
| Clean-checkout SaaS-core verification | MS §54; AFR-154 | Clean-checkout verify on merged SHA before the consolidated Step 6 GO tag | 30, 13, 29 | `scripts/runtime/verify-runtime.sh` (extended) | `backend-runtime-ci`; clean-checkout run; release evidence | PLANNED | ADR [0051](../decisions/adr/0051-step-6-consolidated-saas-core-foundation.md), 0052 | Step 6 GO tag not yet cut; verification pending |

Step 6 consolidates the coupled core sprints per ADR 0051; SPRINT-SF-05..SF-08 remain independently gated. No
Step 6 foundation is claimed verified, merged, tagged, or runtime-verified — those are `PLANNED`/pending. Every
Step 6 AFR maps to an ADR, Claude rule 30, a SaaS-core fitness check (SC-*), and forthcoming evidence. Business
feature modules under `app/Modules/`, deployment, pilot, and production remain **NOT STARTED**.

## SPRINT-SF-05 notification, subscription & platform-admin (AFR-155..170; rule 31; ADRs 0054–0056)

Three platform-core skeletons on the Step 6 SaaS core — notification delivery, subscription/entitlement, and the
platform operator plane — placed in top-level `app/` namespaces, not `app/Modules/` (ADR 0052); business modules
remain NOT STARTED. Status is `IN PROGRESS (FOUNDATION)` / `PLANNED`: code and test paths exist and pass locally;
clean-checkout verification, merge, and the SPRINT-SF-05 GO tag are **not yet done** — evidence is forthcoming under
`docs/evidence/sprint-sf-05/`.

| Foundation | Source | Decision | Rule | Runtime enforcement | Test/CI enforcement | Status | Evidence | Gap |
|-----------|--------|----------|------|---------------------|---------------------|--------|----------|-----|
| Tenant-safe notification dispatch | MS §72, §40; AFR-155, 156 | One dispatcher; membership-verified; per-(recipient,channel) dedup; in-app + email only | [31](../../.claude/rules/31-notification-subscription-platform-admin-foundation.md), 03 | `app/Services/Notifications/*`; `app/Jobs/Notifications/*`; `app/Mail/FoundationNotificationMail.php` | `tests/Feature/Notifications/*`; `tests/Feature/Security/Sf05CrossTenantMatrixTest.php` | IN PROGRESS (FOUNDATION) | ADR [0054](../decisions/adr/0054-notification-delivery-and-state.md); `docs/evidence/sprint-sf-05/` (forthcoming) | SF-05 verification pending |
| Truthful delivery state + idempotency | MS §72, §53; AFR-156, 157 | `pending..suppressed`; `queued` ≠ `sent`; bounded idempotent retry; sanitized failure code | 31, 10 | `app/Models/NotificationDelivery.php` state machine | `tests/Feature/Notifications/DeliveryStateTest.php` | IN PROGRESS (FOUNDATION) | ADR [0054](../decisions/adr/0054-notification-delivery-and-state.md) | SF-05 verification pending |
| Preferences, quiet hours, critical bypass, inbox | MS §72, §40; AFR-158, 159 | Timezone-aware quiet hours; critical bypass; inbox ownership re-check | 31, 04 | `app/Models/NotificationPreference.php`; in-app inbox controller | `tests/Feature/Notifications/PreferenceQuietHoursTest.php`, `InboxOwnershipTest.php` | IN PROGRESS (FOUNDATION) | ADR [0054](../decisions/adr/0054-notification-delivery-and-state.md) | SF-05 verification pending |
| Versioned plan catalog | MS §72, §45; AFR-160 | `(code, version)` catalog; `draft/active/retired`; retired not newly assigned, existing refs valid | 31 | `app/Models/Plan.php`, `PlanEntitlement.php` | `tests/Feature/Subscriptions/PlanCatalogTest.php` | IN PROGRESS (FOUNDATION) | ADR [0055](../decisions/adr/0055-subscription-and-entitlement-model.md) | SF-05 verification pending |
| Typed entitlements + fail-closed resolver | MS §72, §45; AFR-161 | Typed allowlist; unknown/missing/expired fail closed; single authoritative resolver | 31, 04 | `app/Subscriptions/EntitlementKeys.php`, `EntitlementResolver.php` | `tests/Feature/Subscriptions/EntitlementResolverTest.php` | IN PROGRESS (FOUNDATION) | ADR [0055](../decisions/adr/0055-subscription-and-entitlement-model.md) | SF-05 verification pending |
| Subscription state machine + usage idempotency | MS §72, §46; AFR-162, 163 | Guarded transitions; tenant-scoped idempotent metering; timezone-aware periods | 31, 03 | `app/Models/TenantSubscription.php`; `UsageRecord.php` | `tests/Feature/Subscriptions/SubscriptionStateTest.php`, `UsageMeteringTest.php` | IN PROGRESS (FOUNDATION) | ADR [0055](../decisions/adr/0055-subscription-and-entitlement-model.md) | SF-05 verification pending |
| Idempotent reconcile; commercial ≠ payment; security precedence | MS §72, §46; AFR-164, 165 | Rerun-safe reconcile (≤1 transition); no paid claim without provider; security suspension precedence | 31, 04 | `app/Console/Commands/*Reconcile*`; resolver precedence | `tests/Feature/Console/Sf05CommandsTest.php`; `tests/Feature/Subscriptions/SecurityPrecedenceTest.php` | IN PROGRESS (FOUNDATION) | ADR [0055](../decisions/adr/0055-subscription-and-entitlement-model.md) | SF-05 verification pending |
| Platform / tenant plane separation | MS §72, §17, §18; AFR-166 | Separate `/platform-admin/*` plane; platform vs tenant roles distinct; no `Gate::before` bypass | 31, 03 | `app/Platform/*`; `app/Http/Controllers/Platform/*`; `app/Policies/Platform/*` | `tests/Feature/Platform/PlaneSeparationTest.php`; `tests/Architecture/Sf05BoundariesTest.php` | IN PROGRESS (FOUNDATION) | ADR [0056](../decisions/adr/0056-platform-admin-trust-boundary.md) | SF-05 verification pending |
| Least privilege; no self-escalation; last-Super-Admin | MS §72, §43; AFR-167 | Per-permission authorization + audit; self-escalation blocked; last Super Admin protected | 31 | `app/Policies/Platform/*`; `app/Platform/*` | `tests/Feature/Platform/LeastPrivilegeTest.php`, `LastSuperAdminTest.php` | IN PROGRESS (FOUNDATION) | ADR [0056](../decisions/adr/0056-platform-admin-trust-boundary.md) | SF-05 verification pending |
| Secure provisioning; reason-required status; no impersonation | MS §72, §43; AFR-168 | Reset-link onboarding (no logged password); reason-required audited tenant status + owner notify; impersonation prohibited | 31, 04 | `app/Console/Commands/*PlatformAdminProvision*`; `app/Platform/*` | `tests/Feature/Console/Sf05CommandsTest.php`; `tests/Feature/Platform/TenantAdminActionsTest.php` | IN PROGRESS (FOUNDATION) | ADR [0056](../decisions/adr/0056-platform-admin-trust-boundary.md) | SF-05 verification pending |
| Append-only events/notes + sanitized audit | MS §72, §37; AFR-169 | Append-only subscription events + support notes (no `updated_at`, update/delete blocked); sanitized, platform/tenant-distinguished audit | 31, 07 | `app/Models/{SubscriptionEvent,PlatformSupportNote}.php`; `app/Audit/*` | `tests/Feature/Audit/Sf05AuditTest.php` | IN PROGRESS (FOUNDATION) | ADR [0054](../decisions/adr/0054-notification-delivery-and-state.md), 0055, 0056 | SF-05 verification pending |
| Clean-checkout SF-05 verification | MS §72, §54; AFR-170 | Clean-checkout verify on merged SHA before the SPRINT-SF-05 GO tag | 31, 13, 29 | `scripts/runtime/verify-sf-05.sh` | `backend-runtime-ci`; clean-checkout run; release evidence | PLANNED | ADR [0051](../decisions/adr/0051-step-6-consolidated-saas-core-foundation.md), 0054 | SF-05 GO tag not yet cut; verification pending |

Every SPRINT-SF-05 mandatory foundation maps to Claude rule 31, a runtime code path, a test/CI enforcement path, and
ADR evidence — no orphan. No SPRINT-SF-05 foundation is claimed verified, merged, tagged, or runtime-verified; those
are `PLANNED`/pending. Payment/invoicing/AI/Google integrations, business feature modules under `app/Modules/`,
deployment, pilot, and production remain **NOT STARTED**.

## Permanent product foundations (rules established; product implementation scheduled later)

These permanent decisions are governed by rules today; their **application** implementation is scheduled in the
SaaS Foundation and later steps. Recorded here so no permanent decision is orphaned.

| Foundation | Rule | Status | Note |
|-----------|------|--------|------|
| Multi-tenant / branch isolation on every surface | [03](../../.claude/rules/03-multi-tenant-and-branch-isolation.md), 20, 30 | IN PROGRESS (FOUNDATION) | Isolation substrate (context/scope/cache/queue/storage/log) implemented in Step 6 SaaS core (AFR-134..154, rule 30); business-data surfaces layer on it later |
| Security, privacy, PII minimization, encrypted tokens | [04](../../.claude/rules/04-security-privacy-and-secrets.md) | RULE ESTABLISHED — LATER STEP (baseline started) | Step 5 adds secret hygiene + headers; full controls later |
| Human approval for public/high-risk actions | [05](../../.claude/rules/05-ai-governance-and-human-approval.md), 18 | RULE ESTABLISHED — LATER STEP | No public actions exist yet |
| Google Review anti-gating | [06](../../.claude/rules/06-google-review-policy.md), 18 | RULE ESTABLISHED — LATER STEP | No review flows exist yet |
| Supervisor + specialist agent architecture | [05](../../.claude/rules/05-ai-governance-and-human-approval.md), 20 | RULE ESTABLISHED — LATER STEP | No AI runtime yet |
| Data governance / audit / metering | [07](../../.claude/rules/07-data-governance-and-audit.md), 30 | IN PROGRESS (FOUNDATION) | Append-only audit foundation implemented in Step 6 (AFR-151); metering and business-data governance remain LATER STEP |
| Truthful system states | [10](../../.claude/rules/10-ui-ux-and-truthful-states.md), 27 | IMPLEMENTED (FOUNDATION) | Health probes + bootstrap surface are truthful |
| CI/CD safe runtime control | [28](../../.claude/rules/28-safe-ci-runtime-control.md) | CONFIGURED + extended | Step 5 adds the real backend runtime gate |
| Documentation living source / versioning | [12](../../.claude/rules/12-documentation-living-source-versioning.md) | ONGOING | Master Source v2.6.0 |

No orphan permanent decision. Gaps: none for Step 5 scope; Step 6 SaaS-core foundations are `IN PROGRESS
(FOUNDATION)` / `PLANNED` with verification, merge, and the GO tag pending (evidence forthcoming under
`docs/evidence/step-6/`); business feature modules under `app/Modules/`, deployment, pilot, and production remain
`NOT STARTED` by design (see [CLAUDE.md](../../CLAUDE.md) §5, rule 27).
