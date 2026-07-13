# SaaS Foundation Epic Catalog (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102 (implementation-planning foundation rules), building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. Estimates are planning estimate
  classes only, never commitments. No epic has been executed. Architecture (ADRs 0009–0032, the Application
  Architecture Baseline, the Application Foundation Rules) is referenced by name and number in prose only.

---

## How to read this catalog

This catalog defines all sixteen SaaS Foundation epics: EPIC-SF-01, EPIC-SF-02, EPIC-SF-03, EPIC-SF-04,
EPIC-SF-05, EPIC-SF-06, EPIC-SF-07, EPIC-SF-08, EPIC-SF-09, EPIC-SF-10, EPIC-SF-11, EPIC-SF-12, EPIC-SF-13,
EPIC-SF-14, EPIC-SF-15, and EPIC-SF-16. Each epic uses the same field set. Estimate class is a T-shirt size
(S / M / L / XL) as a planning signal only.

> **Configuration & secret foundation** is **not** a standalone epic. It is delivered as **cross-cutting scope
> within SPRINT-SF-01** (before authentication), governed by ADR 0037 and AFR-090/AFR-091; the implementation
> sequence lists it as an ordered step, but the numbered epics remain EPIC-SF-01..16.

Sprint assignment and dependency detail are in
[SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md) and
[SAAS_FOUNDATION_DEPENDENCY_MAP.md](SAAS_FOUNDATION_DEPENDENCY_MAP.md). Per-epic evidence requirements are
elaborated in [SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md](SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md). The
shared completion contract is [SAAS_FOUNDATION_DEFINITION_OF_DONE.md](SAAS_FOUNDATION_DEFINITION_OF_DONE.md).

---

## EPIC-SF-01 — Repository Runtime Bootstrap

- **Objective:** Establish a runnable Laravel 12 (PHP 8.3+) application skeleton so subsequent foundation work
  has a boot target.
- **Scope:** Framework skeleton, autoloading, application bootstrap, base configuration files, health of
  `php artisan` command surface, modular-monolith directory layout for the 17 module boundaries (empty shells).
- **Out-of-scope:** Any business module logic, migrations for domain tables, authentication, tenancy.
- **Dependencies:** None (root of the sequence).
- **Architecture references:** Laravel 12 modular monolith per ADR 0009; module layout per ADR 0010; stack per
  the Application Architecture Baseline (Master Source §34).
- **AFR references:** AFR-099 (implementation planning), AFR-001..005 (module boundaries).
- **Database impact:** None beyond framework scaffolding; no domain tables.
- **Security impact:** Secure defaults (debug off outside local, no secrets committed); establishes secret
  referencing via environment variables.
- **Privacy impact:** None (no personal data).
- **Tenant impact:** None yet; establishes the module layout that will later carry tenant ownership.
- **Operational impact:** Defines the runnable artifact all later operations depend on.
- **Acceptance criteria:** App boots; `php artisan` runs; module directory boundaries exist as shells;
  no secret is committed; base config resolves from environment.
- **Test plan:** Smoke boot test; static lint; secret scan.
- **Evidence:** Boot log, lint output, secret-scan output under `docs/evidence/validation/`.
- **Rollback:** Revert the bootstrap branch; the repository returns to documentation-only state.
- **Estimate class:** M · **Risk:** Low · **Owner role:** Laravel SaaS Architect.
- **Definition of Done:** App boots reproducibly with evidence; no secrets; truthful status recorded.

## EPIC-SF-02 — Local Development Environment

- **Objective:** Provide a reproducible local development runtime so any contributor can boot the app
  identically.
- **Scope:** Containerized/local stack definition (PHP 8.3+, PostgreSQL, Redis), environment templates with
  placeholder-only values, developer bootstrap docs, deterministic dependency install.
