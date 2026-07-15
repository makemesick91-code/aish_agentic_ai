# ADR 0064 — Unified Customer Identity and Customer 360 Ownership

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 architecture LOCK; Customer 360 implementation NOT STARTED (Step 10)
- **Owner:** Principal Architect / Customer Profile & Identity Resolution
- **Rule:** `.claude/rules/34`, `.claude/rules/03`, `.claude/rules/18`, `.claude/rules/30` · **Canonical:** Master Source §75, §36; PRD v1.3.0; rules 34, 03, 04, 07, 18, 30, 32

## Context
The Experience OS needs a unified customer view, but Steps 5–8 have no `Customer` entity — feedback references its
source (a survey response), and survey responses are anonymous by default. Building Customer 360 wrongly could cause
identity poisoning, incorrect/destructive merges, cross-tenant linking, or silent creation of identities from anonymous
data. This ADR locks who owns customer identity and the rules that make it safe, ahead of the Step 10 build.

## Decision
- A tenant-scoped **`Customer` aggregate** (ULID public id; branch as provenance only) owned solely by Customer Profile
  & Identity Resolution; cross-tenant linking is prohibited.
- **Source identities** (`customer_identities`) capture provenance and confidence; **deterministic** links (exact
  normalized verified email/phone) are auto-applied; **probabilistic** matches are suggestions that are never
  auto-applied and require human approval.
- **Merge/split are human-approved, reversible, and immutably audited** (`customer_merge_events` + append-only audit);
  **no silent destructive merge**.
- **Anonymous responses never silently create a customer**; an IP is not an identity (preserves rule 32).
- **Consent history** is versioned and append-only; survey completion is not marketing consent.
- **Erasure** = tombstone + PII purge while retaining minimized non-PII counts; retention configurable; legal hold
  overrides purge.
- **Backfill** of existing Step 8 data is additive, idempotent, queued, resumable, and non-destructive (ADR 0068);
  unlinked feedback remains valid.
- The full contract is `docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md`; the executable plan
  is `docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`.

## Alternatives
- **Auto-merge on any match** — rejected: causes identity poisoning and destructive merges.
- **Create a customer from every survey/IP** — rejected: violates anonymity/consent semantics (rule 32).
- **Store identity per feedback item** — rejected: duplicate ownership; identity must be a single owned aggregate.
- **Irreversible merges** — rejected: no recovery from an incorrect merge; merges must be reversible.

## Consequences
Customer 360 can be built in Step 10 with safe, auditable, reversible identity resolution; no cross-tenant or
destructive identity operation is possible; anonymity and consent are preserved.

## Impacts
- **Security:** cross-tenant linking prohibited; merge/split authorized + audited; identity keys tenant-scoped unique.
- **Privacy:** PII minimized/hashed; erasure supported; MED data never placed in identity records (rule 18); consent
  versioned.
- **Tenant isolation:** identity and every read-model are tenant-scoped; branch is provenance, not a cross-tenant key.
- **Database:** Step 10 adds additive `customers`, `customer_identities`, `customer_merge_events`, `customer_consents`
  and a nullable `customer_id` on `feedback_items`; no Step 8 alteration.
- **Operational:** backfill is idempotent/resumable with progress + reconcile; suggestions queue for human approval.
- **Cost:** none in Step 9; bounded backfill cost in Step 10.

## Verification / fitness function
`scripts/docs/verify-step-9.sh` asserts the identity architecture and Step 10 contract exist and cover confidence,
provenance, deterministic-vs-suggested, merge/split, consent, retention, backfill, and audit. Step 10 adds
`aish:verify-step-10` + `tests/Feature/Security/Sf10CrossTenantMatrixTest.php`. AFR-215, AFR-216, AFR-217, AFR-218,
AFR-219.

## Related
Requirement: Master Source §75, §36; PRD v1.3.0. Rules: 34, 03, 04, 07, 18, 30, 32. ADRs: 0011, 0012, 0053, 0063,
0065, 0068.

## Evidence
`docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md`,
`docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`; `docs/governance/foundation-coverage-matrix.md`;
`docs/evidence/step-9/`.

## Non-claims
Creates no `Customer` model, migration, or runtime; does not claim Customer 360 is implemented; does not link any real
data; does not claim deployment/pilot/production readiness. Customer 360 is NOT STARTED until Step 10 evidence exists.

## Rollback
Tenant-scoped single-owner customer identity, deterministic-vs-suggested links, human-approved reversible merge/split
with immutable audit, no-silent-identity-from-anonymous, consent semantics, and additive non-destructive backfill are
permanent; changing any requires a new ADR + owner-approved Master Source update.
