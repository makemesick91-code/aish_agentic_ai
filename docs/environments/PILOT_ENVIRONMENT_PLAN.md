# Pilot Environment Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Pilot environment: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `16`, `17`, `18`, `19`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. The pilot environment is **NOT STARTED**. No secret
> is stored or committed. No hosting provider is selected (WATCH). All topology below is
> `PLANNED TOPOLOGY — NOT DEPLOYED`. Pilot operational targets are **hypotheses**, not results.

## 1. Purpose

The `pilot` environment hosts the first live tenant, **Klinik Gigi Daengtisia** (recommended first branch
**Daengtisia Pusat**, subject to readiness verification), under the minimum-data policy. It is the first
environment permitted to hold real, consented customer data and perform verified real external actions behind
human approval.

## 2. Deployment class (PLANNED TOPOLOGY — NOT DEPLOYED)

- `pilot` **MUST** run on a **dedicated Ubuntu LTS VM** or equivalently isolated compute.
- `pilot` **MUST NOT** by default share a database, Redis namespace, application directory, PHP-FPM pool,
  queue worker, storage, secrets, or deployment user with DaengtisiaMS, Aish POS, or any other environment.

| Layer | Planned pilot setup |
|-------|---------------------|
| Compute | Dedicated Ubuntu LTS VM (small–medium) |
| Web | Nginx server block `pilot.aish.example` + PHP-FPM pool `aish-pilot` |
| Database | `aish_agentic_pilot` (dedicated PostgreSQL) |
| Cache/queue | Redis prefix `aish:pilot:`, DB index 4; worker `aish-pilot-worker` |
| Storage | Bucket `aish-agentic-pilot` |
| Deploy user | `aish-pilot` |
| App dir | `/srv/aish/pilot` |
| Secrets | `secret/aish/pilot/` (least privilege) |

## 3. Co-hosting exception (if unavoidable)

If pilot is **temporarily** co-hosted with DaengtisiaMS or Aish POS, it **MUST** require an explicit recorded
risk decision **and** full separation on every axis:

| Axis | Required separation |
|------|---------------------|
| Database | Separate database |
| Redis | Separate prefix **and** DB index |
| Directory | Separate application directory |
| Unix user | Separate deployment user |
| PHP-FPM | Separate pool |
| Nginx | Separate server block |
| Secrets | Separate secret path |
| Backup | Separate backup destination |
| Monitoring | Separate monitoring namespace |
| Rollback | Independent rollback |
| Resources | Separate resource limits |
| Network | Separate port/domain isolation |

Absent full separation and a recorded risk decision, co-hosting is **NO-GO**.

## 4. Data policy

- `pilot` holds **approved pilot data under the minimum-data policy** — only fields necessary for the pilot,
  from consented customers of the pilot tenant (Rules 16, 17, 18).
- Raw production data from any other source **MUST NOT** be imported.
- Prohibited healthcare fields (diagnosis, clinical notes, MRN, prescriptions, odontogram, clinical media,
  treatment narrative, insurance, payment-card/bank data) **MUST NOT** be sent to AI or appear in any public
  reply (Rule 18). See [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md).

## 5. External integration behavior

| Integration | Pilot behavior |
|-------------|----------------|
| Email | Real provider, restricted to consented pilot recipients |
| WhatsApp | Real provider, consented pilot only, invitation baseline (Rule 17), human-gated |
| Google | Real Google Business Profile; every review reply **MUST** pass recorded human approval before publish (Rules 06, 18); no gating |
| AI provider | Real provider with guardrails, redaction, structured output, prompt/model versioning, cost/trace; no MED data |

External success **MUST NOT** be reported before provider verification; a failed publish **MUST** keep a
truthful failed state (Rule 18). If Google integration is mock/unavailable, that scope is **BLOCKED**, not a
success.

## 6. Isolation and access

- Tenant/branch **isolation** **MUST** hold on all surfaces (Rule 03); branch-scoped roles see only their
  branch.
- Access control **MUST** be RBAC + branch scope, least privilege, with MFA for privileged roles.
- Pilot roles (Business Owner/Sponsor, Pilot Coordinator/Admin, Branch Manager, Recovery Assignee/CS,
  Reputation Approver) **MUST** be able to operate the console; clinic clinical staff **MUST NOT** be required
  to operate it (Rule 16).

## 7. Operations

| Concern | Pilot plan |
|---------|-----------|
| Logging | Structured, PII-redacted, audit for every important action |
| Monitoring | Full metrics, alerts, traces (Rule 11) |
| Backup | Scheduled backup |
| Restore | Tested restore drill |
| Retention | Pilot-period, configurable |
| Reset | Controlled, audited; never casual |
| Kill switch | Present; manual workflow usable without AI (Rules 05, 17) |

## 8. Readiness gates

Promotion `staging → pilot` requires release approval, migration/backup/smoke/rollback gates, and evidence
(see [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md)). Pilot operational targets
(invitation, delivery, response, completion, triage, SLA, Google disposition, reply latency, structured-AI
validity, recall) are **hypotheses** and **MUST NOT** be reported as achieved without measured evidence
(Rule 19). Hard safety gates (zero cross-tenant exposure, 100% public reply human-approved, no PII/medical
leakage, no duplicate external action) **MUST** all pass.

## 9. Truthful status

The pilot environment is **NOT STARTED** and **NOT DEPLOYED**. "Pilot ready" **MUST NOT** be claimed without
runtime evidence. This plan is a `PLANNED TOPOLOGY — NOT DEPLOYED` specification.

## 10. Cross-reference

Predecessor: [STAGING_PLAN.md](STAGING_PLAN.md). Successor: [PRODUCTION_ENVIRONMENT_PLAN.md](PRODUCTION_ENVIRONMENT_PLAN.md).
Naming: [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md).
