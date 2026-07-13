# Data Policy by Environment — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`, `04`, `07`, `18`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No environment or dataset exists. No secret is
> stored or committed. Provider not selected (WATCH). This is a planning specification.

## 1. Principle

Data sensitivity **MUST** decrease as an environment moves away from production. Lower environments run on
**synthetic** data; only the two deployment-class live environments (`pilot`, `production`) may hold real
customer data, and only under approval and minimization. The overriding rule:

> **No production data** — raw production data MUST NOT be copied to `local`, `test`, `CI`, or `staging`.
> Restated for enforcement: production data MUST NOT leave the production (or approved pilot) boundary into
> any lower environment except through a documented, owner-approved, securely-anonymized export. There is
> no production data in any lower environment.

## 2. Per-environment data rules

| Environment | Allowed data | Forbidden data | Source of data |
|-------------|-------------|----------------|----------------|
| `local` | **Synthetic** only — fabricated records from seed factories | Any real PII, medical, financial, or production data | Local seeders/factories |
| `test` | Synthetic fixtures (deterministic) | Any real PII or production data | Version-controlled fixtures |
| `CI` | Synthetic fixtures (deterministic) | Any real PII or production data | Version-controlled fixtures |
| `staging` | Synthetic **OR** formally anonymized data only | Raw/re-identifiable production data | Synthetic seeders or approved anonymization pipeline |
| `pilot` | Approved pilot-tenant data under minimum-data policy | Non-consented data; prohibited MED fields to AI/public | Consented pilot tenant (Klinik Gigi Daengtisia) |
| `production` | Approved tenant data under production controls | Data outside contract/consent scope | Live tenants |

## 3. Synthetic data definition

**Synthetic** data is fabricated data that has never referred to a real person, patient, transaction, or
tenant. It **MUST NOT** be reverse-engineered from a real dataset. Synthetic generators **MUST** produce
realistic-but-fake names, contacts, and transactions and **MUST** be seedable/deterministic for `test`/`CI`.

## 4. Anonymized data (staging only, exception path)

If synthetic data is insufficient for a `staging` scenario, **formally anonymized** data **MAY** be used only
when **all** of the following hold:

1. A documented owner approval exists for the specific export.
2. A **secure anonymization** process removes or irreversibly transforms all direct and quasi-identifiers so
   re-identification is not reasonably possible.
3. All prohibited healthcare fields (diagnosis, clinical notes, MRN, prescriptions, odontogram, clinical
   media, treatment narrative, insurance, payment-card/bank data — Rule 18) are removed before export.
4. The anonymized dataset is treated as Internal, access-controlled, retention-limited, and audited.
5. The export is recorded in the decision log with scope and expiry.

Absent all five, `staging` **MUST** use synthetic data. Anonymization **MUST NOT** be assumed reversible-safe;
if in doubt, use synthetic.

## 5. Explicit prohibitions (MUST NOT)

- Raw production database dumps **MUST NOT** be restored into `local`, `test`, `CI`, or `staging`.
- Real patient/medical data **MUST NOT** appear in `local`, `test`, `CI`, or `staging` under any circumstance,
  even anonymized, for prohibited fields.
- Real credentials or tenant secrets **MUST NOT** be embedded in seed/fixture data (see
  [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)).
- Screenshots, logs, or evidence artifacts committed to the repository **MUST NOT** contain real customer PII.
- AI providers **MUST NOT** receive prohibited MED fields in any environment (Rule 18).

## 6. Data classification cross-reference

| Class | Where permitted |
|-------|-----------------|
| Synthetic | All environments |
| Formally anonymized | `staging`, `pilot`, `production` (with approval) |
| Approved pilot data (minimized) | `pilot`, `production` |
| Approved production tenant data | `production` only |
| Prohibited MED fields to AI/public | Nowhere (Rule 18) |

## 7. Retention and deletion

- Lower environments (`local`, `test`, `CI`) are ephemeral; data is reset frequently and **MUST NOT** be
  backed up as authoritative.
- `staging` retention is short; anonymized datasets **MUST** be deleted at their recorded expiry.
- `pilot` and `production` retention is configurable per policy/contract; deletion and export requests
  **MUST** be honored and audited (Rule 07).

## 8. Enforcement

- Data-policy adherence is a promotion gate (see [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md)).
- Any exception **MUST** carry documented approval plus a secure anonymization record, or it is **NO-GO**.
- A breach (real production data found in a lower environment) is a security incident and **MUST** be logged,
  contained, and remediated before further promotion.
