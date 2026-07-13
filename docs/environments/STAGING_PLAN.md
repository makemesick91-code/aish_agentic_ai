# Staging Environment Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `11`, `13`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No staging host exists. No secret is stored or
> committed. Provider not selected (WATCH). All topology below is **PLANNED TOPOLOGY — NOT DEPLOYED**.

## 1. Purpose

`staging` is the pre-production integration and UAT environment. It exercises the full application on the
deployment class before any real customer data is involved, using **synthetic or formally anonymized** data
only (see [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md)).

## 2. Deployment class (PLANNED TOPOLOGY — NOT DEPLOYED)

- `staging` **MUST** run on a **dedicated Ubuntu LTS VM** or equivalently isolated compute.
- It **MUST NOT** share a database, Redis namespace, application directory, PHP-FPM pool, queue worker,
  storage, secrets, or deployment user with any other environment, with DaengtisiaMS, or with Aish POS.
- Hosting provider is **not selected** (WATCH).

| Layer | Planned staging setup |
|-------|-----------------------|
| Compute | Dedicated Ubuntu LTS VM (small) |
| Web | Nginx + PHP-FPM pool `aish-staging` |
| Database | `aish_agentic_staging` (dedicated PostgreSQL) |
| Cache/queue | Redis prefix `aish:staging:`, DB index 3; worker `aish-staging-worker` |
| Storage | Bucket `aish-agentic-staging` |
| Domain | `staging.aish.example` (placeholder) |
| Deploy user | `aish-staging` |
| App dir | `/srv/aish/staging` |

## 3. Data policy

- `staging` **MUST** hold only synthetic data **or** formally anonymized data approved under the exception
  path (owner approval + secure anonymization + prohibited-field removal).
- Raw production data **MUST NOT** be copied to `staging`.
- Prohibited healthcare fields (Rule 18) **MUST NOT** be present even in anonymized form.

## 4. Configuration and secrets

- `staging` uses **environment-specific** secrets under `secret/aish/staging/`; values **MUST NOT be committed**
  (see [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)).
- OAuth/Google credentials, if configured, **MUST** be sandbox and encrypted at rest; refresh tokens never
  plaintext.

## 5. External integration behavior

| Integration | Staging behavior |
|-------------|------------------|
| Email | Sandbox mailbox only; no real customer send |
| WhatsApp | Sandbox/mock; no patient/customer send |
| Google | Sandbox or mock; **no** public review publish |
| AI provider | Sandbox key; guardrails, redaction, structured output on; no MED data |

Staging **MUST NOT** perform verified real public actions; any external success shown **MUST** be truthful and
not presented as production integration success (Rule 18).

## 6. Isolation

Tenant and cross-environment **isolation** **MUST** hold on all surfaces (DB, cache, queue, storage, search,
export, API, webhook, analytics, notifications, AI retrieval, logs — Rule 03). Staging **MUST** use the
`staging` resource names from [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md).

## 7. Operations

| Concern | Staging plan |
|---------|--------------|
| Logging | Structured, info+debug, PII-safe |
| Monitoring | Metrics + error tracking; alerts optional |
| Backup | Optional snapshot before destructive change |
| Restore | Rebuildable from seed or snapshot |
| Retention | Short (days–weeks) |
| Reset | On demand / per release |

## 8. Role in promotion

`staging` sits between `test`/`CI` and `pilot` in the flow `local → test/CI → staging → pilot → production`.
Promotion **into** staging requires green CI and a reviewed merge; promotion **out of** staging to `pilot`
requires release approval, migration/backup/smoke/rollback gates, and evidence (see
[ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md)). No unreviewed branch is deployed here.

## 9. UAT usage

- UAT scenarios run on staging with synthetic personas mapped to the pilot roles.
- UAT evidence is required before pilot promotion (Rule 19).
- UAT **MUST NOT** use real patient data.

## 10. Truthful status

Staging is **NOT STARTED** and **NOT DEPLOYED**. No provider, VM, or DNS is provisioned. This plan is a
`PLANNED TOPOLOGY — NOT DEPLOYED` specification; promotion of status requires the evidence in Rules 09, 11, 13.

## 11. Cross-reference

Pilot successor: [PILOT_ENVIRONMENT_PLAN.md](PILOT_ENVIRONMENT_PLAN.md).
Production successor: [PRODUCTION_ENVIRONMENT_PLAN.md](PRODUCTION_ENVIRONMENT_PLAN.md).
Attributes: [ENVIRONMENT_MATRIX.md](ENVIRONMENT_MATRIX.md).