- **Out-of-scope:** Production infrastructure; CI runners (EPIC-SF-03); cloud provisioning.
- **Dependencies:** EPIC-SF-01.
- **Architecture references:** Stack components per the Application Architecture Baseline (Master Source §34);
  Redis for cache/queue.
- **AFR references:** AFR-099, AFR-100 (environment reproducibility).
- **Database impact:** Local PostgreSQL instance definition only; no schema.
- **Security impact:** `.env` templates carry placeholders only; real secrets never committed; secret manager
  referencing documented.
- **Privacy impact:** None.
- **Tenant impact:** None yet.
- **Operational impact:** Foundation for reproducible local verification.
- **Acceptance criteria:** A clean checkout boots locally following documented steps; services (DB, Redis)
  reachable; secret scan clean.
- **Test plan:** Fresh-clone boot verification; environment matrix check.
- **Evidence:** Boot transcript, environment matrix confirmation under `docs/evidence/validation/`.
- **Rollback:** Revert local-env branch; contributors fall back to prior instructions (none) — low blast radius.
- **Estimate class:** M · **Risk:** Low · **Owner role:** DevEx / Laravel SaaS Architect.
- **Definition of Done:** Reproducible local boot proven with evidence; no secrets committed.

## EPIC-SF-03 — CI Runtime Foundation

- **Objective:** Run the application build/test/lint pipeline in CI so every change is validated with evidence.
- **Scope:** CI workflow for install, static analysis, unit/feature test execution, secret scanning, and
  artifact/evidence capture; branch-protection-compatible required checks.
- **Out-of-scope:** Deployment pipelines (EPIC-SF-15); performance/security suites beyond smoke (later epics).
- **Dependencies:** EPIC-SF-01, EPIC-SF-02.
- **Architecture references:** CI gate discipline per Rule 13; documentation-as-code gates per Rule 09.
- **AFR references:** AFR-100 (CI runtime), AFR-101 (evidence capture).
- **Database impact:** Ephemeral CI PostgreSQL service; no persistent schema.
- **Security impact:** Secret scanning enforced in CI; push protection not disabled.
- **Privacy impact:** No real data in CI; fixtures are synthetic.
- **Tenant impact:** None yet.
- **Operational impact:** Establishes the green-CI precondition for all merges.
- **Acceptance criteria:** CI runs on PR and main; required checks visible; secret scan runs; evidence archived.
- **Test plan:** Pipeline dry-run on a trivial change; failing-case verification (a deliberately broken test
  fails CI).
- **Evidence:** CI run logs and status under `docs/evidence/ci/`.
- **Rollback:** Disable the new workflow file via revert; prior documentation CI remains.
- **Estimate class:** M · **Risk:** Medium · **Owner role:** Release/CI Owner.
- **Definition of Done:** CI green on a real change with archived evidence; no gate weakened.

## EPIC-SF-04 — Authentication and Account Security

- **Objective:** Provide secure user authentication and account security as the identity layer beneath tenancy.
- **Scope:** Fortify/Sanctum-based login, session/token issuance, password policy, MFA capability, OAuth state
  validation primitives, rate limiting on auth endpoints, secure account recovery.
- **Out-of-scope:** Tenant/branch binding (EPIC-SF-05); RBAC (EPIC-SF-06); external OAuth provider integration
  (Google) beyond state-validation primitives.
- **Dependencies:** EPIC-SF-01, EPIC-SF-02, EPIC-SF-03, and the configuration/secret foundation.
- **Architecture references:** Auth stack (Fortify/Sanctum) per the Application Architecture Baseline
  (Master Source §34); security controls per Rule 04 (Master Source §43).
- **AFR references:** AFR-041 boundary awareness (AI not involved in auth), AFR-021..030 (identity/security).
- **Database impact:** Users, credentials, MFA, and auth-related tables (no tenant business data).
- **Security impact:** High — tokens encrypted; refresh tokens never plaintext; OAuth state validated; MFA;
  rate limiting; CSRF protection.
