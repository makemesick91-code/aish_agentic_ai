# SaaS Foundation Sprint Roadmap (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. No sprint below has been
  executed. Estimates and durations are planning signals, not commitments. Architecture (ADRs 0009–0032,
  the Application Architecture Baseline, the Application Foundation Rules) is cited by name/number in prose only.

---

## Overview

The SaaS Foundation is delivered by nine sprints, SPRINT-SF-00 through SPRINT-SF-08, executing the fixed
sequence in [SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md](SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md). Epics are defined
in [SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md); dependencies in
[SAAS_FOUNDATION_DEPENDENCY_MAP.md](SAAS_FOUNDATION_DEPENDENCY_MAP.md). The foundation must **not** be collapsed
into one giant sprint; each sprint is a distinct evidence-gated increment. The recommended first sprint after
the Step 4 GO tag is SPRINT-SF-00, detailed in [NEXT_IMPLEMENTATION_SPRINT.md](NEXT_IMPLEMENTATION_SPRINT.md).

Every sprint carries a **Master Source update rule**: on completion, if the sprint changed scope, workflow,
architecture, security posture, or produced GO/WATCH/NO-GO results, a `MASTER SOURCE UPDATE` block with a
semver bump is required per Rule 12; a pure status/clarification is a patch bump.

Each sprint's exit is judged on **GO/WATCH/NO-GO** criteria. **GO**: all sprint gates green with evidence, no
unresolved critical/high issue, isolation and security checks pass. **WATCH**: no critical breach but adoption,
performance, coverage, or cost signals still need work. **NO-GO**: cross-tenant exposure, privilege escalation,
secret leakage, falsified success, missing release-critical evidence, or a failed hard gate — blocks
progression until fixed and retested.

| Sprint | Goal | Epics |
|--------|------|-------|
| SPRINT-SF-00 | Runtime bootstrap & local/CI environment | EPIC-SF-01, 02, 03 |
| SPRINT-SF-01 | Config/secret foundation & authentication | config foundation, EPIC-SF-04 |
| SPRINT-SF-02 | Tenant & branch foundation | EPIC-SF-05 |
| SPRINT-SF-03 | RBAC & audit | EPIC-SF-06, 07 |
| SPRINT-SF-04 | Queue/cache/storage isolation | EPIC-SF-08, 09 |
| SPRINT-SF-05 | Notification, subscription & admin skeletons | EPIC-SF-10, 11, 12 |
| SPRINT-SF-06 | Observability & backup/restore | EPIC-SF-13, 14 |
| SPRINT-SF-07 | Deployment & rollback | EPIC-SF-15 |
| SPRINT-SF-08 | Security & architecture verification | EPIC-SF-16 |

---

## SPRINT-SF-00 — Runtime Bootstrap & Local/CI Environment

- **Goal:** Produce a runnable Laravel 12 skeleton with a reproducible local runtime and green CI, so all later
  work can be validated with evidence.
- **Scope:** EPIC-SF-01, EPIC-SF-02, EPIC-SF-03 — app skeleton, module directory shells, local stack, CI
  pipeline with secret scanning and evidence capture.
- **Out-of-scope (explicit):** Authentication, tenancy, RBAC, any business module logic, deployment.
- **Entry criteria:** Definition of Ready satisfied (see
  [SAAS_FOUNDATION_DEFINITION_OF_READY.md](SAAS_FOUNDATION_DEFINITION_OF_READY.md)); Step 4 GO tag verified;
  origin confirmed `makemesick91-code/aish_agentic_ai`.
- **User stories:** As a developer I can clone and boot the app locally; as a maintainer I see CI validate
  every PR; as a security owner I see secret scanning enforced.
- **Technical tasks:** Framework skeleton; module boundary shells; local container/stack; env templates with
  placeholders; CI workflow (install, lint, test, secret scan, evidence archive).
- **Security tasks:** Secret scan wired into CI; push protection confirmed enabled; no committed secrets;
  debug disabled outside local.
- **Testing:** Boot smoke test; lint; a deliberately failing test proving CI fails red.
- **CI gates:** Install, static analysis, unit smoke, secret scan — all required and green.
- **Documentation:** Local-dev bootstrap doc; CI runtime doc; status update.
- **Evidence:** Boot transcript, CI run logs, secret-scan output under `docs/evidence/`.
- **Rollback:** Revert the sprint branch; repository returns to documentation-only baseline.
- **GO/WATCH/NO-GO:** GO when app boots reproducibly and CI is green with evidence; WATCH if CI is flaky or
  local boot needs manual steps; NO-GO if a secret is committed or CI cannot run.
