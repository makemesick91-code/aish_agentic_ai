# SaaS Foundation Cost Model (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. No price, quantity, or total in
  this model is a committed or contracted cost; all figures are planning categories and placeholders only.
  Architecture (ADRs 0009–0032, the Application Architecture Baseline, the Application Foundation Rules) is
  cited by name/number in prose only.

---

## Purpose

This cost model enumerates the planning **cost** categories for standing up and operating the SaaS Foundation.
It exists so budgeting can be reasoned about before implementation; it does **not** assert any real price. Each
category is a placeholder to be filled with quoted figures at procurement time. Cost signals feed the
GO/WATCH/NO-GO "acceptable measured cost" criterion in
[SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md), but only once real figures exist.

No figure here may be reported as an actual cost. "Acceptable cost" is judged only against measured spend.

---

## Cost categories

| ID | Category | What it covers | Driver | Cost basis (placeholder) |
|----|----------|----------------|--------|--------------------------|
| COST-SF-01 | Compute / infra | Application servers, workers, scheduler hosts | Instance size × count × hours | TBD — not committed |
| COST-SF-02 | Database (PostgreSQL) | Managed or self-hosted PostgreSQL, IOPS, storage | Storage GB + connections + tier | TBD — not committed |
| COST-SF-03 | Cache/queue (Redis) | Redis for cache and queue | Memory tier + throughput | TBD — not committed |
| COST-SF-04 | Object storage | S3-compatible tenant-scoped storage | Storage GB + requests + egress | TBD — not committed |
| COST-SF-05 | Domain | Domain registration/renewal | Per domain per year | TBD — not committed |
| COST-SF-06 | TLS certificates | TLS for app and API surfaces | Per cert or managed (may be $0 via ACME) | TBD — not committed |
| COST-SF-07 | Backups | Backup storage + retention + restore test runs | Backup GB × retention | TBD — not committed |
| COST-SF-08 | AI provider tokens | LLM/agent token usage (future AI epics, not foundation) | Tokens × model rate | TBD — not committed; foundation ships no AI usage |
| COST-SF-09 | Monitoring/observability | Logging, tracing, metrics, alerting, error tracking (Sentry-class) | Events/traces/retention | TBD — not committed |
| COST-SF-10 | Email delivery | Transactional email channel | Messages sent | TBD — not committed |
| COST-SF-11 | WhatsApp / messaging | WhatsApp survey/invitation delivery (future business epics) | Conversations/messages | TBD — not committed; not in foundation scope |
| COST-SF-12 | CI runtime | CI minutes/runners | Build minutes × concurrency | TBD — not committed |
| COST-SF-13 | Secret management | Secret manager / vault | Secrets × operations | TBD — not committed |

---

## Cost drivers and notes

- **Tenant scale** — most categories scale with active tenants/branches and event volume; the foundation
  itself is low-volume until business features and pilot traffic arrive.
- **AI cost is deferred** — COST-SF-08 and COST-SF-11 are listed for completeness but the SaaS Foundation
  ships no AI or messaging usage; their spend starts only with later business epics.
- **Free-tier possibilities** — TLS (ACME) and some monitoring/CI tiers may be $0; this is not assumed as a
  commitment.
- **Restore-test cost** — COST-SF-07 includes the cost of periodically running and verifying restores
  (EPIC-SF-14), not just storing backups.

---

## Cost governance

- No figure is committed until a real quote is recorded; this model holds categories, not contracts.
- Measured spend, once implementation begins, is compared against these categories; a category trending toward
  an unacceptable level is a **WATCH** signal, and only a measured breach informs a NO-GO.
- AI cost logging and cost limits (Rule 05, Rule 11) apply when AI features are built on this foundation; the
  foundation itself only wires the observability to make cost measurable.
- Cost changes that affect scope or plan structure require a Master Source update (Rule 12).

Application implementation: NOT STARTED. This cost model is a PLANNING BASELINE — NOT IMPLEMENTED.