- **Privacy impact:** Minimal PII (account identifiers); PII minimized and classified.
- **Tenant impact:** Identity precedes tenant context; a user is later associated with tenant memberships.
- **Operational impact:** Auth availability underpins all console access.
- **Acceptance criteria:** Users authenticate; MFA works; tokens encrypted; auth rate limiting enforced; OAuth
  state validated; account recovery secure.
- **Test plan:** Auth functional tests; security tests (credential stuffing/rate-limit, CSRF, token handling,
  OAuth state); negative-path tests.
- **Evidence:** Auth test results and security-test transcripts under `docs/evidence/validation/`.
- **Rollback:** Feature-flag/disable new auth routes; revert branch; no tenant data affected.
- **Estimate class:** L · **Risk:** High · **Owner role:** Security-Privacy Owner + Architect.
- **Definition of Done:** Auth and account-security controls proven by functional + security tests with evidence.

## EPIC-SF-05 — Tenant and Branch Foundation

- **Objective:** Establish the tenant and branch context primitive so every business record can carry and
  enforce ownership.
- **Scope:** Tenant and branch entities, tenant/branch context resolution and propagation, mandatory
  `tenant_id` (and `branch_id` where relevant) ownership convention, base scoping enforcement at the query
  boundary.
- **Out-of-scope:** RBAC (EPIC-SF-06); queue/storage propagation (EPIC-SF-08/09) beyond defining the contract.
- **Dependencies:** EPIC-SF-04 (identity) and the runtime/CI foundation.
- **Architecture references:** Shared-DB/shared-schema/row-level tenancy per ADR 0011, 0012, 0015;
  Tenant Isolation Control Matrix.
- **AFR references:** AFR-006..020 (tenancy), AFR-099.
- **Database impact:** Tenant and branch tables; ownership columns and constraints convention established.
- **Security impact:** High — cross-tenant isolation begins here; scoping enforced at the data boundary.
- **Privacy impact:** Tenant/branch metadata only; no sensitive customer data yet.
- **Tenant impact:** Foundational — defines isolation for all later surfaces.
- **Operational impact:** All later features depend on this context primitive.
- **Acceptance criteria:** Records carry tenant/branch ownership; a query without tenant scope is prevented or
  fails safe; branch-scoped access returns only the branch's data.
- **Test plan:** Multi-tenant isolation tests (cross-tenant read/write denied); branch-scoping tests; context
  propagation tests.
- **Evidence:** Isolation test suite results under `docs/evidence/validation/`.
- **Rollback:** Revert tenancy branch; no business features exist yet to strand.
- **Estimate class:** L · **Risk:** High · **Owner role:** Architect + Security-Privacy Owner.
- **Definition of Done:** Tenant/branch context enforced and proven by cross-tenant isolation tests with evidence.

## EPIC-SF-06 — Roles, Permissions, and Scope

- **Objective:** Enforce role-based access control with branch scoping before any privileged surface exists.
- **Scope:** Spatie-Permission-based roles/permissions, the minimum pilot role set (Business Owner, Corporate
  Admin, Branch Manager, Recovery Assignee, Reputation Approver), branch-scoped authorization, policy gates.
- **Out-of-scope:** The privileged console UI (EPIC-SF-12); approval workflow content (later business epics).
- **Dependencies:** EPIC-SF-05.
- **Architecture references:** RBAC + branch scoping per Rule 04 and the Application Architecture Baseline;
  role coverage per Rule 16.
- **AFR references:** AFR-021..030 (authorization), AFR-006..020 (branch scope).
- **Database impact:** Roles, permissions, role-user, and permission-scope tables.
- **Security impact:** High — privilege escalation and broken-access-control defenses established here.
- **Privacy impact:** Access control limits exposure of tenant data.
- **Tenant impact:** Roles are tenant-scoped; branch roles see only their branch.
- **Operational impact:** Gates every privileged action for the rest of the build.
- **Acceptance criteria:** Roles enforce permissions; branch-scoped roles cannot read other branches; privilege
  escalation attempts denied.
