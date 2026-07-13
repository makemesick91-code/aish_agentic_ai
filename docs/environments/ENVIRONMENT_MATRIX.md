# Environment Matrix — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `11`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No environment exists. No secret is stored or
> committed. No hosting provider is selected (WATCH). This matrix is a **planning specification**.

## 1. Scope

This matrix documents, for each of the six environments — **local**, **test**, **CI**, **staging**, **pilot**,
and **production** — the full attribute set required by the Step 4 environment baseline. Every environment
**MUST** enforce tenant and cross-environment **isolation** on all surfaces named below. See
[ENVIRONMENT_STRATEGY.md](ENVIRONMENT_STRATEGY.md) for the model and
[DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md) for the data rules.

## 2. Purpose, ownership, and data class

| Attribute | local | test | CI | staging | pilot | production |
|-----------|-------|------|----|---------|-------|------------|
| Purpose | Individual dev build & manual testing | Automated test execution | Pipeline runtime validation | Pre-prod integration & UAT | First-tenant live pilot | GA multi-tenant SaaS |
| Owner | Individual developer | Developer / QA | DevOps (pipeline owner) | DevOps + QA lead | DevOps + Pilot Coordinator | DevOps + Product Owner |
| Data classification | Synthetic only | Synthetic fixtures | Synthetic fixtures | Synthetic OR formally anonymized | Approved pilot data (minimum-data) | Approved tenant data (production controls) |
| Allowed data | Fabricated records, seed factories | Deterministic fixtures | Deterministic fixtures | Synthetic or signed-off anonymized | Consented pilot tenant records, minimized | Live tenant records under contract |
| Forbidden data | Any real PII/medical/financial | Any real PII | Any real PII | Raw production data | Non-consented or non-minimized data | Data outside contract/consent |

## 3. Isolation surfaces

Cross-environment collision is prohibited; concrete names live in
[ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md).

| Isolation surface | local | test | CI | staging | pilot | production |
|-------------------|-------|------|----|---------|-------|------------|
| Database isolation | Dedicated local DB | Separate test DB | Ephemeral service DB | Dedicated DB | Dedicated DB (no share w/ DaengtisiaMS/POS) | Dedicated DB cluster |
| Redis isolation | Prefix `aish:local:` | Prefix `aish:test:` | Prefix `aish:ci:` | Prefix `aish:staging:` | Prefix `aish:pilot:` | Prefix `aish:prod:` |
| Queue isolation | Local queue conn | Sync/array driver | Ephemeral queue | Dedicated queue | Dedicated queue worker | Dedicated queue worker pool |
| Storage isolation | Local disk / bucket `aish-agentic-local` | `aish-agentic-test` | `aish-agentic-ci` | `aish-agentic-staging` | `aish-agentic-pilot` | `aish-agentic-prod` |

## 4. External integration behavior

| Behavior | local | test | CI | staging | pilot | production |
|----------|-------|------|----|---------|-------|------------|
| Email | Mailpit/log driver (no real send) | Array/log (asserted, no send) | Log driver (no send) | Sandbox mailbox only | Real provider, restricted recipients | Real provider, full |
| WhatsApp | Mock adapter (no send) | Mock adapter | Mock adapter | Sandbox/mock (no patient send) | Real provider, consented pilot only, human-gated | Real provider, consent-gated |
| Google integration | Mock/disabled | Mock/disabled | Mock/disabled | Sandbox or mock (no publish) | Real GBP, human-approved reply only | Real GBP, human-approved reply only |
| AI provider | Mock or dev key, redaction on | Mock/stubbed responses | Mock/stubbed responses | Sandbox key, guardrails on | Real provider, guardrails + approval, no MED data | Real provider, guardrails + approval, no MED data |

Lower environments **MUST NOT** perform real external side effects. `pilot`/`production` external actions
**MUST NOT** report success before provider verification (Rule 18) and **MUST** respect human approval
(Rules 05, 06).

## 5. Runtime, flags, and observability

| Attribute | local | test | CI | staging | pilot | production |
|-----------|-------|------|----|---------|-------|------------|
| URL pattern | `http://localhost:8000` | n/a (test harness) | n/a (runner) | `https://staging.aish.example` | `https://pilot.aish.example` | `https://app.aish.example` |
| Feature flags | All experimental on | Per-test toggles | Per-test toggles | Release-candidate set | Pilot-approved set only | GA-approved set only |
| Logging | Debug, pretty | Debug, captured | Debug, artifacted | Info + debug, structured | Info, structured, PII-redacted | Info/Warn, structured, PII-redacted |
| Monitoring | None required | Test reporter | CI reporter | Metrics + error tracking | Full metrics, alerts, traces | Full metrics, alerts, traces, SLO |

## 6. Retention, backup, restore, reset

| Attribute | local | test | CI | staging | pilot | production |
|-----------|-------|------|----|---------|-------|------------|
| Retention | Ephemeral | Ephemeral | Ephemeral | Short (days–weeks) | Pilot-period, configurable | Configurable per policy/contract |
| Backup | None | None | None | Optional snapshot | Scheduled backup | Scheduled backup, tested restore |
| Restore | Rebuild from seed | Rebuild | Rebuild | Rebuild/restore snapshot | Tested restore drill | Tested restore drill (mandatory) |
| Reset policy | Any time (developer) | Every run | Every run | On demand / per release | Controlled, audited | Controlled, audited, never casual |

## 7. Access control, promotion, cost, evidence

| Attribute | local | test | CI | staging | pilot | production |
|-----------|-------|------|----|---------|-------|------------|
| Access control | Developer only | Developer/QA | Pipeline identity | RBAC, limited team | RBAC + branch scope, least privilege | RBAC + branch scope, least privilege, MFA |
| Promotion path | → test/CI | → CI/staging | → staging | → pilot | → production | terminal |
| Cost category | Dev machine | Dev/runner | CI minutes | Small VM | Small–medium VM | Right-sized VM(s) |
| Evidence requirement | Local run notes | Test output | CI logs/artifacts | Smoke + UAT evidence | Pilot gate evidence (Rule 19) | Release gate evidence (Rules 09, 11, 13) |

## 8. Deployment-class note (staging, pilot, production)

The three deployment-class environments **MUST** run on dedicated Ubuntu LTS VMs or equivalently isolated
compute and **MUST NOT** by default share a database, Redis namespace, application directory, PHP-FPM pool,
queue worker, storage, secrets, or deployment user with DaengtisiaMS or Aish POS. All topology is
**PLANNED TOPOLOGY — NOT DEPLOYED**; the provider is not selected (WATCH). Details:
[STAGING_PLAN.md](STAGING_PLAN.md), [PILOT_ENVIRONMENT_PLAN.md](PILOT_ENVIRONMENT_PLAN.md),
[PRODUCTION_ENVIRONMENT_PLAN.md](PRODUCTION_ENVIRONMENT_PLAN.md).
