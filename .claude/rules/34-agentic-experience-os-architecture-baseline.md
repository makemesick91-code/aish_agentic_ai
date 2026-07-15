---
id: "34"
title: Agentic Experience OS Architecture Baseline
domain: architecture-governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §75; §34–§42, §35, §36, §47, §57, §62"
  - "PRD v1.3.0; Agentic Experience OS PRD Addendum v1.0.0"
  - "ADRs 0063–0068; ADRs 0009–0016, 0019, 0023, 0028, 0029, 0051–0062; AFR-211..238; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30, 31, 32, 33"
supersede: "Permanent for Step 9+. Single-writer domain ownership and source-of-truth boundaries, the customer-identity/feedback-lifecycle/timeline ownership rules, additive-not-replacing Experience Event Ledger, provider-neutral channel adapters with truthful states and no-mock-integration-claim, bounded AI tool actions with mandatory high-risk human approval + cost ceilings + kill switch + no-duplicate-external-action, additive/idempotent/resumable/reversible migration with Step 8 preservation, and truthful architecture-only status cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees."
---

# Rule 34 — Agentic Experience OS Architecture Baseline

## Purpose
Lock the Step 9 architecture re-baseline for expanding Aish Agentic AI into an Agentic Experience OS so that Wave 1–3
work (Customer 360, recovery, reputation, conversations, AI) proceeds without reopening fundamentals, without duplicate
domain ownership, without weakening tenant isolation, and without any false implementation/deployment claim.

## Scope
Domain boundaries and source-of-truth ownership; the Customer 360 identity model; the Experience Event Ledger and its
relationship to the Step 8 Feedback Timeline; the provider-neutral channel-adapter contract; the AI tool-permission and
human-approval contract; and the additive migration/backfill/rollout strategy. Applies to
`docs/architecture/experience-os/**`, `docs/product/capability-inventory/**`, `docs/product/competitive/**`,
`docs/product/EXPERIENCE_OS_ROADMAP.md`, `docs/product/AGENTIC_EXPERIENCE_OS_PRD_ADDENDUM.md`,
`docs/security/STEP_9_THREAT_MODEL.md`, `docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`,
`docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`, ADRs 0063–0068, and AFR-211..238. This is
architecture/governance LOCK only — no application domain is implemented in Step 9; business modules remain
**NOT STARTED**.

## Rules

### Domain boundaries and source of truth
- Every major capability **MUST** have a single owning domain and a single source of truth per
  `docs/architecture/experience-os/DOMAIN_BOUNDARY_MAP.md`; a domain **MUST NOT** write another domain's tables;
  cross-domain work **MUST** use a contract, application service, or typed versioned domain event (AFR-211).
- Customer identity **MUST** be owned only by Customer Profile & Identity Resolution; Survey/Feedback/Recovery/Review/
  Conversation **MUST** reference a customer id and **MUST NOT** create/merge/mutate it. Feedback item lifecycle **MUST**
  be owned only by Feedback Operations; `resolved`/`closed` are **NOT** recovery outcomes (AFR-212).
- The Step 8 immutable Feedback Timeline **MUST** be preserved as authoritative; the Experience Event Ledger **MUST** be
  additive and **MUST NOT** replace or destructively migrate it (AFR-213).
- Every cross-boundary interaction **MUST** carry tenant/branch context and **MUST** cross only references and minimized
  non-sensitive attributes — never raw free text, MED data, secrets, or tokens (AFR-214).

### Customer 360 identity
- The `Customer` aggregate **MUST** be tenant-scoped; cross-tenant linking is **PROHIBITED** (AFR-215).
- Deterministic links **MAY** be auto-applied; probabilistic matches **MUST** be human-approved suggestions with
  recorded provenance/confidence (AFR-216). Merge/split **MUST** be human-approved, reversible, and immutably audited;
  **no silent destructive merge** (AFR-217).
- Anonymous responses **MUST NOT** silently create a customer; consent history **MUST** be versioned/append-only; survey
  completion is **NOT** marketing consent (AFR-218). Erasure/retention/legal-hold **MUST** be honored and Step 8
  backfill **MUST** be additive/idempotent/resumable/non-destructive (AFR-219).

### Experience Event Ledger
- Events **MUST** carry ULID identity, tenant/branch context, subject + sanitized actor, source domain, and a typed
  versioned type with distinct occurred_at/recorded_at (AFR-220). Delivery **MUST** be idempotent; ordering **MUST** be
  per-subject monotonic with **no** global total order (AFR-221). Payloads **MUST** be minimized + PII/MED-redacted and
  append-only (AFR-222). Projections **MUST** be idempotent/rebuildable with DLQ + reconcile + backfill markers
  (AFR-223); schema evolution **MUST** be additive/versioned and tenant-scoped (AFR-224).