- **Test plan:** Authorization tests; privilege-escalation and IDOR tests; branch-scope leakage tests.
- **Evidence:** RBAC test results under `docs/evidence/validation/`.
- **Rollback:** Revert RBAC branch; access defaults to deny (fail-safe).
- **Estimate class:** L · **Risk:** High · **Owner role:** Security-Privacy Owner + Architect.
- **Definition of Done:** RBAC and branch scoping proven by authorization + escalation tests with evidence.

## EPIC-SF-07 — Audit and Security Events

- **Objective:** Provide an immutable audit and security-event trail before any sensitive mutation ships.
- **Scope:** Audit-log and security-event tables, write path for privileged/sensitive actions, immutability
  (append-only), tenant scoping of audit visibility.
- **Out-of-scope:** Full observability/tracing (EPIC-SF-13); alerting rules (EPIC-SF-13).
- **Dependencies:** EPIC-SF-05, EPIC-SF-06.
- **Architecture references:** Audit immutability per Rule 07 (Master Source §36, §37).
- **AFR references:** AFR-031..040 (audit), AFR-006..020 (tenant scoping).
- **Database impact:** `audit_logs`, `security_events` tables (append-only).
- **Security impact:** High — audit is a security control; must be non-deletable.
- **Privacy impact:** Audit records are minimized; no sensitive medical/financial payloads stored in logs.
- **Tenant impact:** Audit visibility tenant-scoped; no cross-tenant audit leakage.
- **Operational impact:** Precondition for shipping any sensitive mutation.
- **Acceptance criteria:** Sensitive actions produce audit records; records cannot be edited/deleted; audit is
  tenant-scoped; security events recorded.
- **Test plan:** Audit-coverage tests; immutability tests (delete/update denied); tenant-scope tests.
- **Evidence:** Audit test transcripts under `docs/evidence/validation/`.
- **Rollback:** Revert audit branch; blocks progression of sensitive-mutation epics (by design).
- **Estimate class:** M · **Risk:** Medium · **Owner role:** Security-Privacy Owner.
- **Definition of Done:** Immutable, tenant-scoped audit proven by coverage + immutability tests with evidence.

## EPIC-SF-08 — Queue, Redis, and Scheduler

- **Objective:** Provide tenant-context-carrying asynchronous processing before any external async work.
- **Scope:** Redis-backed queue, worker configuration, scheduler, mandatory tenant/branch context propagation
  into jobs and events, idempotency and retry-safety primitives, dead-letter handling.
- **Out-of-scope:** Actual external integrations (Google/WhatsApp) — later business epics; notification content
  (EPIC-SF-10).
- **Dependencies:** EPIC-SF-05, EPIC-SF-07.
- **Architecture references:** Queue with tenant context per Rule 08; outbox/idempotency per ADR 0016, 0017.
- **AFR references:** AFR-031..036 (outbox/idempotency), AFR-006..020 (context propagation).
- **Database impact:** Job/outbox/dead-letter support tables.
- **Security impact:** Jobs must not leak cross-tenant data; context integrity enforced.
- **Privacy impact:** Job payloads minimized; no sensitive data beyond necessity.
- **Tenant impact:** High — establishes queue isolation for every later async workflow.
- **Operational impact:** Scheduler + workers become operational dependencies.
- **Acceptance criteria:** Jobs carry tenant context; retries do not duplicate side effects; dead-letter works;
  scheduler runs tenant-safe tasks.
- **Test plan:** Queue tenant-context tests; idempotent-retry tests; dead-letter tests.
- **Evidence:** Queue isolation and idempotency test results under `docs/evidence/validation/`.
- **Rollback:** Disable workers/scheduler; revert branch; synchronous fallback documented.
- **Estimate class:** L · **Risk:** High · **Owner role:** Architect.
- **Definition of Done:** Tenant-scoped, idempotent queue proven by context + retry tests with evidence.

