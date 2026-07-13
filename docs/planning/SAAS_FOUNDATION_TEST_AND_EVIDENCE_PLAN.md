# SaaS Foundation Test and Evidence Plan (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, test, or code has been created. This plan describes tests
  to be written and evidence to be captured by a future implementation; none has been executed. Architecture
  (ADRs 0009–0032, the Application Architecture Baseline, the Application Foundation Rules) is cited by
  name/number in prose only.

---

## Purpose

This plan defines the test categories and per-epic evidence requirements for the SaaS Foundation. It
implements the Master Source §50 testing categories and the Rule 09 release gates. Every epic in
[SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md) contributes tests and evidence; the shared
completion contract is [SAAS_FOUNDATION_DEFINITION_OF_DONE.md](SAAS_FOUNDATION_DEFINITION_OF_DONE.md).

Tenant isolation is the load-bearing property of this foundation. Every test category below treats tenant
isolation as a first-class assertion, and no epic is done until its tenant isolation evidence exists.

---

## Test categories

### 1. Functional

Verifies each epic's acceptance criteria behave as specified: authentication flows, tenant/branch context,
RBAC decisions, audit writes, queue processing, storage operations, notification dispatch, entitlement gating,
admin actions, health checks, backup/restore, and deploy/rollback. Evidence: captured test output per epic.

### 2. Multi-tenant isolation

The central category. Verifies there is no cross-tenant leakage on any surface: DB queries, cache, queue jobs,
file storage, search, export, API, webhook, notifications, analytics, and tenant-visible logs. Tests include
cross-tenant read/write denial, branch-scope leakage, unscoped-query fail-safe, queue tenant-context
propagation, and storage cross-tenant object-access denial. Tenant isolation evidence is mandatory for
EPIC-SF-05 through EPIC-SF-16. Evidence: isolation suite results under `docs/evidence/validation/`.

### 3. AI evaluation (adversarial)

Although the foundation ships no AI feature, the AI boundary is asserted: customer content must not steer tool
calls, MED-classified data must not reach AI or public output, and manual works without AI. When AI features
arrive on top of this foundation, the adversarial dataset (prompt injection, PII, sarcasm, mixed language)
applies with no PII/medical leakage, valid structured output, human approval, cost limit, kill switch, and
idempotent retry. Foundation-stage evidence: negative tests proving no AI dependency in basic workflows and no
prohibited data path.

### 4. Security

Exercises the full battery: broken access control, cross-tenant access, privilege escalation, OAuth leakage,
CSRF, XSS, SQLi, file upload, webhook forgery, rate-limit bypass, and IDOR/SSRF. Mapped to epics: auth
(EPIC-SF-04), RBAC (EPIC-SF-06), storage upload (EPIC-SF-09), admin (EPIC-SF-12), and the consolidated
verification (EPIC-SF-16). Evidence: security-test transcripts under `docs/evidence/validation/`.

### 5. Performance

Baseline performance checks on foundation surfaces: auth, tenant-scoped queries, queue throughput, and health
endpoints under representative load. Not a full load test at foundation stage, but a regression baseline.
Evidence: performance-run summaries under `docs/evidence/validation/`.

---

## Per-epic evidence requirements

| Epic | Primary test categories | Required evidence |
|------|-------------------------|-------------------|
| EPIC-SF-01 | Functional | Boot smoke transcript, lint output, secret scan |
| EPIC-SF-02 | Functional | Fresh-clone boot transcript, environment matrix confirmation |
| EPIC-SF-03 | Functional, Security | CI run logs, failing-case proof, secret scan |
| EPIC-SF-04 | Functional, Security | Auth functional results, CSRF/rate-limit/OAuth-state/token security transcripts |
| EPIC-SF-05 | Multi-tenant isolation | Cross-tenant denial, branch-scope, context-propagation results |
| EPIC-SF-06 | Security, Multi-tenant isolation | Authorization, privilege-escalation, IDOR, branch-scope results |
| EPIC-SF-07 | Functional, Security | Audit-coverage, immutability, tenant-scope results |
| EPIC-SF-08 | Multi-tenant isolation, Functional | Queue tenant-context, idempotent-retry, dead-letter results |
| EPIC-SF-09 | Security, Multi-tenant isolation | Storage isolation, malicious-upload, signed-URL scope results |
| EPIC-SF-10 | Functional, Multi-tenant isolation | Delivery, truthful-state, AI-unavailable fallback, tenant-scope results |
| EPIC-SF-11 | Functional, Multi-tenant isolation | Entitlement-gating, idempotent-metering, tenant-scope results |
| EPIC-SF-12 | Security, Functional | Admin RBAC, audit-coverage, risky-action confirmation results |
| EPIC-SF-13 | Functional, Security | Health-check, log-redaction, alert-definition results |
| EPIC-SF-14 | Functional | Backup execution, restore-verification transcript (no real PII), retention check |
| EPIC-SF-15 | Functional | Deploy dry-run, rollback verification, kill-switch results |
| EPIC-SF-16 | All | Consolidated isolation, security battery, fitness-function, traceability audit |

---

## Evidence handling rules

- All evidence lives under `docs/evidence/` (subfolders `validation/`, `ci/`, `git-release/`).
- Evidence MUST be tenant-safe and contain **no real customer PII** and no secrets.
- Fixtures are synthetic; restore verification uses non-production, non-real data.
- Evidence is captured at the time of the run, never fabricated or back-dated.
- A missing evidence artifact means the corresponding item is not done (see the Definition of Done).

---

## Gate integration

- CI runs functional, isolation, and security suites on PR and main (Rule 13); failing gates block merge.
- Release gates (functional, security, data, AI, integration, operational) apply per Rule 09 before any
  product-release GO.
- Fitness functions (module boundaries, no forbidden cross-writes, outbox/idempotency, no critical orphan) run
  in EPIC-SF-16 and are re-runnable in CI.

Application implementation: NOT STARTED. This test and evidence plan is a PLANNING BASELINE — NOT IMPLEMENTED.
