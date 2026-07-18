# Step 10 — Customer 360 Foundation: GO / WATCH / NO-GO Gate

**Canonical:** Master Source v2.13.0 §77; rule 36; ADRs 0070–0072; AFR-250..262
**Related:** rules 09, 13, 27, 28, 29; `docs/security/STEP_10_THREAT_MODEL.md`
**Status:** see §4 — filled from actual evidence only. No cell may be marked PASS without the evidence named beside it.

## 1. Hard gates (any failure ⇒ NO-GO)

| # | Gate | Evidence |
|---|---|---|
| H-01 | No cross-tenant customer read or write; no cross-tenant merge | `Sf10CrossTenantMatrixTest`; `aish:verify-step-10` |
| H-02 | Identity hashes do not correlate the same person across tenants | `Sf10CrossTenantMatrixTest`; verify command |
| H-03 | No plaintext contact PII on `customer_identities` | `Sf10MigrationIntegrityTest`; model-layer guard; verify command |
| H-04 | No identity value, contact detail, or consent prose in audit, logs, or merge snapshots | `Sf10AuditTest`; `verify-step-10.sh` leak + PII scans |
| H-05 | A merge never deletes and is always reversible from its recorded snapshot | `CustomerMergeSplitTest`; verify command |
| H-06 | Merge/consent/audit history is append-only and non-deletable | `Sf10AuditTest`, `Sf10BoundariesTest`, `Sf10MigrationIntegrityTest` |
| H-07 | Only verified identities auto-link; anonymous sources create no customer | `CustomerIdentityResolverTest`, `Sf10CommandsTest` |
| H-08 | Merge requires human approval, `customer.merge`, and reachability of BOTH customers; no bulk merge | `CustomerHttpTest`, `Sf10CrossTenantMatrixTest`; route table |
| H-09 | Contact PII gated by `customer.view-contact`; feedback content still gated by `feedback.view-content` | `CustomerHttpTest`, `CustomerInteractionsReadModelTest` |
| H-10 | Consent fails closed; do-not-contact overrides; a merge cannot discard an objection | `CustomerConsentTest`; verify command |
| H-11 | Migrations additive only; no Step 8 column altered/dropped; no in-migration backfill; unlinked feedback valid | `Sf10MigrationIntegrityTest`; `check-step10-coverage.sh`; migration review |
| H-12 | Backfill idempotent, resumable, non-destructive | `Sf10CommandsTest`; `verify-step-10.sh` double-run |
| H-13 | Entitlement fails closed via the single authoritative resolver | `CustomerHttpTest`; verify command |
| H-14 | Google Review anti-gating preserved; no AI on customer data in Step 10 | `Sf10BoundariesTest`; rule 36 |
| H-15 | All prior-step regressions still green (Step 6, SF-05, Step 7, Step 8) | `verify-step-10.sh` steps 3–6 |
| H-16 | Full hermetic suite green; Pint + PHPStan clean | `php artisan test`; `vendor/bin/pint --test`; `vendor/bin/phpstan analyse` |
| H-17 | Independent security review PASS with no unresolved Critical/High/Medium | `docs/evidence/step-10/step-10-independent-security-review.md` |
| H-18 | Authoritative Full CI green on the exact final candidate SHA | GitHub run id + SHA |
| H-19 | Clean-checkout verification passes on the exact merged SHA | `verify-step-10.sh` on the merge commit |
| H-20 | Annotated immutable GO tag matches the merged commit on local, remote, and main | `docs/release/STEP_10_TAG_VERIFICATION.md` |

## 2. WATCH conditions (not blocking, must be recorded)
- Pepper/normalizer rotation tooling is not shipped (versioning exists to make it possible later).
- Suggestion scoring is minimal — unverified candidates are reported, not ranked.
- Merged customer rows are retained indefinitely by design.
- Email/phone **ownership verification channels** do not exist; "verified" means the source already proved control.

## 3. NO-GO triggers
Cross-tenant customer exposure · cross-tenant identity correlation · plaintext PII in identity rows · PII in
audit/logs/snapshots · an unreversible or silently destructive merge · mutable identity history · an unverified value
auto-linking · anonymous data creating a customer · a merge laundering a do-not-contact · a destructive or
non-idempotent backfill · any Step 8 regression · an unresolved Critical/High/Medium security finding · fabricated CI,
merge, tag, or runtime evidence.

## 4. Current status

| Gate group | Status | Note |
|---|---|---|
| H-01..H-14 (functional/security invariants) | **PASS** | 452-test hermetic suite + `aish:verify-step-10` (32 checks) on real PostgreSQL 17 + Redis 7 |
| H-15 (prior-step regressions) | **PASS** | `verify-step-10.sh` re-ran Step 6, SF-05, Step 7, Step 8 real-infra checks |
| H-16 (suite + lint + static analysis) | **PASS** | 452 passed / 1998 assertions; Pint passed; PHPStan no errors |
| H-17 (independent security review) | **PENDING** | scheduled before merge |
| H-18 (authoritative Full CI on final head) | **PENDING** | requires a pushed candidate SHA |
| H-19 (clean-checkout on merged SHA) | **PENDING** | requires the merge commit |
| H-20 (immutable GO tag) | **PENDING** | requires H-17..H-19 |

**Verdict: IN PROGRESS toward GO.** Step 10 is CODE COMPLETE and TESTED locally. It is **not** merged, **not**
tagged, **not** CI-green-on-CI, and **not** clean-checkout-verified on a merged SHA. GO is claimed only when every
hard gate above shows PASS with its evidence recorded.