- **Master Source update rule:** Record foundation runtime start; patch/minor bump per materiality.

## SPRINT-SF-01 — Configuration/Secret Foundation & Authentication

- **Goal:** Establish secure configuration/secret handling and authenticated identity.
- **Scope:** Config/secret foundation and EPIC-SF-04 — Fortify/Sanctum auth, MFA capability, token encryption,
  OAuth state validation primitives, auth rate limiting.
- **Out-of-scope (explicit):** Tenant binding, RBAC, external Google OAuth integration, business features.
- **Entry criteria:** SPRINT-SF-00 GO; secret matrix approved; auth acceptance criteria available.
- **User stories:** As a user I can log in securely with MFA; as a security owner I confirm tokens are
  encrypted and auth is rate-limited.
- **Technical tasks:** Config/secret referencing layer; auth flows; MFA; session/token issuance; account
  recovery; OAuth state validation.
- **Security tasks:** Token encryption; refresh tokens never plaintext; CSRF protection; auth rate limiting;
  OAuth state validation; credential-stuffing defenses.
- **Testing:** Auth functional tests; security tests (CSRF, rate-limit bypass, token handling, OAuth state).
- **CI gates:** Prior gates plus auth functional + security suites green.
- **Documentation:** Auth design notes; secret matrix reference; status update.
- **Evidence:** Auth + security test transcripts under `docs/evidence/validation/`.
- **Rollback:** Disable/flag auth routes; revert branch; no tenant data affected.
- **GO/WATCH/NO-GO:** GO when auth + security tests pass with evidence; WATCH if MFA or recovery needs
  hardening; NO-GO on token leakage, secret exposure, or auth bypass.
- **Master Source update rule:** Security-posture change → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-02 — Tenant & Branch Foundation

- **Goal:** Enforce tenant/branch context so every business record can carry and enforce ownership.
- **Scope:** EPIC-SF-05 — tenant/branch entities, context resolution/propagation, ownership convention, base
  scoping enforcement.
- **Out-of-scope (explicit):** RBAC, queue/storage propagation implementation, business features.
- **Entry criteria:** SPRINT-SF-01 GO; tenant-isolation implementation sequence available.
- **User stories:** As the platform I reject unscoped access to tenant data; as a branch user I see only my
  branch's records.
- **Technical tasks:** Tenant/branch tables; context primitive; ownership columns/constraints; query-boundary
  scoping.
- **Security tasks:** Cross-tenant read/write denial; fail-safe on missing scope; branch-scope enforcement.
- **Testing:** Multi-tenant isolation tests; branch-scoping tests; context-propagation tests.
- **CI gates:** Prior gates plus isolation suite green.
- **Documentation:** Tenancy implementation notes referencing the Tenant Isolation Control Matrix; status update.
- **Evidence:** Isolation test results under `docs/evidence/validation/`.
- **Rollback:** Revert tenancy branch; no business features stranded.
- **GO/WATCH/NO-GO:** GO when isolation tests pass with evidence; WATCH if scoping coverage is partial; NO-GO
  on any cross-tenant exposure.
- **Master Source update rule:** Tenancy is foundational → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-03 — RBAC & Audit

- **Goal:** Enforce role/permission/branch scoping and an immutable audit trail before privileged surfaces and
  sensitive mutations.
- **Scope:** EPIC-SF-06 and EPIC-SF-07 — roles/permissions, branch-scoped authorization, append-only audit and
  security events.
- **Out-of-scope (explicit):** Platform-admin UI, approval-workflow content, full observability.
- **Entry criteria:** SPRINT-SF-02 GO; role set and audit requirements available.
- **User stories:** As an owner I assign roles; as a branch manager I cannot access other branches; as a
  security owner every sensitive action is audited and audit is non-deletable.
- **Technical tasks:** Roles/permissions; policy gates; branch scoping; audit + security-event write path;
  immutability enforcement.
- **Security tasks:** Privilege-escalation defense; IDOR defense; audit immutability; tenant-scoped audit.
- **Testing:** Authorization tests; escalation/IDOR tests; audit-coverage + immutability tests.
- **CI gates:** Prior gates plus RBAC + audit suites green.
- **Documentation:** RBAC + audit notes; status update.
- **Evidence:** RBAC and audit test transcripts under `docs/evidence/validation/`.
- **Rollback:** Revert branch; access defaults to deny; audit path removed with progression blocked (by design).
- **GO/WATCH/NO-GO:** GO when RBAC + audit tests pass with evidence; WATCH if audit coverage is partial; NO-GO
  on privilege escalation or mutable/deletable audit.