## EPIC-SF-09 — Tenant-Scoped Storage

- **Objective:** Provide tenant-isolated file storage before any tenant upload path exists.
- **Scope:** S3-compatible storage abstraction, tenant/branch-scoped path prefixes, access control on read/write,
  secure upload validation, signed-URL discipline.
- **Out-of-scope:** Business upload features (survey attachments, etc.) — later epics.
- **Dependencies:** EPIC-SF-05, EPIC-SF-06.
- **Architecture references:** S3-compatible storage per the Application Architecture Baseline (Master Source §34);
  tenant scoping per Rule 03.
- **AFR references:** AFR-006..020 (storage isolation), AFR-051..060 (integration boundaries).
- **Database impact:** File-metadata table (tenant-scoped).
- **Security impact:** High — secure upload, no path traversal, no cross-tenant object access.
- **Privacy impact:** Uploaded objects may carry PII; access strictly scoped and minimized.
- **Tenant impact:** High — storage isolation per tenant/branch.
- **Operational impact:** Storage becomes an operational dependency with backup implications.
- **Acceptance criteria:** Objects stored under tenant/branch prefixes; cross-tenant object access denied;
  upload validation rejects unsafe files; signed URLs scoped and expiring.
- **Test plan:** Storage isolation tests; malicious-upload tests; signed-URL scope tests.
- **Evidence:** Storage isolation and upload-validation results under `docs/evidence/validation/`.
- **Rollback:** Disable upload routes; revert branch; no business upload exists yet.
- **Estimate class:** M · **Risk:** High · **Owner role:** Security-Privacy Owner + Architect.
- **Definition of Done:** Tenant-scoped storage proven by isolation + upload-validation tests with evidence.

## EPIC-SF-10 — Notification Foundation

- **Objective:** Provide a tenant-safe notification dispatch skeleton usable without AI.
- **Scope:** Notification abstraction (in-app + email channel skeleton), queue-backed delivery, tenant scoping,
  delivery-state truthful vocabulary, opt-out honoring hooks.
- **Out-of-scope:** WhatsApp invitation flow content, Google review notifications, AI-generated content — later
  business epics.
- **Dependencies:** EPIC-SF-08 (queue), EPIC-SF-05 (tenant context).
- **Architecture references:** Notification channels and delivery observability per Rule 11; truthful states
  per Rule 10.
- **AFR references:** AFR-031..036 (async delivery), AFR-061..068 (operations).
- **Database impact:** Notification and delivery-log tables (tenant-scoped).
- **Security impact:** No sensitive data in notification metadata/logs; channel credentials referenced securely.
- **Privacy impact:** Recipient PII minimized; opt-out respected.
- **Tenant impact:** Notifications tenant-scoped; no cross-tenant delivery.
- **Operational impact:** Delivery health becomes observable; works when AI is unavailable.
- **Acceptance criteria:** Notifications dispatch via queue; delivery states truthful; tenant-scoped; opt-out
  honored; works without AI.
- **Test plan:** Delivery tests; tenant-scope tests; truthful-state tests; AI-unavailable fallback test.
- **Evidence:** Notification delivery and state test results under `docs/evidence/validation/`.
- **Rollback:** Disable notification dispatch; revert branch.
- **Estimate class:** M · **Risk:** Medium · **Owner role:** Architect.
- **Definition of Done:** Tenant-scoped notification skeleton proven with truthful-state and fallback evidence.

## EPIC-SF-11 — Subscription and Entitlement Skeleton

- **Objective:** Provide a plan/entitlement gating skeleton without charging money.
- **Scope:** Plans, subscriptions, entitlement checks, plan-aware feature gating hooks, usage-metering
  scaffolding (idempotent, tenant-scoped, reconcilable) — no payment capture.
