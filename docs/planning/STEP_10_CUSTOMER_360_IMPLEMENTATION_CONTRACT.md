# Aish Agentic AI — Step 10: Customer 360 Foundation — Implementation Contract

**Status:** EXECUTION-READY CONTRACT — Step 10 is NOT STARTED (this is the contract, not the implementation)
**Sprint origin:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Related:** ADR 0064 (Unified Customer Identity & Customer 360), ADR 0065 (Experience Event Ledger), ADR 0068
(migration/backfill), rule 34, rules 03/04/07/18/30/32/33;
`docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md`,
`docs/architecture/experience-os/MIGRATION_AND_COMPATIBILITY_STRATEGY.md`
**Canonical repo:** makemesick91-code/aish_agentic_ai

> A coding agent MUST be able to execute Step 10 from this contract without repeating architecture discovery.

---

## 1. Objective

Deliver a tenant-scoped **Customer 360 Foundation**: a canonical `Customer` aggregate, source-identity resolution
(deterministic + suggested), human-approved reversible merge/split with immutable audit, consent history, additive
idempotent backfill of existing Step 8 feedback/survey data, and a Customer 360 read-model — without weakening any
tenant-isolation, security, privacy, review-policy, or release gate, and without altering Step 8 records.

## 2. Scope
- `customers` aggregate (tenant-scoped; ULID public id; branch as provenance only).
- `customer_identities` (source identities: survey, transaction, Google, WhatsApp, email, API; normalized keys; hashed
  where PII; provenance; first/last seen).
- Deterministic identity linking (exact normalized email/phone) + probabilistic **suggestions** (never auto-applied).
- Human-approved **merge** and **split** with `customer_merge_events` immutable ledger + append-only audit.
- Consent & communication-preference history (versioned, append-only).
- Backfill: link existing `feedback_items`/survey responses to customers where a deterministic identity exists.
- Customer 360 read-model (interactions timeline projected from existing feedback events + future ledger).
- Tenant-scoped API + minimal UI (list/detail/merge-approval); permission-gated.
- Entitlement gate + idempotent usage metering; `aish:customer-reconcile`; `aish:verify-step-10`.

## 3. Out of scope
Transaction ingestion runtime (only the identity hooks); Recovery; Google Review; AI; omnichannel; analytics
dashboards; payment. No cross-tenant linking ever. No silent/destructive merge. No AI on customer data in Step 10.

## 4. Domain model
`Customer(id ULID, tenant_id, primary_branch_id?, display_name?, status, created/updated)`;
`CustomerIdentity(id, tenant_id, customer_id, source_type, identity_type[email|phone|external_ref], value_normalized,
value_hash, provenance, confidence, is_deterministic, first_seen_at, last_seen_at, unique(tenant_id, identity_type,
value_hash))`; `CustomerMergeEvent(id, tenant_id, action[merge|split], survivor_id, merged_id, actor_user_id, reason,
snapshot_ref, created_at)` (append-only); `CustomerConsent(id, tenant_id, customer_id, consent_type, accepted bool,
consent_text_version, source, created_at)` (append-only).

## 5. Schema plan (additive migrations only)
New dated migrations under `database/migrations/` for `customers`, `customer_identities`, `customer_merge_events`,
`customer_consents`, and a nullable additive `customer_id` FK on `feedback_items` (nullable; no backfill in the
migration). Indexes: `(tenant_id, status)`, `(tenant_id, identity_type, value_hash)` unique, `(tenant_id,
customer_id)`. No alteration/drop of Step 8 columns.

## 6. Migration & backfill plan
Per `docs/architecture/experience-os/MIGRATION_AND_COMPATIBILITY_STRATEGY.md`: additive migrate → deploy tolerant code
→ queued chunked resumable idempotent backfill linking feedback/survey rows to customers by deterministic identity →
verify + `aish:customer-reconcile` + isolation check → per-tenant feature flag. Unlinked rows remain valid. Backup
before large backfill.

## 7. Customer identity rules
Tenant-scoped only; cross-tenant linking PROHIBITED. Deterministic link on normalized verified email/phone; everything
else is a suggestion requiring human approval. Anonymous survey responses never silently create a customer; IP is not
an identity. Duplicate prevention via unique `(tenant_id, identity_type, value_hash)`.

