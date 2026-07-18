# Step 10 — Customer 360 Foundation: Release Manifest

**Canonical:** Master Source v2.13.0 §77 · PRD v1.3.0 (unchanged) · ADRs 0070–0072 · AFR-250..262 · rule 36 · D-036
**Target GO tag:** `aish-agentic-ai-step-10-customer-360-foundation-v1.0.0-go`
**Baseline:** branched from canonical `main` at `2eb328b` (after the Autonomous Execution & Tooling Governance
post-tag evidence sync, PR #26)

## 1. Scope delivered
- Canonical tenant-scoped `Customer` aggregate as **platform-core** (`app/Customers/**`, `app/Models/Customer*`),
  single-writer owned by Customer Profile & Identity Resolution.
- Source-identity resolution: centralized versioned normalization; **keyed tenant-bound HMAC** hashing with no
  plaintext PII on identity rows; verified-only deterministic linking; probabilistic matches remain human-approved
  suggestions; anonymous sources create nothing; DB-enforced duplicate prevention.
- **No-delete, fully reversible** merge/split with an append-only sanitized snapshot recording the exact moved id set;
  deterministic-order row locking; no bulk merge.
- Append-only versioned consent whose resolution folds the merge chain; do-not-contact precedence; absent decision is
  never permission.
- **Derived, non-materialized**, permission-aware Customer 360 interactions read-model over the preserved Step 8
  sources.
- Tenant UI (directory + profile), permissions, entitlement gating with idempotent metering, and sanitized audit.
- Additive migrations + idempotent, chunked, resumable, non-destructive backfill (`aish:customer-reconcile`).
- Verification: `aish:verify-step-10` (32 checks) + `scripts/runtime/verify-step-10.sh`, wired into
  `backend-runtime-ci`.

## 2. Explicitly NOT delivered (remain NOT STARTED)
Step 11 Customer Recovery OS · transaction and service-event ingestion · Experience Event Ledger runtime · Google
OAuth/Review sync and reply · AI sentiment/severity/summary, agent orchestration, RAG · omnichannel channel adapters ·
knowledge base · advanced analytics/ROI · public API, webhooks, marketplace · payment/billing · deployment, pilot
readiness, pilot runtime, production readiness. No domain is owned; nothing is deployed.

## 3. Artifacts
**Governance:** `docs/decisions/adr/0070..0072`, `.claude/rules/36-*`, `docs/security/STEP_10_THREAT_MODEL.md`,
`docs/quality/STEP_10_GO_WATCH_NO_GO.md`, Master Source §77 + v2.13.0 changelog, AFR-250..262, D-036, version matrix,
foundation coverage matrix.
**Schema:** `database/migrations/2026_07_18_100001..100005` (additive only).
**Code:** `app/Customers/**`, `app/Models/Customer*`, `app/Policies/CustomerPolicy.php`,
`app/Http/Controllers/Tenancy/Customers/**`, `app/Http/Requests/Customers/**`,
`app/Http/Middleware/EnsureCustomer360Enabled.php`, `app/Console/Commands/{CustomerReconcile,VerifyStep10}Command.php`,
`resources/views/customers/*`, routes, permissions, entitlement + meter keys.
**Tests:** `tests/Unit/Customers/*`, `tests/Feature/Customer360/*`, `tests/Feature/Security/Sf10*`,
`tests/Feature/Audit/Sf10*`, `tests/Feature/Sf10MigrationIntegrityTest.php`, `tests/Feature/Console/Sf10*`,
`tests/Architecture/Sf10BoundariesTest.php`.
**Gates:** `scripts/docs/check-step10-coverage.sh` (registered in `scripts/docs/validate.sh`),
`scripts/runtime/verify-step-10.sh`, `aish:verify-step-10` step in `.github/workflows/pr-ci.yml`.

## 4. Local evidence (recorded at code-complete)
- Hermetic suite: **452 passed / 1998 assertions** (baseline before Step 10: 354).
- `vendor/bin/pint --test`: passed. `vendor/bin/phpstan analyse`: no errors.
- `aish:verify-step-10`: **32/32 checks** green against real PostgreSQL 17.10 + Redis 7.
- `scripts/runtime/verify-step-10.sh`: **STEP 10 VERIFICATION: PASS**, including the Step 6, SF-05, Step 7, and
  Step 8 real-infra regressions and a double-run backfill idempotency check.
- CI validators: `validate-ci-topology.sh`, `test-change-classifier.sh`, `validate-workflow-security.sh` all pass.

## 5. Release evidence (filled only from actual results)
| Item | Value |
|---|---|
| Implementation PR | _pending_ |
| Authoritative Full CI run / SHA | _pending_ |
| Merge commit SHA | _pending_ |
| Clean-checkout verification on merged SHA | _pending_ |
| Annotated GO tag object / peeled commit | _pending_ |
| GitHub Release | _pending_ |
| Post-tag evidence PR | _pending_ |
| Independent security review verdict | _pending_ |

## 6. Rollback
Disable the per-tenant `customer-360.enabled` entitlement (the surface then fails closed). The schema is additive, so
reverting the code leaves Step 8 fully functional: `feedback_items.customer_id` is nullable and unread by Step 8 logic,
and the four customer tables are unreferenced. Forward-fix is preferred once live. No destructive rollback path is
required because nothing in Step 10 mutates a Step 8 record's own state.

## 7. Truthful status
The Step 10 GO tag, once created, attests **Customer 360 foundation readiness only** — not Customer Recovery, not
deployment, not pilot readiness, not production readiness, and not that any domain is owned.
