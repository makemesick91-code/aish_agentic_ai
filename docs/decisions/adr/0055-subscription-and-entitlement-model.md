# ADR 0055 — Subscription and Entitlement Model

- **Status:** Accepted (2026-07-14, Asia/Makassar) — SPRINT-SF-05 Subscription & entitlement skeleton; commercial model IN PROGRESS toward GO, business/module features and payment NOT STARTED
- **Owner:** Principal Architect / Security & Privacy Lead
- **Rule:** `.claude/rules/31`, `.claude/rules/03` · **Canonical:** Master Source §72; §45, §46, §36; PRD v1.3.0 §14, §23; rules 31, 03, 04, 07

## Context
The platform needs a foundation to say "what is this tenant entitled to" without duplicating plan logic across the
codebase, without conflating a **commercial** subscription state with a **payment** state, and without ever
overriding a **security** suspension. It also needs tenant-scoped usage metering that is safe to increment more than
once (retries, at-least-once queues). Getting this wrong risks (a) inconsistent entitlement decisions, (b) claiming
a tenant is "paid" with no provider evidence, (c) double-counted usage, and (d) a commercial state masking a
security suspension. Payment, invoicing, tax, and dunning are explicitly out of scope for this foundation.

## Decision
- **Versioned plan catalog.** Plans are a global catalog identified by **`(code, version)`** with lifecycle
  `draft | active | retired`. A plan **version** never silently changes historical meaning; a retired plan **cannot**
  be newly assigned but existing references stay valid.
- **Typed, allowlisted entitlements.** Entitlement keys are explicit and typed (`App\Subscriptions\EntitlementKeys`);
  an **unknown** key fails closed. All entitlement decisions go through **one authoritative** `EntitlementResolver`;
  there is no duplicated plan logic anywhere else. `unknown`, `missing`, and `expired` all fail closed.
- **Guarded subscription state machine.** A tenant subscription has states
  `trialing | active | grace | suspended | cancelled | expired`; transitions are guarded and **invalid transitions
  are rejected**.
- **Idempotent usage metering.** Usage records are tenant-scoped and **idempotent** — a repeated increment does not
  double-count; a negative quantity is refused outside an explicit correction workflow; period boundaries are
  **timezone-aware**. The foundation ships exactly one internal `foundation.verification` meter.
- **Idempotent reconciliation.** `aish:subscription-reconcile` is safe to rerun and emits a transition/notification
  **at most once**.
- **Commercial ≠ payment; security precedence.** No paid/collected state is claimed without provider evidence
  (payment/invoicing/tax/dunning out of scope). A commercial restriction is distinct from a **security suspension**,
  and **security suspension always takes precedence** — a commercial state **must not** override a tenant/user/
  membership security state.

## Alternatives
- **Plan `code` without a `version`** — rejected: a later plan edit would silently rewrite the meaning of existing
  subscriptions; versioning preserves historical intent.
- **Entitlement checks scattered at call sites** — rejected: drift and inconsistent decisions; a single resolver is
  the only authority.
- **Free-form entitlement strings** — rejected: an unknown/typo key must fail closed, which requires a typed
  allowlist.
- **Non-idempotent counters** — rejected: at-least-once delivery would double-count usage; increments must be
  idempotent.
- **Treat commercial state as security state** — rejected: a paid/grace/cancelled state must never suppress or
  override a security suspension.

## Consequences
Every entitlement decision is consistent and fail-closed; usage cannot be double-counted; reconciliation is
rerun-safe; historical subscriptions keep their original plan meaning; and no code path can use a commercial state
to bypass a security suspension. Payment integration later plugs in behind the same resolver without changing
callers.

## Impacts
- **Security:** security suspension precedence prevents a commercial state from re-enabling a suspended tenant/user;
  unknown entitlements fail closed.
- **Privacy:** metering records carry tenant-scoped counters only; no PII/medical content; audit metadata sanitized.
- **Tenant isolation:** subscriptions, entitlements, and usage are tenant-scoped; no cross-tenant read/write.
- **Database:** adds plan/plan-entitlement, tenant-subscription, subscription-event, and usage-record tables; no
  payment/invoice/tax tables (out of scope).
- **Operational:** idempotent reconcile command + guarded transitions give a safe, rerun-tolerant operational
  surface; append-only subscription events give an auditable history.
- **Cost:** one resolver + idempotent counters avoid duplicated work; no billing-provider cost is incurred at this
  foundation stage.

## Verification / fitness function
`tests/Feature/Subscriptions/*`, `tests/Feature/Console/Sf05CommandsTest.php`, and
`tests/Feature/Security/Sf05CrossTenantMatrixTest.php` assert: unknown/missing/expired entitlement fails closed;
single-resolver authority; retired plan not newly assignable but existing refs valid; invalid state transitions
rejected; usage idempotent (no double-count) and negative refused; reconcile idempotent; commercial state never
overrides a security suspension. AFR-160, AFR-161, AFR-162, AFR-163, AFR-164, AFR-165; SC-27, SC-28, SC-29, SC-30,
SC-31, SC-32.

## Related
Requirement: Master Source §72; §45, §46, §36; PRD v1.3.0 §14, §23. Application rules: AFR-160..AFR-165, AFR-169.
Rules: 31, 03, 04, 07. ADRs: 0011, 0015, 0029, 0051, 0052, 0053, 0054, 0056.

## Evidence
`app/Subscriptions/*`, `app/Models/{Plan,PlanEntitlement,TenantSubscription,SubscriptionEvent,UsageRecord}*`,
`app/Console/Commands/*Reconcile*` (forthcoming under SPRINT-SF-05); `docs/governance/foundation-coverage-matrix.md`;
`docs/evidence/sprint-sf-05/` (forthcoming).

## Non-claims
This ADR does not add payment, invoicing, tax, or dunning, does not claim any tenant is paid/collected, and does not
create any business/feature module (`app/Modules/*` remains **NOT STARTED**). It does not claim deployment, pilot, or
production readiness, and does not assert the SPRINT-SF-05 release is merged, tagged, CI-green, or
clean-checkout-verified — those remain **PLANNED** until evidenced.

## Rollback
Single-authoritative resolver, fail-closed entitlements, versioned plans, idempotent metering/reconciliation, and
security-suspension precedence are permanent guarantees; loosening any of them requires an owner-approved Master
Source update. Adding a payment provider behind the resolver is a future ADR + Master Source event.