- **Out-of-scope:** Real billing charges, payment provider integration, overage invoicing — later epics.
- **Dependencies:** EPIC-SF-05, EPIC-SF-06, EPIC-SF-07.
- **Architecture references:** Subscription/metering per Rule 07 (Master Source §45, §46); truthful states per
  Rule 10.
- **AFR references:** AFR-006..020 (tenant scope), AFR-031..036 (idempotent metering).
- **Database impact:** Plans, subscriptions, entitlements, usage-metering tables.
- **Security impact:** Entitlement checks cannot be bypassed; metering integrity enforced.
- **Privacy impact:** No payment-card data stored in this skeleton (out of scope by design).
- **Tenant impact:** Subscriptions tenant-scoped; entitlements per tenant.
- **Operational impact:** Metering must be idempotent and reconcilable.
- **Acceptance criteria:** Entitlement gates enforce plan limits; metering idempotent and tenant-scoped; no
  charge is made; truthful subscription states.
- **Test plan:** Entitlement-gating tests; idempotent-metering tests; tenant-scope tests.
- **Evidence:** Entitlement and metering test results under `docs/evidence/validation/`.
- **Rollback:** Disable entitlement gating (default allow within pilot scope only if explicitly approved);
  revert branch.
- **Estimate class:** M · **Risk:** Medium · **Owner role:** Architect + Product.
- **Definition of Done:** Entitlement + metering skeleton proven by gating + idempotency tests with evidence.

## EPIC-SF-12 — Platform Admin Skeleton

- **Objective:** Provide a privileged platform-admin console skeleton after RBAC and audit exist.
- **Scope:** Platform-admin surface for tenant/user/plan oversight (read-first), guarded by RBAC, every action
  audited, no destructive bulk operations without explicit approval.
- **Out-of-scope:** Full admin feature set; billing operations; data-deletion execution (requires approval
  workflow, later).
- **Dependencies:** EPIC-SF-06 (RBAC), EPIC-SF-07 (audit).
- **Architecture references:** Platform admin per Master Source §36; privileged-action guards per Rule 15.
- **AFR references:** AFR-021..030 (authorization), AFR-031..040 (audit).
- **Database impact:** Admin-scoped views; no new sensitive tables.
- **Security impact:** High — most privileged surface; strict RBAC + audit + confirmation on risky actions.
- **Privacy impact:** Admin visibility into tenant metadata is minimized and audited; no sensitive customer PII
  surfaced by default.
- **Tenant impact:** Cross-tenant admin visibility is a platform-operator capability, strictly controlled and
  audited; tenant users never gain cross-tenant access.
- **Operational impact:** Enables safe operator oversight during later stages.
- **Acceptance criteria:** Admin actions require platform-admin role; every action audited; risky actions
  require confirmation; no unaudited privileged path.
- **Test plan:** Admin RBAC tests; audit-coverage tests; risky-action confirmation tests.
- **Evidence:** Admin authorization and audit results under `docs/evidence/validation/`.
- **Rollback:** Disable admin routes; revert branch.
- **Estimate class:** M · **Risk:** High · **Owner role:** Security-Privacy Owner + Architect.
- **Definition of Done:** Guarded, fully-audited admin skeleton proven with RBAC + audit evidence.

## EPIC-SF-13 — Observability and Health Checks

- **Objective:** Provide the minimum observability and health checks required before any pilot.
- **Scope:** Structured logging, health/readiness endpoints, tracing hooks (OpenTelemetry-compatible), key
  metrics (error rate, queue backlog, delivery, DB/Redis/storage health), minimum alert definitions.
