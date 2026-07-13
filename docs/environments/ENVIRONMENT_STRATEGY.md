# Environment Strategy — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `11`, `13`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing in this document is deployed or provisioned. No environment exists yet. No secret is
> stored, referenced with a real value, or committed. No hosting provider has been selected (status **WATCH**).
> This is a **planning baseline**, not an implementation, deployment, pilot-readiness, or production-readiness claim.

## 1. Purpose

Fix the environment model for Aish Agentic AI so that later implementation and operations steps do **not**
reopen fundamental decisions about how many environments exist, what data each may hold, how they are isolated
and named, and how changes promote from a developer laptop to production. This strategy is the parent document
for the sibling planning files in this directory.

Sibling documents (planning specifications only):

- [ENVIRONMENT_MATRIX.md](ENVIRONMENT_MATRIX.md) — per-environment attribute matrix.
- [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md) — configuration and secret classification.
- [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md) — allowed and forbidden data per environment.
- [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md) — collision-free resource naming.
- [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md) — promotion flow and gates.
- [LOCAL_DEVELOPMENT_STRATEGY.md](LOCAL_DEVELOPMENT_STRATEGY.md) — local developer baseline.
- [CI_RUNTIME_PLAN.md](CI_RUNTIME_PLAN.md) — future runtime CI jobs.
- [STAGING_PLAN.md](STAGING_PLAN.md) — staging environment plan.
- [PILOT_ENVIRONMENT_PLAN.md](PILOT_ENVIRONMENT_PLAN.md) — pilot environment plan.
- [PRODUCTION_ENVIRONMENT_PLAN.md](PRODUCTION_ENVIRONMENT_PLAN.md) — production environment plan.

## 2. The six environments

Aish Agentic AI **MUST** operate exactly six named environment classes. Additional ephemeral instances
(e.g. per-branch preview) **MAY** exist only as a variant of `test`/`CI` and **MUST NOT** introduce a seventh
data classification.

| # | Environment | One-line role | Data classification |
|---|-------------|---------------|---------------------|
| 1 | `local` | Individual developer machine | Synthetic only |
| 2 | `test` | Automated test execution (local + shared) | Synthetic fixtures |
| 3 | `CI` | Continuous-integration pipeline runtime | Synthetic fixtures |
| 4 | `staging` | Pre-production integration & UAT | Synthetic or formally anonymized only |
| 5 | `pilot` | First-tenant live pilot (Klinik Gigi Daengtisia) | Approved pilot data, minimum-data policy |
| 6 | `production` | General availability multi-tenant SaaS | Approved tenant data, production controls |

## 3. Guiding principles (MUST)

- Every environment **MUST** have a single documented owner, a single data classification, and enforced
  isolation on all surfaces (see [ENVIRONMENT_MATRIX.md](ENVIRONMENT_MATRIX.md)).
- Environments **MUST NOT** share a database, a Redis namespace, a queue, or an object-storage bucket across
  classifications. Cross-environment collision of resource names is prohibited
  (see [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md)).
- Raw production data **MUST NOT** be copied into `local`, `test`, `CI`, or `staging`
  (see [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md)).
- Tenant isolation, security, and privacy **MUST** outrank convenience in every environment decision
  (Master Source §57).
- Secrets **MUST NOT** be committed; each environment uses **environment-specific** secret material referenced
  from a secret manager (see [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)).
- Change **MUST** flow only along the promotion path `local → test/CI → staging → pilot → production`; there is
  **no direct** deploy from an unreviewed branch to `pilot` or `production`
  (see [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md)).
- External side effects (email, WhatsApp, Google, AI) **MUST** be safe by environment: lower environments
  simulate or sandbox; only `pilot` and `production` may perform verified real external actions, and only
  behind human approval where the canonical rules require it (Rules 05, 06, 18).

## 4. Environment topology overview (PLANNED TOPOLOGY — NOT DEPLOYED)

```text
 developer laptop        shared runners            dedicated isolated compute
 ┌───────────┐  push   ┌───────────┐  merge   ┌─────────┐  gate   ┌───────┐  gate   ┌────────────┐
 │  local    ├────────►│ test / CI ├─────────►│ staging ├────────►│ pilot ├────────►│ production │
 └───────────┘         └───────────┘          └─────────┘         └───────┘         └────────────┘
   synthetic            synthetic              synthetic/anon      pilot data        tenant data
```

- `local`, `test`, and `CI` run on developer or runner compute with synthetic data only.
- `staging`, `pilot`, and `production` are the **deployment class**: each **MUST** run on a dedicated Ubuntu
  LTS VM (or equivalently isolated compute) with no shared database, Redis namespace, application directory,
  PHP-FPM pool, queue worker, storage, secrets, or deployment user between classes.
- The hosting provider is **not selected**; all topology remains **WATCH** and labelled
  `PLANNED TOPOLOGY — NOT DEPLOYED`.

## 5. Cost posture

| Environment | Cost category | Notes |
|-------------|---------------|-------|
| `local` | Developer machine (no cloud cost) | Docker Compose baseline |
| `test` | Developer machine / shared runner | Ephemeral |
| `CI` | CI minutes (metered) | Documentation CI only in Step 4 |
| `staging` | Small dedicated VM | Provisioned only when application exists |
| `pilot` | Small–medium dedicated VM | Provisioned only at pilot readiness |
| `production` | Right-sized dedicated VM(s) | Provisioned only at production readiness |

No cloud cost is incurred in Step 4 because nothing is provisioned.

## 6. Truthful status

Per Rule 10 and CLAUDE.md §5, this cluster of documents uses only evidence-backed status words. The current
truthful state is **PLANNING BASELINE — NOT IMPLEMENTED**. No environment is `DEPLOYED`, `RUNTIME VERIFIED`,
`PILOT READY`, or `PRODUCTION READY`. Any future promotion of this status **MUST** carry the evidence required
by Rules 09 and 13.

## 7. Change control

Material changes to the environment model (adding/removing an environment, changing a data classification,
changing the deployment class, selecting a provider) are architecture-affecting and **MUST** produce a Master
Source update (Rule 12) and, where topology is affected, an ADR. Naming, promotion gates, and data policy
changes **MUST** stay synchronized across the sibling documents in this directory.