## 8. Merge & split rules
Merge/split are human-approved, reversible, and produce an immutable `customer_merge_events` row + audit. No silent
destructive merge; a merge preserves both source snapshots so a split can restore. Branch-restricted users cannot
merge across branches they cannot access.

## 9. Experience event projection
The Customer 360 interactions read-model projects from existing `feedback_events` (preserved) and, when the Experience
Event Ledger exists, from ledger events. Projection is idempotent and rebuildable; it does not own or mutate feedback
state (per ADR 0065 and the domain boundary map).

## 10. Permission & tenant rules
New permissions: `customer.view`, `customer.view-contact` (PII contact fields), `customer.manage`, `customer.merge`.
Policy + service-layer defense in depth. Fail-closed tenant context; branch scope enforced. Platform roles grant no
customer data.

## 11. API & UI requirements
Tenant-scoped endpoints: list/search customers (metadata; contact search gated by `customer.view-contact`), customer
detail with interactions timeline, merge-suggestion queue, merge/split approval. Truthful states (loading/empty/
permission-denied). ULID public ids only.

## 12. Audit requirements
Every create/link/merge/split/consent change and PII access is audited (sanitized; no raw PII/medical/secrets). Merge
ledger + audit are append-only.

## 13. Performance requirements
Tenant-scoped queries; indexes as above; no cross-domain N+1; read-model for 360 timeline; backfill within a per-tenant
throughput budget off-peak.

## 14. Testing matrix
Unit: normalization, deterministic-match, suggestion scoring, merge/split reversibility. Feature: link idempotency,
merge approval flow, consent capture, backfill idempotency/resumability, entitlement gate, usage metering. Security:
`tests/Feature/Security/Sf10CrossTenantMatrixTest.php` (cross-tenant/branch, IDOR, identity poisoning, incorrect
merge, PII access gating). Architecture: boundary tests (customer domain owns identity; no cross-domain writes).
Migration integrity + console command tests. Do not reduce existing tests.

## 15. Security review vectors
Cross-tenant/branch leakage; IDOR; identity poisoning; incorrect/destructive merge; PII leakage; MED exclusion; audit
tampering; backfill isolation; stale merge approval; export/contact field minimization. Map to
`docs/security/STEP_9_THREAT_MODEL.md` (T-01..T-06, T-16, T-22).

## 16. Observability
Identity-link confidence distribution; merge/split anomaly alert; backfill progress; projection lag; cross-tenant
anomaly page; reconciliation drift — per `docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`.

## 17. Rollout & 18. Rollback
Rollout: additive migrate → tolerant deploy → backfill → verify → per-tenant flag. Rollback: disable flag; drop unused
additive structures (no Step 8 data loss); tombstone derived rows if needed; source untouched; forward-fix preferred
once live.

## 19. Acceptance criteria
Additive migrations only; Step 8 records unchanged and valid; deterministic-vs-suggested links with provenance; no
silent/destructive merge (reversible + audited); anonymous responses create no identity; cross-tenant linking
impossible; backfill idempotent/resumable; entitlement-gated + metered; permission-aware PII; `aish:verify-step-10`
green on real PostgreSQL 17 + Redis 7; hermetic suite green (no reductions); Pint/PHPStan clean; security review
PASS (no unresolved HIGH/CRITICAL).

## 20. Definition of Done
Code complete + tested locally + security review PASS + Master Source bump + rule (new invariants) + ADR(s) + Step 10
GO/WATCH/NO-GO gate + clean-checkout verification on merged SHA + immutable GO tag + GitHub Release + post-tag
evidence — per rule 09/13/28/33.

## 21. Evidence requirements
`docs/evidence/step-10/` (baseline, runtime verify logs, security review, clean-checkout), `docs/release/STEP_10_*`,
`docs/quality/STEP_10_GO_WATCH_NO_GO.md`, foundation coverage matrix + traceability updates.

## 22. Suggested identity
- Branch: `feature/step-10-customer-360-foundation`
- Verification: `scripts/runtime/verify-step-10.sh` + `php artisan aish:verify-step-10` (wired into `backend-runtime-ci`)
- Draft PR early; ready when local gates pass; merge on exact-SHA CI green.
- GO tag: `aish-agentic-ai-step-10-customer-360-foundation-v1.0.0-go`
- GitHub Release with scope, exclusions, ADRs, test/CI evidence, merge SHA, tag object, known limitations.
