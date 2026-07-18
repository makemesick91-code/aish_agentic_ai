---
id: "36"
title: Customer 360 Identity, Merge Reversibility, and Consent Foundation
domain: customer-identity
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §77, §75; §36, §37, §43, §50, §53, §54"
  - "PRD v1.3.0 §9, §14, §16, §23, §24; Agentic Experience OS PRD Addendum v1.0.0"
  - "ADRs 0070, 0071, 0072; ADRs 0063–0068, 0011–0016, 0029, 0051–0060; AFR-250..262; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30, 31, 32, 33, 34"
supersede: "Permanent for Step 10+. Single tenant-scoped customer ownership, the prohibition on cross-tenant linking, verified-only deterministic linking with human-approved suggestions, no-delete fully reversible snapshot-based merge/split, append-only identity/merge/consent history, no-silent-identity-from-anonymous, keyed tenant-bound identity hashing with no plaintext PII in identity rows, the derived non-competing interactions read-model, permission-gated contact PII, additive idempotent resumable backfill preserving Step 8 records, the single authoritative entitlement resolver, idempotent usage metering, Google Review anti-gating, platform-role isolation, and evidence-based release cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees."
---

# Rule 36 — Customer 360 Identity, Merge Reversibility, and Consent Foundation

## Purpose
Keep the Step 10 substrate — the canonical customer aggregate, source-identity resolution, human-approved reversible
merge/split, consent history, the Customer 360 interactions read-model, and the additive backfill of existing Step 8
data — tenant-safe, fail-closed, truthful, auditable, privacy-aware, and free of identity poisoning, silent destructive
merges, or cross-tenant correlation from a clean checkout onward, without weakening any security, privacy,
review-policy, documentation, or release gate, and without altering any Step 8 record.

## Scope
The customer aggregate and its lifecycle; source identities, normalization, hashing, and deterministic-vs-suggested
linking; merge and split; consent and communication-preference history; the Customer 360 interactions read-model;
customer permissions and PII gating; customer entitlement and usage metering; the additive backfill and reconciliation;
and customer audit. Applies to `app/Customers/**`, `app/Models/Customer*`,
`app/Http/Controllers/Tenancy/Customers/**`, `app/Http/Requests/Customers/**`, `app/Policies/Customer*`,
`app/Enums/Customer*`, `app/Jobs/Customers/**`, `app/Console/Commands/{CustomerReconcileCommand,VerifyStep10Command}.php`,
related `database/` and
`tests/{Feature/Customer360,Feature/Security/Sf10*,Feature/Audit/Sf10*,Unit/Customers,Architecture/Sf10*,Feature/Console}`.
This is platform-core in top-level `app/` namespaces (ADR 0070), not inside `app/Modules/`; business modules remain
**NOT STARTED**.

## Rules

### Ownership and single source of truth
- The `Customer` aggregate **MUST** be tenant-scoped and owned solely by Customer Profile & Identity Resolution; that
  domain **MUST** be the only writer of `customers`, `customer_identities`, `customer_merge_events`, and
  `customer_consents`. Other domains **MUST** reference a customer id and **MUST NOT** create, merge, or mutate identity.
- A parallel customer master, duplicate customer profile table, competing customer identifier system, second
  identity-resolution engine, separate consent store, separate contact-preference store, or competing customer timeline
  **MUST NOT** be created (rule 34, AFR-211/212).
- Cross-tenant identity linking is **PROHIBITED**. A customer, identity, consent record, merge event, and every
  read-model query **MUST** be tenant-scoped and **MUST** fail closed without a validated `TenantContext`.
- Platform roles **MUST NOT** imply customer access; a platform role grants no tenant customer data.
- Branch is **provenance only** on a customer; it **MUST NOT** act as a cross-tenant key, and branch-restricted users
  **MUST** be limited to customers and merges within their reachable branch scope.

### Identity normalization, hashing, and matching
- Normalization **MUST** be centralized, explicit, and versioned (`IdentityNormalizer`); callers **MUST NOT** normalize
  inline. Email local parts **MUST** be preserved verbatim (no dot-stripping or `+tag` removal); phone values **MUST**
  resolve to unambiguous E.164 or be refused as a deterministic identity.
