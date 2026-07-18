# ADR 0070 — Customer 360 Platform-Core Placement and Interactions Read-Model

- **Status:** Accepted (2026-07-18, Asia/Makassar) — Step 10 Customer 360 Foundation implementation decision
- **Owner:** Principal Architect / Customer Profile & Identity Resolution
- **Rule:** `.claude/rules/36`, `.claude/rules/34`, `.claude/rules/30`, `.claude/rules/33` · **Canonical:** Master Source §77, §75, §36; PRD v1.3.0; rules 36, 34, 03, 04, 07, 18, 30, 32, 33

## Context
ADR 0064 locked *what* Customer 360 must guarantee (single tenant-scoped `Customer` aggregate, deterministic-vs-suggested
links, human-approved reversible merge/split, consent history, additive backfill) but not *where* the implementation
lives or *how* the Customer 360 interactions timeline is produced. Steps 6–8 established a precedent: platform-core
capabilities that every future business module depends on live in **top-level `app/` namespaces** (`app/Tenancy`,
`app/Surveys` per ADR 0057, `app/Feedback` per ADR 0060), not inside `app/Modules/`. Customer identity is consumed by
Recovery (Step 11), Google Review, conversations, and AI, so placing it inside a business module would invert the
dependency direction and create the duplicate-ownership failure ADR 0063 prohibits.

Separately, the Customer 360 interactions timeline must show a customer's feedback history without owning or mutating
feedback state (ADR 0065, domain boundary map). The Experience Event Ledger does not exist yet, so the read-model must
project from the **existing preserved** `feedback_events` / `feedback_items` without a competing event store.

## Decision
- Customer 360 is **platform-core** and lives in top-level namespaces: `app/Customers/**` (services), `app/Models/Customer*`
  (aggregate + identity + merge ledger + consent), `app/Http/Controllers/Tenancy/Customer/**`, `app/Policies/Customer*`,
  `app/Enums/Customer*`. It is **not** placed under `app/Modules/`; business modules remain NOT STARTED.
- Customer Profile & Identity Resolution is the **single writer** of `customers`, `customer_identities`,
  `customer_merge_events`, and `customer_consents`. No other domain may write them; consumers reference `customer_id`.
- The Customer 360 interactions timeline is a **derived, read-only projection**, not a stored competing timeline. It is
  assembled on read by `CustomerInteractionsReadModel` from sources the customer domain does **not** own
  (`feedback_items` + preserved `feedback_events`), joined only through the additive nullable `feedback_items.customer_id`
  link. The projection performs **no writes** to any feedback table.
- Because the read-model is derived and stateless, it is inherently **idempotent and rebuildable**: correcting a link
  (or reversing a merge) immediately yields a correct timeline with no reprojection job and no stale materialized rows.
- When the Experience Event Ledger lands, it becomes an **additional** source for the same read-model interface without
  changing the customer aggregate or requiring a destructive migration (ADR 0065, ADR 0068).
- Timeline entries are **permission-filtered at read time**: contact/PII fields require `customer.view-contact`; free-text
  feedback content remains gated by the Step 8 `feedback.view-content` permission and is never widened by Customer 360.

## Alternatives
- **Place Customer 360 in `app/Modules/Customer`** — rejected: platform-core capabilities consumed by every later module
  must not sit behind a module boundary; inverts dependency direction and contradicts the ADR 0057/0060 precedent.
- **Materialize a `customer_interactions` table now** — rejected: creates a second event history competing with the Step 8
  timeline (prohibited by ADR 0063/0065), needs reprojection jobs, and can go stale after a merge reversal.
- **Let the customer domain write `feedback_items` beyond the additive link** — rejected: violates single-writer ownership.
- **Wait for the Experience Event Ledger before shipping any timeline** — rejected: blocks Step 10 on unscheduled work;
  the read-model interface makes the ledger purely additive later.

## Consequences
Step 11 Customer Recovery and all later Experience OS modules consume one canonical customer identity through a stable
namespace and a stable read-model interface. The timeline is always consistent with its sources because it is derived,
at the cost of a read-time join that must stay indexed and paginated (see Impacts → Database).

## Impacts
- **Security:** single-writer ownership prevents identity mutation from other domains; read-model filters by permission
  at read time so a widened feedback query cannot leak contact PII.
- **Privacy:** contact fields are gated by `customer.view-contact`; MED/free-text content is never copied into customer
  records; no PII is duplicated into a projection table.
- **Tenant isolation:** every customer table and every read-model query is tenant-scoped; the read-model inherits the
  fail-closed `TenantContext` and cannot be executed without it.
- **Database:** additive only — `customers`, `customer_identities`, `customer_merge_events`, `customer_consents`, plus a
  nullable `feedback_items.customer_id`. No Step 8 column is altered or dropped. Requires indexes
  `(tenant_id, customer_id)` on `feedback_items` and `(tenant_id, status)` on `customers` to keep the derived timeline
  bounded; timeline reads are paginated, never unbounded.
- **Operational:** no projection lag, no reprojection backlog, and no DLQ for the timeline, because nothing is
  materialized; reconciliation concerns only identity links, handled by `aish:customer-reconcile`.
- **Cost:** negligible; a bounded indexed read-time join replaces a materialized projection and its rebuild jobs.

## Verification / fitness function
`tests/Architecture/Sf10BoundariesTest.php` asserts customer models live in the platform-core namespace, that no
non-customer class writes a customer table, and that the customer domain writes no feedback table.
`tests/Feature/Customer360/CustomerInteractionsReadModelTest.php` asserts derivation, permission filtering, pagination,
and post-merge correctness. `php artisan aish:verify-step-10` re-proves it on real PostgreSQL 17 + Redis 7. AFR-250,
AFR-251, AFR-252, AFR-262.

## Related
Requirement: Master Source §77, §75, §36; PRD v1.3.0; `docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`
§2, §9, §11. Rules: 36, 34, 03, 04, 07, 18, 30, 32, 33. ADRs: 0057, 0060, 0063, 0064, 0065, 0068.

## Evidence
`app/Customers/**`, `app/Models/Customer*.php`, `tests/Architecture/Sf10BoundariesTest.php`,
`tests/Feature/Customer360/**`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-10/`.

## Non-claims
Creates no Experience Event Ledger, no transaction/service-event ingestion runtime, no recovery capability, no AI on
customer data, and no deployment. Does not claim pilot or production readiness. Step 11 Customer Recovery OS remains
NOT STARTED until its own evidence exists.

## Rollback
Platform-core placement, single-writer customer ownership, and the derived non-materializing interactions read-model are
permanent for Step 10+; materializing the timeline or moving customer ownership requires a new ADR plus an owner-approved
Master Source update that preserves single-writer ownership and the Step 8 timeline.