- **Master Source update rule:** Security-control addition → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-04 — Queue/Cache/Storage Isolation

- **Goal:** Guarantee tenant context in async work and tenant-isolated storage before external async and
  uploads.
- **Scope:** EPIC-SF-08 and EPIC-SF-09 — Redis queue/scheduler with tenant context, idempotency, dead-letter;
  tenant-scoped S3-compatible storage with secure upload.
- **Out-of-scope (explicit):** External integrations (Google/WhatsApp), business upload features.
- **Entry criteria:** SPRINT-SF-03 GO; outbox/idempotency contract confirmed.
- **User stories:** As the platform, queued jobs carry tenant context and retries never duplicate side effects;
  as a tenant, my files are isolated and cannot be read by another tenant.
- **Technical tasks:** Queue/worker/scheduler; context propagation into jobs/events; idempotency + dead-letter;
  storage abstraction with tenant/branch prefixes; upload validation; signed URLs.
- **Security tasks:** Job cross-tenant leakage prevention; storage cross-tenant access denial; unsafe-upload
  rejection; path-traversal defense.
- **Testing:** Queue context + idempotent-retry tests; storage isolation + malicious-upload tests.
- **CI gates:** Prior gates plus queue + storage isolation suites green.
- **Documentation:** Queue and storage isolation notes; status update.
- **Evidence:** Queue/storage isolation and idempotency results under `docs/evidence/validation/`.
- **Rollback:** Disable workers/upload routes; revert branch; synchronous fallback documented.
- **GO/WATCH/NO-GO:** GO when isolation + idempotency tests pass with evidence; WATCH if dead-letter handling
  needs tuning; NO-GO on cross-tenant job/object access or duplicate side effects.
- **Master Source update rule:** Async/storage foundation → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-05 — Notification, Subscription & Admin Skeletons

- **Goal:** Deliver tenant-safe notification, entitlement, and privileged-admin skeletons without AI dependence
  and without charging money.
- **Scope:** EPIC-SF-10, EPIC-SF-11, EPIC-SF-12 — notification dispatch skeleton, subscription/entitlement +
  idempotent metering, guarded and audited platform-admin skeleton.
- **Out-of-scope (explicit):** WhatsApp/Google content, AI-generated messages, real billing charges,
  destructive admin bulk operations.
- **Entry criteria:** SPRINT-SF-04 GO; entitlement and admin acceptance criteria available.
- **User stories:** As a tenant I receive tenant-scoped notifications with truthful delivery states; as an
  operator I use an audited admin console; as the platform I gate features by plan without charging.
- **Technical tasks:** Notification abstraction + queue delivery; delivery-state vocabulary; plans/subscriptions/
  entitlements; idempotent metering; admin surface guarded by RBAC + audit.
- **Security tasks:** Notification tenant scoping; no sensitive data in delivery logs; entitlement-bypass
  defense; admin RBAC + audit + risky-action confirmation.
- **Testing:** Notification delivery/truthful-state/fallback tests; entitlement + metering tests; admin RBAC +
  audit tests.
- **CI gates:** Prior gates plus notification, entitlement, and admin suites green.
- **Documentation:** Notification, subscription, and admin skeleton notes; status update.
- **Evidence:** Test transcripts under `docs/evidence/validation/`.
- **Rollback:** Disable notification/admin routes and entitlement gating; revert branch.
- **GO/WATCH/NO-GO:** GO when the three skeletons pass their suites with evidence and manual works without AI;
  WATCH if metering reconciliation needs work; NO-GO on cross-tenant notification, entitlement bypass, or
  unaudited admin action.
- **Master Source update rule:** New foundation surfaces → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-06 — Observability & Backup/Restore

- **Goal:** Deliver the observability and tested backup/restore required before any pilot.
- **Scope:** EPIC-SF-13 and EPIC-SF-14 — structured logging, health checks, tracing hooks, minimum alerts;
  backups with a tested restore and retention.
- **Out-of-scope (explicit):** AI agent-run dashboards; full DR topology; production go-live.
- **Entry criteria:** SPRINT-SF-05 GO; deployment-target class and rollback plan available.
- **User stories:** As an operator I see component health and alerts; as an operator I can restore from backup
  and verify it.
- **Technical tasks:** Structured logs; health/readiness endpoints; tracing hooks; metrics; alerts; backup
  routine; restore procedure; retention.
