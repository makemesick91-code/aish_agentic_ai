# Step 10 — Customer 360 Foundation: Independent Security Review

**Scope:** `app/Customers/**`, `app/Models/Customer*`, `app/Policies/CustomerPolicy.php`,
`app/Http/Controllers/Tenancy/Customers/**`, `app/Http/Requests/Customers/**`,
`app/Http/Middleware/EnsureCustomer360Enabled.php`, `app/Console/Commands/CustomerReconcileCommand.php`,
`database/migrations/2026_07_18_1000*`, `routes/web.php` (Customer 360 group), `resources/views/customers/*`,
`app/Authorization/{Permissions,Roles}.php` (customer additions). ~2,900 LoC.
**Method:** independent adversarial review in a separate context, driven by `docs/security/STEP_10_THREAT_MODEL.md`
(S10-T01..T20) and rule 36. Each finding was verified against the actual enforcing code path before being reported.
**Round 1 verdict:** FAIL — 0 Critical, **2 High**, **1 Medium**, 2 Low, 2 Informational.
**Round 2 verdict (after fixes):** **PASS** — no unresolved Critical/High/Medium.

## Findings and dispositions

### F-1 — HIGH — Customer 360 timeline bypassed Step 8 branch scoping · **FIXED**
`CustomerInteractionsReadModel::interactions()`/`summary()` applied only the content-permission filter and never
`FeedbackBranchScope`. Because a customer with a NULL `primary_branch_id` is deliberately tenant-wide visible, a
branch-restricted viewer could open such a customer and receive that customer's feedback — and, holding
`FEEDBACK_VIEW_CONTENT` via `FEEDBACK_BRANCH_OPS`, its content — from branches they hold no grant for. Counts and
first/last timestamps in `summary()` leaked the same way.
**Fix:** `FeedbackBranchScope::apply()` is now applied to both the interactions query and the summary base query, with
`TenantContext` injected into the read-model.
**Regression:** `Sf10CrossTenantMatrixTest::test_the_interactions_timeline_is_branch_scoped` — a branch-restricted
manager sees exactly the one reachable item, and `feedback_count` discloses only that one.

### F-2 — HIGH — `split()` never checked branch reachability · **FIXED**
`merge()` correctly required reaching both customers, but `CustomerMergeService::split()` had no branch check at all,
`CustomerMergeController::destroy()` resolved only the survivor, and `CustomerPolicy::split()` took a single customer.
An actor with `customer.merge` on a branch-restricted membership could reverse a merge whose merged customer sat in an
unreachable branch, rewriting identity state outside their scope and stripping history off the survivor.
**Fix:** `CustomerPolicy::split()` now mirrors `merge()` and takes the pair; the controller resolves both customers and
authorizes the pair; and `CustomerMergeService::split()` performs the same `canReach` guard on both rows as
defence in depth.
**Regression:** `Sf10CrossTenantMatrixTest::test_a_branch_restricted_member_cannot_reverse_a_merge_involving_an_unreachable_customer`.

### F-3 — MEDIUM — Unescaped `display_name` in the page heading · **FIXED**
`@section('heading', $customer->display_name ...)` is rendered by `@yield('heading')`, which does not escape. Not
exploitable as shipped (no current path writes `display_name`), but the resolver's public signature accepts an
arbitrary string and this is precisely the domain that ingests untrusted-origin values.
**Fix:** the heading is now wrapped in `e()`.

### F-4 — LOW — Concurrent split could append a duplicate reversal event · **FIXED**
The `alreadyReversed` check ran before `lockPair()`, so two concurrent reversals could both observe "not yet reversed"
and append two `split` rows for one merge. Data converged, but the append-only ledger would falsely claim the merge was
reversed twice.
**Fix:** the check moved to after the rows are locked, so concurrent reversals serialize and the second is refused.

### F-5 — LOW — Resolver could re-link to an `Erased` customer · **FIXED**
`survivorOf()` followed only `merged` status; an `Erased` customer would receive new identity links and a refreshed
`last_seen_at`, resurrecting a purged profile. Not reachable today (no purge writer ships in Step 10) but a trap for
the erasure step.
**Fix:** the resolver now returns an anonymous resolution when the resolved customer is not linkable.

### F-6 — INFORMATIONAL — Query-builder updates bypass model hooks · **HARDENED**
`CustomerMergeService` moves links via the query builder, which does not fire the model `updating` guards. Correct as
written (only mutable columns move), but it left ADR 0071 advisory against future code.
**Fix:** a PostgreSQL `CHECK` constraint (`customer_identities_no_plaintext_pii`) now enforces
"PII identity ⇒ `value_normalized IS NULL`" at the database, so the invariant holds even against a raw write. SQLite
cannot add a CHECK via ALTER, so the hermetic suite continues to rely on the model-layer guard plus
`Sf10MigrationIntegrityTest`.

### F-7 — INFORMATIONAL — `is_verified`/`is_deterministic` were mass-assignable · **HARDENED**
No caller passed request data, but these two fields decide whether an identity links automatically.
**Fix:** both removed from `$fillable`; the resolver now sets them explicitly after its own verification gate.

## Vectors attacked that held (no defect found)
Keyed HMAC hashing with tenant-bound key derivation, domain separation, base64 `APP_KEY` decoding, and a hard throw on
an empty key · `hash_equals` comparison · fail-closed `TenantScope` on all four models with **zero**
`withoutGlobalScope`/`DB::raw`/`whereRaw` anywhere in Step 10 · composite `(tenant_id, customer_id)` FKs making a
cross-tenant link structurally impossible · consent merge-chain folding (bounded at depth 16; correctly stops applying
after a split) with absent-is-not-permission and do-not-contact precedence · out-of-order reversal refusal ·
`restoredStatus()` restoring the recorded pre-merge status · sanitized snapshots (ids/status/`has_contact_*` booleans
only) · append-only enforcement with `UPDATED_AT = null` on both ledgers · no plaintext PII reachable on an identity
row · audit metadata limited to counts/ids/provenance · exception messages never echoing the offending value ·
backfill non-destructive, resumable, and unable to overwrite an existing link · no `{!! !!}` in either view ·
authorization asserted before entitlement in every controller action · contact search excluded from the query (not
merely from output) without `customer.view-contact` · pagination bounded at 100 in both directions.

## Post-fix evidence
- Hermetic suite: **454 passed / 2003 assertions** (2 new regressions added).
- `vendor/bin/pint --test`: passed · `vendor/bin/phpstan analyse`: no errors.
- `scripts/runtime/verify-step-10.sh`: **STEP 10 VERIFICATION: PASS** on real PostgreSQL 17.10 + Redis 7, including the
  Step 6, SF-05, Step 7, and Step 8 real-infra regressions, backfill double-run idempotency, and the secret/PII leak
  scans.
- `scripts/docs/validate.sh`: ALL GATES PASSED.

## Residual risk (accepted, documented)
Pepper/normalizer rotation tooling is not shipped (version columns exist to make it possible) · merged customer rows
are retained indefinitely by design, trading bounded storage for recoverability and audit truth · suggestion scoring is
minimal in Step 10 and must remain human-approved · no email/phone ownership-verification channel exists, so "verified"
means the source already proved control (a redeemed invitation).

**VERDICT: PASS — no unresolved Critical, High, or Medium findings.**