- **Out-of-scope:** AI cost/tracing dashboards for agent runs (arrive with AI epics later); full SRE tooling.
- **Dependencies:** EPIC-SF-08, EPIC-SF-10.
- **Architecture references:** Observability baseline per Rule 11 (Master Source §51).
- **AFR references:** AFR-061..068 (operations/observability).
- **Database impact:** Minimal (metrics metadata); no sensitive data in logs.
- **Security impact:** Logs must not contain secrets or sensitive PII; tenant-isolation anomaly alert defined.
- **Privacy impact:** Log redaction enforced; PII kept out of logs.
- **Tenant impact:** Tenant-visible logs are tenant-scoped; cross-tenant anomalies alertable.
- **Operational impact:** Precondition for operating a pilot.
- **Acceptance criteria:** Health checks report component status; structured logs emitted; minimum alerts
  defined; no secret/PII in logs.
- **Test plan:** Health-check tests; log-redaction tests; alert-definition review.
- **Evidence:** Health-check output and log-redaction results under `docs/evidence/validation/`.
- **Rollback:** Revert observability branch; health endpoints removed.
- **Estimate class:** M · **Risk:** Medium · **Owner role:** Operations Owner + Architect.
- **Definition of Done:** Health checks, structured logging, and alerts proven with redaction evidence.

## EPIC-SF-14 — Backup and Restore Foundation

- **Objective:** Provide backups with a tested restore before any pilot.
- **Scope:** Backup routine for database and object storage, retention configuration, a documented and
  **tested** restore procedure, export/deletion scaffolding hooks.
- **Out-of-scope:** Full disaster-recovery topology; cross-region replication.
- **Dependencies:** EPIC-SF-05, EPIC-SF-09, EPIC-SF-13.
- **Architecture references:** Backup + tested restore per Rule 11 (Master Source §54 data/operational gates).
- **AFR references:** AFR-061..068 (operations).
- **Database impact:** Backup targets defined; no schema change to business tables.
- **Security impact:** Backups encrypted; never committed to the repository; access controlled.
- **Privacy impact:** Backups may contain PII; handled under encryption and retention policy.
- **Tenant impact:** Restore must preserve tenant isolation; no cross-tenant restore leakage.
- **Operational impact:** Tested restore is a hard pilot precondition.
- **Acceptance criteria:** Backups run; a restore is executed and verified in a non-production environment;
  retention configured; no backup committed to git.
- **Test plan:** Backup execution test; restore-verification test; retention check.
- **Evidence:** Restore-verification transcript under `docs/evidence/validation/` (no real customer PII).
- **Rollback:** Revert backup automation; manual backup fallback documented.
- **Estimate class:** M · **Risk:** High · **Owner role:** Operations Owner.
- **Definition of Done:** Backup with a proven, evidence-backed restore; no secrets/PII committed.

## EPIC-SF-15 — Deployment and Rollback Foundation

- **Objective:** Provide a deployment path with a proven rollback before any deployment claim.
- **Scope:** Deployment target class definition, release process, migration-safe deploy, health-gated
  cutover, and a tested rollback procedure; kill-switch wiring for risky subsystems.
- **Out-of-scope:** Production go-live; pilot cutover; scaling topology.
- **Dependencies:** EPIC-SF-03, EPIC-SF-13, EPIC-SF-14.
- **Architecture references:** Deployment/rollback discipline per Rule 11 and Rule 13; no success before
  verification per ADR 0017.
- **AFR references:** AFR-061..068 (operations), AFR-101 (evidence).
- **Database impact:** Migration-safe deploy discipline; reversible migrations preferred.
- **Security impact:** Deploy secrets referenced securely; no secret in pipeline logs.
- **Privacy impact:** None beyond secure handling.
- **Tenant impact:** Deploy/rollback must not corrupt tenant isolation.
- **Operational impact:** Establishes the release/rollback runbook.
- **Acceptance criteria:** A deploy to a non-production target succeeds and is health-gated; a rollback is
  executed and verified; kill switch operates; no false "deployed" claim.