- Identity values **MUST** be stored as a **keyed hash bound to the tenant** (HMAC with an `APP_KEY`-derived pepper
  concatenated with `tenant_id`); a plain unsalted digest **MUST NOT** be used. The pepper **MUST NOT** be logged,
  committed, or placed in audit metadata.
- Plaintext email/phone **MUST NOT** be persisted on `customer_identities`; displayable contact values live on the
  customer record behind `customer.view-contact`.
- Duplicate identities **MUST** be prevented by a database unique `(tenant_id, identity_type, value_hash)` index.
- A deterministic link **MUST** require a **verified** identity; an unverified email/phone **MUST** produce only a
  suggestion. Probabilistic matches **MUST NEVER** be auto-applied and **MUST** require human approval with recorded
  provenance and confidence.
- Anonymous responses **MUST NOT** silently create or link a customer; an IP address **MUST NOT** be treated as an
  identity (rule 32). Free-text answers, medical/`MED` data, and secrets **MUST NOT** become identity values.
- The normalizer version **MUST** be recorded per identity row; a normalization change **MUST** ship as a new version
  plus an additive idempotent resumable re-hash backfill, never an in-place rewrite.

### Merge and split
- Merge **MUST NOT** delete. The non-surviving customer **MUST** be retained with status `merged` and a
  `merged_into_customer_id` survivor pointer; its identities and consent history **MUST** be preserved.
- Every merge **MUST** append an immutable `customer_merge_events` row containing a **sanitized** snapshot of both
  customers' pre-merge state and the exact set of moved identity/feedback ids; a split **MUST** restore from that
  recorded snapshot and **MUST NOT** re-derive the reversal from current state.
- A split **MUST** be a new appended event referencing the merge it reverses; the original merge row **MUST NOT** be
  updated or deleted. Merge/split history **MUST** be append-only (no `updated_at`; update/delete blocked at the model
  layer) and **MUST NOT** be deletable.
- Merge and split **MUST** require human approval and the `customer.merge` permission, and **MUST** be refused unless
  the actor can reach **both** customers' branch scopes and both customers belong to the current tenant.
- Merging a customer that is already `merged` **MUST** be refused. Bulk merge **MUST NOT** exist in Step 10.
- Concurrent merges **MUST** be guarded by deterministic-order row locking so no partially-merged state is observable.
- Merge snapshots and audit metadata **MUST** be sanitized — ids, provenance, counts, and status only; never raw
  email/phone, free text, tokens, secrets, or medical data.

### Consent and privacy
- Consent and communication-preference history **MUST** be versioned and append-only, recording the consent text
  version and source. Survey completion **MUST NOT** be treated as marketing consent (rule 32).
- Contact PII **MUST** be gated by `customer.view-contact`; contact search **MUST** be excluded from the query for users
  without it. `MED`/medical data **MUST NEVER** be stored on, or exposed through, a customer record (rule 18).
- PII access **MUST** be audited; audit metadata **MUST** be sanitized and **MUST NOT** contain raw PII, free text,
  tokens, or secrets.
- Erasure **MUST** purge PII while retaining minimized non-PII ledger rows; retention **MUST** be configurable and legal
  hold **MUST** override purge.

### Interactions read-model
- The Customer 360 interactions timeline **MUST** be a derived read-only projection assembled from sources the customer
  domain does not own (the preserved Step 8 `feedback_items`/`feedback_events`, and later the Experience Event Ledger);
  it **MUST NOT** be a materialized competing timeline and **MUST NOT** write to any feedback table.
- The read-model **MUST** be permission-filtered at read time, **MUST** be paginated (never unbounded), and **MUST**
  remain correct after a merge or a merge reversal.
- The Step 8 immutable feedback timeline **MUST** be preserved as authoritative and **MUST NOT** be replaced or
  destructively migrated (rule 34, AFR-213).

### Entitlement, usage, and backfill
- Customer entitlement decisions **MUST** use the single authoritative resolver through one guard; an unknown or
  ungranted key **MUST** fail closed; a commercial state **MUST NOT** override a security suspension.