### Channel adapters
- Connections/credentials **MUST** be tenant-owned + encrypted (rotation; no plaintext refresh token) with a
  provider-independent conversation model (AFR-225). Webhooks **MUST** be signature-verified + replay-protected;
  inbound/outbound **MUST** be idempotent (AFR-226). States **MUST** be truthful and a mock **MUST NOT** be claimed as
  integration success (AFR-227). Bounded retry + DLQ + reconciliation, rate limits, private MIME-validated attachments,
  cost metering, and tenant/branch routing **MUST** hold (AFR-228). A circuit breaker **MUST** fall back to manual; one
  provider's failure **MUST NOT** break Feedback Operations or Customer Recovery; channel routing **MUST NEVER** gate
  Google Review access (AFR-229).

### AI tool permission and approval
- AI tools **MUST** be an explicit allowlisted registry, per-tool-permission-gated, tenant/branch-scoped, with enforced
  structured output; customer content **MUST NOT** steer tool calls (AFR-230). High-risk actions **MUST** require human
  approval; forbidden actions **MUST NEVER** be auto-executed (AFR-231). Cost ceilings + metering + bounded
  timeout/retry + duplicate-action prevention + correlation/trace **MUST** hold (AFR-232). Guardrails +
  prompt-injection defense + PII/MED redaction + a kill switch **MUST** exist and manual workflows **MUST** remain
  operable without AI (AFR-233). An adversarial evaluation dataset + quality gates **MUST** pass before release; autonomy
  **MUST** follow manual → semi-automated → approved automation → limited autonomy (AFR-234).

### Migration and rollout
- Migrations **MUST** be additive-only with no history reset; Step 8 records **MUST** remain valid; no destructive
  production backfill (AFR-235). Backfills **MUST** be idempotent/queued/chunked/resumable with visibility + DLQ + an
  idempotent reconcile (AFR-236). Capabilities **MUST** be per-tenant flagged (default off) enabled only after
  backfill + verification, ordered migrate → tolerant deploy → backfill → verify → flag (AFR-237). A tested restore
  **MUST** precede pilot/production backfill and every backfill **MUST** be tenant-scoped with an isolation check +
  performance budget (AFR-238).

### Truthful status
- Step 9 attests architecture/governance readiness only. Customer 360, recovery, Google Review, AI, omnichannel,
  knowledge base, analytics, public API, payment, deployment, pilot, and production **MUST** be stated **NOT STARTED**
  until evidenced. No design artifact **MUST** be read as an implementation, deployment, or ownership claim.

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These remain binding but their application is scheduled later; Step 9 **MUST NOT** be read as delivering them: Customer
360 identity resolution (Step 10), transaction/service-event ingestion, Customer Recovery/SLA, basic AI and Google
Review, omnichannel conversations, AI copilot/handover/QA, knowledge base + RAG, Agent Studio/workflow automation,
advanced analytics/ROI, public API/webhooks/marketplace, and payment/billing (rules 02, 05, 06, 18).

## Required checks
- `scripts/docs/verify-step-9.sh` (Step 9 artifact + ADR/AFR + version-consistency + no-stale-status + secret-scan +
  Step 8 regression checks); the full documentation-as-code suite (`scripts/docs/validate.sh`); `scripts/docs/check-adr.sh`;
  `scripts/docs/secret-scan.sh`; the `backend-runtime-ci` gate (rules 28, 29) which re-runs the Step 5–8 real-infra
  regressions unchanged.

## Evidence
- `docs/architecture/experience-os/**`, `docs/product/capability-inventory/STEP_9_CAPABILITY_INVENTORY.md`,
  `docs/product/competitive/**`, `docs/product/EXPERIENCE_OS_ROADMAP.md`,
  `docs/product/AGENTIC_EXPERIENCE_OS_PRD_ADDENDUM.md`, `docs/security/STEP_9_THREAT_MODEL.md`,
  `docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`,
  `docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`; ADRs 0063–0068; AFR-211..238;
  `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-9/`; `docs/release/STEP_9_*`.

## Related canonical sections
- Master Source §75; §34–§42, §35, §36, §47, §57, §62; PRD v1.3.0; Agentic Experience OS PRD Addendum v1.0.0; ADRs
  0063–0068; AFR-211..238; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27, 28, 29, 30, 31, 32, 33.

## Supersession
Permanent for Step 9+. Single-writer domain ownership, the identity/lifecycle/timeline boundaries, additive-not-replacing
ledger, provider-neutral truthful channel adapters, bounded human-approval-governed AI tool actions, additive reversible
migration with Step 8 preservation, and truthful architecture-only status are permanent; superseded only by a
higher-version Master Source update that preserves these guarantees.