- **Test plan:** Deploy dry-run; rollback verification; kill-switch test.
- **Evidence:** Deploy + rollback transcript under `docs/evidence/git-release/` and `docs/evidence/validation/`.
- **Rollback:** The epic itself delivers rollback; revert its automation if defective.
- **Estimate class:** L · **Risk:** High · **Owner role:** Release/CI Owner + Operations Owner.
- **Definition of Done:** Deploy + rollback proven on a non-production target with runtime evidence.

## EPIC-SF-16 — Security and Architecture Verification

- **Objective:** Verify the whole foundation against security and architecture fitness functions before any
  pilot readiness is considered.
- **Scope:** Consolidated multi-tenant isolation verification, security test battery (broken access control,
  cross-tenant, privilege escalation, OAuth leakage, CSRF/XSS/SQLi, file upload, webhook forgery, rate-limit
  bypass, IDOR/SSRF), architecture fitness functions (module boundaries, no forbidden cross-writes,
  outbox/idempotency), and traceability with no critical orphan.
- **Out-of-scope:** Business-feature acceptance; pilot runtime.
- **Dependencies:** EPIC-SF-01..15.
- **Architecture references:** Fitness functions and traceability per Rule 20 (AFR-069); testing categories per
  Rule 09 (Master Source §50).
- **AFR references:** AFR-069 (no orphan), AFR-099..102, and all upstream AFRs verified.
- **Database impact:** None (verification only).
- **Security impact:** Highest assurance checkpoint for the foundation.
- **Privacy impact:** Confirms no PII/medical leakage paths in foundation surfaces.
- **Tenant impact:** Confirms tenant isolation on every foundation surface.
- **Operational impact:** Gate before declaring the foundation ready for business features / pilot planning.
- **Acceptance criteria:** All security tests pass; isolation holds on every surface; fitness functions pass;
  traceability shows no critical orphan; evidence complete.
- **Test plan:** Full security suite; isolation suite; fitness-function run; traceability audit.
- **Evidence:** Consolidated security, isolation, and fitness-function evidence under `docs/evidence/`.
- **Rollback:** N/A (verification gate); failures block progression until fixed and retested.
- **Estimate class:** L · **Risk:** High · **Owner role:** Security-Privacy Owner + QA-Traceability Owner.
- **Definition of Done:** Foundation-wide security, isolation, and fitness verification passed with full evidence.

---

## Epic summary table

| Epic | Title | Estimate | Risk | Primary owner |
|------|-------|----------|------|---------------|
| EPIC-SF-01 | Repository Runtime Bootstrap | M | Low | Architect |
| EPIC-SF-02 | Local Development Environment | M | Low | DevEx/Architect |
| EPIC-SF-03 | CI Runtime Foundation | M | Medium | Release/CI |
| EPIC-SF-04 | Authentication and Account Security | L | High | Security-Privacy |
| EPIC-SF-05 | Tenant and Branch Foundation | L | High | Architect |
| EPIC-SF-06 | Roles, Permissions, and Scope | L | High | Security-Privacy |
| EPIC-SF-07 | Audit and Security Events | M | Medium | Security-Privacy |
| EPIC-SF-08 | Queue, Redis, and Scheduler | L | High | Architect |
| EPIC-SF-09 | Tenant-Scoped Storage | M | High | Security-Privacy |
| EPIC-SF-10 | Notification Foundation | M | Medium | Architect |
| EPIC-SF-11 | Subscription and Entitlement Skeleton | M | Medium | Architect/Product |
| EPIC-SF-12 | Platform Admin Skeleton | M | High | Security-Privacy |
| EPIC-SF-13 | Observability and Health Checks | M | Medium | Operations |
| EPIC-SF-14 | Backup and Restore Foundation | M | High | Operations |
| EPIC-SF-15 | Deployment and Rollback Foundation | L | High | Release/Operations |
| EPIC-SF-16 | Security and Architecture Verification | L | High | Security-Privacy/QA |

All estimates are planning classes only. No epic has been executed. Application implementation: NOT STARTED.