- Customer usage meters **MUST** be tenant-scoped and idempotent; a retry **MUST NOT** double-count.
- Migrations **MUST** be additive only; no Step 8 column **MUST** be altered or dropped and Step 8 records **MUST**
  remain valid and unchanged. Unlinked feedback **MUST** remain valid.
- Backfill and reconciliation (`aish:customer-reconcile`) **MUST** be queued, chunked, resumable, idempotent,
  tenant-scoped, and non-destructive, and **MUST NOT** become a second uncontrolled identity write path.

### Review policy and AI boundary (preserved)
- A customer's identity, consent, merge state, or recovery history **MUST NEVER** determine whether Google Review access
  is shown; review gating remains **prohibited** and all eligible customers **MUST** retain equal review access
  (rules 06, 18).
- Step 10 **MUST NOT** send customer data, identity values, or feedback free text to an AI provider, and **MUST NOT**
  perform AI-assisted matching; identity resolution **MUST** remain deterministic, rule-based, and explainable
  (rules 05, 18).

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These remain binding but are scheduled later; Step 10 **MUST NOT** be read as delivering them: Customer Recovery,
tickets, SLA, and escalation (Step 11); the Experience Event Ledger runtime; transaction and service-event ingestion;
Google OAuth, review sync, and reply; AI sentiment/severity/summary, agent orchestration, and RAG; omnichannel
conversations and channel adapters; knowledge base; advanced analytics and ROI; public API, webhooks, and marketplace;
payment and billing; and deployment.

## Required checks
- `tests/Feature/Customer360/*`, `tests/Unit/Customers/*`, `tests/Feature/Security/Sf10CrossTenantMatrixTest.php`,
  `tests/Feature/Audit/Sf10AuditTest.php`, `tests/Feature/Sf10MigrationIntegrityTest.php`,
  `tests/Feature/Console/Sf10CommandsTest.php`, `tests/Architecture/{Sf10BoundariesTest,TenancyBoundariesTest}.php`;
  the consolidated Step-10 GO/WATCH/NO-GO gate; a clean-checkout Step 10 verification on the merged SHA
  (`scripts/runtime/verify-step-10.sh` / `php artisan aish:verify-step-10`); `scripts/docs/check-step10-coverage.sh`;
  `scripts/docs/secret-scan.sh`; the `backend-runtime-ci` gate (rules 28, 29).

## Evidence
- `app/Customers/**`, `app/Models/Customer*`, `app/Http/Controllers/Tenancy/Customers/**`,
  `app/Policies/Customer*`, `app/Jobs/Customers/**`; `tests/**/Sf10*`, `tests/Feature/Customer360/**`,
  `tests/Unit/Customers/**`; `docs/governance/foundation-coverage-matrix.md`; `docs/security/STEP_10_THREAT_MODEL.md`;
  `docs/evidence/step-10/`; `docs/release/STEP_10_*`.

## Related canonical sections
- Master Source §77, §75; §36, §37, §43, §47, §50, §53, §54, §62; PRD v1.3.0 §9, §14, §16, §23, §24; Agentic Experience
  OS PRD Addendum v1.0.0; ADRs 0070–0072; ADRs 0063–0068, 0011–0016, 0029, 0051–0060; AFR-250..262; rules 02, 03, 04,
  05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30, 31, 32, 33, 34.

## Supersession
Permanent for Step 10+. Single tenant-scoped customer ownership, prohibited cross-tenant linking, verified-only
deterministic linking with human-approved suggestions, no-delete fully reversible snapshot-based merge/split,
append-only identity/merge/consent history, no-silent-identity-from-anonymous, keyed tenant-bound hashing with no
plaintext PII in identity rows, the derived non-competing interactions read-model, permission-gated contact PII,
additive idempotent resumable backfill preserving Step 8 records, the single authoritative entitlement resolver,
idempotent usage metering, Google Review anti-gating, platform-role isolation, and evidence-based release are
permanent; superseded only by a higher-version Master Source update that preserves these guarantees.
