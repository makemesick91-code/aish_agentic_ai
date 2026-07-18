# ADR 0071 — Customer Identity Normalization, Hashing, and Deterministic Matching

- **Status:** Accepted (2026-07-18, Asia/Makassar) — Step 10 Customer 360 Foundation implementation decision
- **Owner:** Principal Architect / Security Engineer / Customer Profile & Identity Resolution
- **Rule:** `.claude/rules/36`, `.claude/rules/04`, `.claude/rules/34` · **Canonical:** Master Source §77, §75, §43; PRD v1.3.0; rules 36, 34, 03, 04, 07, 18, 32

## Context
ADR 0064 requires deterministic linking on "exact normalized verified email/phone" and duplicate prevention through a
unique `(tenant_id, identity_type, value_hash)` index, but does not specify how a value is normalized or hashed. These
choices are security-critical and effectively irreversible once identities exist:

- If normalization is too aggressive, distinct customers collapse into one (identity poisoning by construction).
- If normalization is too weak, the same customer fragments and the unique index fails to prevent duplicates.
- If the hash is unsalted, the identity table becomes an offline-crackable directory of every customer's email and phone
  number — the value space for phone numbers is small enough to enumerate exhaustively.
- If the hash salt were tenant-scoped, an identical value in two tenants would produce different hashes — which is
  desirable — but a *global* salt would let anyone with table access confirm the same person exists across tenants.

## Decision
- **Normalization is explicit, versioned, and centralized** in `App\Customers\Identity\IdentityNormalizer`. No caller
  may normalize inline. Rules:
  - *Email*: trim, lowercase, NFKC-normalize, reject if it fails RFC-shaped validation. The local part is **preserved
    verbatim** — no dot-stripping, no `+tag` removal, no provider-specific aliasing, because those rules are not
    universal and applying them would merge distinct mailboxes.
  - *Phone*: trim, strip spaces/hyphens/parentheses/dots, convert a leading `00` to `+`, apply the tenant's default
    region to a national-format number to produce E.164, then require a valid E.164 result. A number that cannot be
    resolved to unambiguous E.164 is **rejected as a deterministic identity** and may only ever be a suggestion.
  - *External ref*: trim only; case and punctuation are preserved because external identifiers are opaque.
- **Hashing is keyed, not plain.** `value_hash = HMAC-SHA256(key = APP_KEY-derived identity pepper ‖ tenant_id, message =
  identity_type ‖ ":" ‖ value_normalized)`, hex-encoded. Binding the tenant into the key means an identical email in two
  tenants yields unrelated hashes, so the table cannot be used to correlate a person across tenants, and the pepper means
  the table is not an offline-enumerable directory.
- **`value_normalized` is stored only for identity types that are not PII** (`external_ref`). For `email` and `phone`
  the normalized value is **not** persisted on `customer_identities`; the displayable contact value lives on the customer
  record behind `customer.view-contact`, and the identity row keeps only the hash plus provenance. This keeps the
  matching index functional without duplicating PII into a second table.
- **Deterministic linking requires a verified identity.** An unverified email/phone produces a *suggestion*, never an
  automatic link — matching an address someone merely typed is not proof of ownership.
- **The normalization scheme is versioned** (`identity_normalizer_version` on each identity row). Changing normalization
  never rewrites existing hashes in place; a future change ships as a new version plus an additive, idempotent,
  resumable re-hash backfill (ADR 0068), so historical links stay explainable.

## Alternatives
- **Plain SHA-256 of the value** — rejected: phone/email space is enumerable, making the table an offline-crackable PII
  directory; also enables cross-tenant correlation.
- **Global (non-tenant-bound) pepper** — rejected: preserves cross-tenant correlation, which ADR 0064 prohibits.
- **Store the normalized email/phone in plaintext for matching** — rejected: duplicates PII into a table that
  `customer.view-contact` does not gate.
- **Gmail-style alias normalization (strip dots / `+tags`)** — rejected: provider-specific, not universally true, and a
  wrong collapse is an unrecoverable silent merge of two real people.
- **Encrypt rather than hash** — rejected: reversible encryption in a uniqueness index widens blast radius on key
  compromise without improving matching, which needs only equality.

## Consequences
Deterministic matching is exact, explainable, and reproducible; the identity table leaks neither a customer directory
nor cross-tenant correlation. The cost is that a value cannot be recovered from an identity row (by design), so
operator-facing lookup must go through the permission-gated customer contact fields, and normalization changes require a
versioned backfill rather than an in-place update.

## Impacts
- **Security:** keyed hashing removes offline enumeration; tenant-bound keying removes cross-tenant correlation; pepper
  derives from `APP_KEY` and is never logged, committed, or placed in audit metadata.
- **Privacy:** PII is minimized — no second plaintext copy of email/phone; identity rows are hash + provenance only;
  MED data is never an identity.
- **Tenant isolation:** the tenant is bound into the hash key *and* the unique index is `(tenant_id, identity_type,
  value_hash)`, so isolation holds even if a query loses its tenant scope.
- **Database:** `customer_identities.value_hash` is a fixed-width hex column with a tenant-scoped unique index;
  `value_normalized` is nullable and populated only for `external_ref`; `identity_normalizer_version` is a small integer.
- **Operational:** rotating the pepper (or bumping the normalizer version) requires a versioned re-hash backfill, not an
  in-place rewrite; the procedure is documented in the Step 10 runbook.
- **Cost:** negligible — one HMAC per identity write.

## Verification / fitness function
`tests/Unit/Customers/IdentityNormalizerTest.php` covers email casing/whitespace/alias-preservation, phone E.164 with
region + leading-`00`, ambiguous-number rejection, and idempotent normalization. `tests/Feature/Customer360/CustomerIdentityLinkTest.php`
covers deterministic link, duplicate prevention via the unique index, unverified→suggestion, and cross-tenant
non-correlation (identical email in two tenants yields different hashes and never links). `php artisan aish:verify-step-10`
re-proves it on real PostgreSQL 17. AFR-253, AFR-254, AFR-255, AFR-256.

## Related
Requirement: Master Source §77, §75, §43, §36; PRD v1.3.0;
`docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md` §4, §7. Rules: 36, 34, 03, 04, 07, 18, 32. ADRs: 0064,
0068, 0070, 0072; 0011, 0012, 0025.

## Evidence
`app/Customers/Identity/IdentityNormalizer.php`, `app/Customers/Identity/IdentityHasher.php`,
`tests/Unit/Customers/IdentityNormalizerTest.php`, `tests/Feature/Customer360/CustomerIdentityLinkTest.php`;
`docs/security/STEP_10_THREAT_MODEL.md`; `docs/evidence/step-10/`.

## Non-claims
Does not implement probabilistic scoring beyond producing human-approval suggestions; does not verify email/phone
ownership (no verification channel exists yet — an identity is "verified" only when its source already proved
ownership); does not implement transaction/Google/WhatsApp ingestion; claims no deployment, pilot, or production
readiness.

## Rollback
Centralized versioned normalization, keyed tenant-bound hashing, no-plaintext-PII-in-identity-rows, and
verified-only-deterministic-linking are permanent for Step 10+; changing the scheme requires a new ADR, a version bump,
an additive idempotent re-hash backfill, and an owner-approved Master Source update — never an in-place rewrite of
existing identity rows.