- **Security tasks:** Log redaction (no secrets/PII); tenant-isolation anomaly alert; backups encrypted and
  never committed.
- **Testing:** Health-check tests; log-redaction tests; backup execution + restore-verification tests.
- **CI gates:** Prior gates plus observability + backup smoke green.
- **Documentation:** Observability and backup/restore baselines; status update.
- **Evidence:** Health-check output, log-redaction results, restore-verification transcript (no real PII).
- **Rollback:** Revert observability/backup automation; manual backup fallback documented.
- **GO/WATCH/NO-GO:** GO when health checks work and restore is verified with evidence; WATCH if alert coverage
  is partial; NO-GO on secret/PII in logs or an unverifiable restore.
- **Master Source update rule:** Operational readiness → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-07 — Deployment & Rollback

- **Goal:** Establish a deployment path with a proven rollback before any deployment claim.
- **Scope:** EPIC-SF-15 — deployment target class, release process, migration-safe deploy, health-gated
  cutover, tested rollback, kill-switch wiring.
- **Out-of-scope (explicit):** Production go-live; pilot cutover; scaling.
- **Entry criteria:** SPRINT-SF-06 GO; rollback plan and deployment-target class approved.
- **User stories:** As a release owner I deploy to a non-production target and roll back on failure; as an
  operator I can trip the kill switch.
- **Technical tasks:** Deployment pipeline; migration-safe deploy; health gate; rollback procedure; kill switch.
- **Security tasks:** Deploy secrets referenced securely; no secret in pipeline logs; rollback preserves
  tenant isolation.
- **Testing:** Deploy dry-run; rollback verification; kill-switch test.
- **CI gates:** Prior gates plus deploy/rollback smoke green.
- **Documentation:** Deployment + rollback runbook; status update.
- **Evidence:** Deploy + rollback transcript under `docs/evidence/git-release/` and `docs/evidence/validation/`.
- **Rollback:** Revert deploy automation if defective; manual deploy runbook fallback.
- **GO/WATCH/NO-GO:** GO when a non-production deploy and rollback are proven with runtime evidence; WATCH if
  migration reversibility is partial; NO-GO on a false "deployed" claim or unrecoverable rollback.
- **Master Source update rule:** Deployment capability → minor bump with `MASTER SOURCE UPDATE`.

## SPRINT-SF-08 — Security & Architecture Verification

- **Goal:** Verify the whole foundation against security and architecture fitness functions before any pilot
  readiness is considered.
- **Scope:** EPIC-SF-16 — consolidated multi-tenant isolation verification, full security battery, architecture
  fitness functions, and traceability with no critical orphan.
- **Out-of-scope (explicit):** Business-feature acceptance; pilot runtime; production readiness.
- **Entry criteria:** SPRINT-SF-07 GO; all prior sprints GO; traceability has no critical orphan.
- **User stories:** As a security owner I confirm isolation and security across every surface; as a QA owner I
  confirm every foundation requirement traces to evidence.
- **Technical tasks:** Run isolation suite; run security battery; run fitness functions; audit traceability.
- **Security tasks:** Broken access control, cross-tenant, privilege escalation, OAuth leakage, CSRF/XSS/SQLi,
  file upload, webhook forgery, rate-limit bypass, IDOR/SSRF — all exercised.
- **Testing:** Full security + isolation + fitness-function suites; traceability audit.
- **CI gates:** All prior gates plus the full verification suite green.
- **Documentation:** Verification report; traceability matrix update; status update.
- **Evidence:** Consolidated security, isolation, and fitness-function evidence under `docs/evidence/`.
- **Rollback:** N/A (verification gate); failures block progression until fixed and retested.
- **GO/WATCH/NO-GO:** GO when all security + isolation + fitness checks pass and traceability has no critical
  orphan; WATCH if only non-critical findings remain; NO-GO on any isolation breach, security failure, or
  critical orphan.
- **Master Source update rule:** Foundation verification result → minor bump with `MASTER SOURCE UPDATE`;
  record GO/WATCH/NO-GO decision.

---

## Sequencing rules

- Sprints run in order; a later sprint MUST NOT start before the prior sprint reaches GO (or an owner-approved
  WATCH with a recorded remediation plan).
- No sprint may weaken or skip a gate from an earlier sprint.
- Every GO/WATCH/NO-GO decision is recorded with evidence; no fabricated success.

Application implementation: NOT STARTED. This roadmap is a PLANNING BASELINE — NOT IMPLEMENTED.
