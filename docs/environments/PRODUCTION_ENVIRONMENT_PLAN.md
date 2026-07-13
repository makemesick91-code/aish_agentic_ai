# Production Environment Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Production environment: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `07`, `11`, `13`, `18`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. The production environment is **NOT STARTED**. No
> secret is stored or committed. No hosting provider is selected (WATCH). All topology below is
> `PLANNED TOPOLOGY — NOT DEPLOYED`.

## 1. Purpose

`production` is the general-availability, multi-tenant SaaS environment serving live tenants under full
production controls. It is the terminal environment in the promotion flow and carries the strictest security,
privacy, isolation, and operational requirements.

## 2. Deployment class (PLANNED TOPOLOGY — NOT DEPLOYED)

- `production` **MUST** run on dedicated Ubuntu LTS VM(s) or equivalently isolated compute, right-sized to
  load.
- It **MUST NOT** share a database, Redis namespace, application directory, PHP-FPM pool, queue worker,
  storage, secrets, or deployment user with any lower environment, with DaengtisiaMS, or with Aish POS.
- Hosting provider is **not selected** (WATCH).

| Layer | Planned production setup |
|-------|--------------------------|
| Compute | Dedicated, right-sized Ubuntu LTS VM(s) |
| Web | Nginx server block `app.aish.example` + PHP-FPM pool `aish-prod` |
| Database | `aish_agentic_prod` (dedicated PostgreSQL, backups + tested restore) |
| Cache/queue | Redis prefix `aish:prod:`, dedicated instance; worker pool `aish-prod-worker` |
| Storage | Bucket `aish-agentic-prod` |
| Deploy user | `aish-prod` |
| App dir | `/srv/aish/prod` |
| Secrets | `secret/aish/prod/` (most restricted, audited) |

## 3. Data policy

- `production` holds **approved tenant data under production controls** only, within contract/consent scope
  (Rule 07).
- Production data **MUST NOT** be copied to `local`, `test`, `CI`, or `staging`; any anonymized export
  requires documented approval + secure anonymization (see [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md)).
- Prohibited healthcare fields **MUST NOT** reach AI or public output (Rule 18).

## 4. Configuration and secrets

- Production uses **environment-specific**, most-restricted secrets under `secret/aish/prod/`; values
  **MUST NOT be committed** (see [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)).
- Access/refresh tokens encrypted (refresh never plaintext); OAuth credentials encrypted at rest; rotation
  documented and enforced.
- Production secrets **MUST NOT** equal any developer or lower-environment secret.

## 5. External integration behavior

| Integration | Production behavior |
|-------------|---------------------|
| Email | Real provider, full, consent-aware |
| WhatsApp | Real provider, consent-gated, frequency caps (Rule 17) |
| Google | Real GBP; every reply human-approved before publish; no gating/manipulation (Rules 06, 18) |
| AI provider | Real provider, guardrails, redaction, structured output, prompt/model versioning, cost/trace, kill switch; no MED data |

External success **MUST NOT** be reported before provider verification; failed actions keep truthful failed
states (Rule 18). Human approval on public/high-risk actions **MUST NOT** be bypassed (Rule 05).

## 6. Isolation

Tenant/branch **isolation** **MUST** hold on every surface: DB, cache, queue, storage, search, export, API,
webhook, analytics, notifications, AI retrieval, knowledge base, and tenant-visible logs (Rule 03). No
cross-tenant leakage is tolerated; a breach is **NO-GO**.

## 7. Operations (Rule 11)

| Concern | Production plan |
|---------|-----------------|
| Logging | Structured, PII-redacted; audit immutable (Rule 07) |
| Monitoring | Full metrics, alerts, traces, SLOs; AI cost/guardrail alerts |
| Backup | Scheduled backup, **tested restore** (mandatory) |
| Restore | Tested restore drill; documented RPO/RTO |
| Retention | Configurable per policy/contract |
| Reset | Controlled, audited; never casual |
| Incident/rollback | Runbook + rollback plan required before GO |

## 8. Release gates (Rules 09, 13)

Promotion `pilot → production` requires: owner approval (Rule 19 GO), all functional/security/data/AI/
integration/operational gates passing with evidence, verified backup + tested restore, rehearsed rollback,
post-deploy smoke, and an immutable annotated release tag on the merged commit. No unreviewed branch is
deployed. Gates **MUST NOT** be skipped, weakened, or faked.

## 9. Truthful status

The production environment is **NOT STARTED** and is **PLANNED TOPOLOGY — NOT DEPLOYED**. No provider, VM, DNS,
backup, or monitoring is provisioned. "Production ready" **MUST NOT** be claimed without the full release-gate
evidence. Application implementation, deployment, pilot readiness/runtime, and production readiness all remain
**NOT STARTED**.

## 10. Cross-reference

Predecessor: [PILOT_ENVIRONMENT_PLAN.md](PILOT_ENVIRONMENT_PLAN.md). Promotion gates:
[ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md). Naming:
[ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md).
