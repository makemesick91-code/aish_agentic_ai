# Step 10 — Customer 360 Foundation Threat Model

**Status:** ACTIVE — governs the Step 10 implementation and its independent security review
**Canonical:** Master Source v2.13.0 §77; ADRs 0070–0072 (with 0063–0065, 0068); rule 36; AFR-250..262
**Related:** `docs/security/STEP_9_THREAT_MODEL.md` (T-01..T-06, T-16, T-22), rules 03, 04, 07, 18, 30, 32, 33

Customer 360 concentrates, for the first time in this product, a **cross-domain identity graph plus contact PII**.
That changes the risk profile: a defect here does not leak one record, it links or exposes a person's whole history.
The controls below are therefore weighted toward *irreversibility* and *correlation*, not just access control.

## 1. Assets
| Asset | Why it matters |
|---|---|
| `customers` (contact email/phone, display name) | Directly identifying PII |
| `customer_identities` (hashes, provenance) | The matching graph; a weak hash makes it a customer directory |
| `customer_merge_events` (snapshots) | The only thing that makes an incorrect merge recoverable |
| `customer_consents` | Legal basis for contacting a person |
| `feedback_items.customer_id` | The join that turns anonymous feedback into an attributed history |
| The identity pepper (APP_KEY-derived) | Compromise would make every identity hash enumerable |

## 2. Trust boundaries
Authenticated tenant members (branch-scoped or tenant-wide) · platform operators (no tenant customer data) ·
queue workers (carry tenant context) · the CLI backfill (runs per tenant) · untrusted survey/feedback content
(never an identity source unless verified).

## 3. Threats and controls

| ID | Threat | Control | Verified by |
|---|---|---|---|
| S10-T01 | **Cross-tenant customer read/write** (IDOR via a guessed ULID) | Global `TenantScope` fails closed; ULID route keys; composite `(tenant_id, customer_id)` FKs; policy re-asserts tenant | `Sf10CrossTenantMatrixTest`, `aish:verify-step-10` |
| S10-T02 | **Cross-tenant identity correlation** — confirming a person exists in another tenant by comparing hashes | HMAC key binds `tenant_id`, so the same value hashes differently per tenant (ADR 0071) | `Sf10CrossTenantMatrixTest::identity_hashes_do_not_correlate`, verify command |
| S10-T03 | **Offline enumeration of the identity table** — phone/email space is small enough to brute force | Keyed HMAC with an `APP_KEY`-derived pepper, never a bare digest; pepper never logged/committed/audited | ADR 0071; secret scan; leak scan in `verify-step-10.sh` |
| S10-T04 | **PII leak via a second plaintext copy** | `customer_identities` stores no plaintext for PII types; enforced at the model layer, not only in services | `Sf10MigrationIntegrityTest`, `Sf10CrossTenantMatrixTest` |
| S10-T05 | **Identity poisoning** — attaching yourself to someone else's history by typing their email | Only a **verified** identity links; unverified values become suggestions; a redeemed invitation is the sole verified survey source | `CustomerIdentityResolverTest`, `Sf10CommandsTest` |
| S10-T06 | **Silent identity collapse** — over-normalization merging two real mailboxes | Conservative normalization: local part verbatim, no dot/`+tag` stripping; ambiguous phone refused | `IdentityNormalizerTest` |
| S10-T07 | **Irrecoverable incorrect merge** | Merge never deletes; append-only snapshot records the exact moved id set; split restores from it | `CustomerMergeSplitTest`, verify command |
| S10-T08 | **Audit/ledger tampering** to hide a bad merge | Merge/consent/audit are append-only; update and delete blocked at the model layer; no `updated_at` | `Sf10AuditTest`, `Sf10BoundariesTest`, `Sf10MigrationIntegrityTest` |
| S10-T09 | **Privilege abuse via merge** — a branch operator absorbing out-of-scope customers | `customer.merge` withheld from branch ops; merge requires reaching BOTH customers; no bulk merge | `Sf10CrossTenantMatrixTest`, `CustomerHttpTest` |
| S10-T10 | **Contact PII exposure to under-privileged viewers** | `customer.view-contact` gates real values; masked otherwise; contact columns excluded from search without it | `CustomerHttpTest`, `CustomerInteractionsReadModelTest` |
| S10-T11 | **Content escalation via the 360 timeline** — reading feedback text you could not read in the Inbox | Read-model selects metadata-only columns without `feedback.view-content` | `CustomerInteractionsReadModelTest` |
| S10-T12 | **Consent laundering via merge** — absorbing a duplicate to erase a do-not-contact | Effective consent folds the merge chain; do-not-contact overrides every purpose | `CustomerConsentTest`, verify command |
| S10-T13 | **Fabricated consent** — treating silence as agreement | Absent decision returns null and `mayContact` is false; consent text version recorded | `CustomerConsentTest` |
| S10-T14 | **Anonymous de-anonymization** — creating a profile per response | Anonymous sources create nothing; IP is never an identity | `CustomerIdentityResolverTest`, `Sf10CommandsTest` |
| S10-T15 | **Destructive backfill** damaging Step 8 records | Additive migrations only; no in-migration backfill; reconcile only fills NULL links; dry-run available | `Sf10MigrationIntegrityTest`, `Sf10CommandsTest`, `verify-step-10.sh` |
| S10-T16 | **Duplicate customers from a concurrent race** | DB unique `(tenant_id, identity_type, value_hash)`; resolver catches the violation and re-reads the winner | `Sf10MigrationIntegrityTest`, resolver tests |
| S10-T17 | **Partially-merged state from concurrent merges** | Both rows locked in deterministic id order inside one transaction | ADR 0072; `CustomerMergeSplitTest` |
| S10-T18 | **Entitlement bypass** | Single authoritative resolver; unknown key fails closed; `EnsureCustomer360Enabled` guard; merge gated separately | `CustomerHttpTest`, verify command |
| S10-T19 | **PII reaching logs/audit/snapshots** | Audit metadata is ids/counts/provenance only; snapshots record `has_contact_*` booleans; error messages never echo the offending value | `Sf10AuditTest`, `verify-step-10.sh` PII leak scan |
| S10-T20 | **Platform-role escalation into tenant customer data** | Platform plane is separate; no `Gate::before` elevation; policies require tenant membership | rule 31/36; `Sf10CrossTenantMatrixTest` |

## 4. Explicitly out of scope for Step 10
Customer Recovery (Step 11), transaction/service-event ingestion, the Experience Event Ledger runtime, Google Review,
AI on customer data (prohibited in Step 10), omnichannel channels, analytics, public API, billing, and deployment.
Email/phone **ownership verification channels** do not exist yet — "verified" means the source already proved control
(a redeemed invitation), never a self-asserted value.

## 5. Residual risks (accepted, documented)
- **Pepper rotation** requires a versioned re-hash backfill rather than an in-place update; no rotation tooling ships
  in Step 10 (the normalizer/hash version column exists to make it possible). Tracked for a later step.
- **Merged rows are retained indefinitely**, which is a deliberate trade: bounded storage in exchange for
  recoverability and audit truth.
- **Suggestion scoring is deterministic and minimal** in Step 10 (unverified candidates are reported as suggestions,
  not ranked). Richer matching is deferred and must remain human-approved (ADR 0064).
