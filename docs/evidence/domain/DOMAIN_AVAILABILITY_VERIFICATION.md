# Domain Availability Verification — Point-in-Time Evidence

**Status:** POINT-IN-TIME EVIDENCE — availability is not ownership. **No domain is owned or purchased.**
**Rule:** `.claude/rules/21`; AFR-073, AFR-078. **Canonical:** Master Source v2.4.0 §68 (Domain strategy).

## Method
- **Protocol:** RDAP (Registration Data Access Protocol), authoritative registry query.
- **`.com` authority:** `https://rdap.verisign.com/com/v1/domain/<name>` (Verisign, the `.com` registry).
- **`.ai` authority:** `https://rdap.org` IANA-bootstrap redirect → `rdap.identitydigital.services`
  (Identity Digital operates `.ai` RDAP). Redirects followed.
- **Interpretation:** HTTP `200` = object exists = **REGISTERED**; HTTP `404` = no matching object =
  **AVAILABLE** at query time.
- **Verification date/time:** 2026-07-13, timezone Asia/Makassar.
- **Query environment:** sandboxed HTTPS client (no Bash egress); `Accept: application/rdap+json`.

## Method validation (controls)
Known-registered controls returned `200`, proving the method distinguishes registered from available and that
`.ai` RDAP resolves through the bootstrap (i.e., `.ai` `404`s are true negatives, not a missing RDAP service):

| Control | Result | Final RDAP host |
|---------|--------|-----------------|
| `google.com` | HTTP 200 (REGISTERED) | `rdap.verisign.com` |
| `character.ai` | HTTP 200 (REGISTERED) | `rdap.identitydigital.services` |
| `elevenlabs.ai` | HTTP 200 (REGISTERED) | `rdap.identitydigital.services` |

## Candidate results (2026-07-13, point-in-time)
| Candidate | RDAP result | Availability (point-in-time) |
|-----------|-------------|------------------------------|
| `aishagentic.ai` | HTTP 404 | AVAILABLE |
| `aishagenticai.com` | HTTP 404 | AVAILABLE |
| `aishagentic.com` | HTTP 404 | AVAILABLE |
| `aishcx.ai` | HTTP 404 | AVAILABLE |
| `aishcx.com` | HTTP 404 | AVAILABLE |
| `getaish.ai` | HTTP 404 | AVAILABLE |
| `aishcustomer.ai` | HTTP 404 | AVAILABLE |

## Caveats (truthful)
- Availability is **point-in-time** and can change at any moment; re-verify at registrar checkout.
- RDAP "not found" does **not** guarantee registerability: a name may be **premium-priced**, **registry-reserved**,
  or on **registry hold**; registrar checkout is the only binding confirmation.
- `.ai` requires a **minimum 2-year** registration term and typically carries higher cost/renewal than `.com`.
- This evidence records **availability only**. It is **not** a purchase, reservation, or ownership claim.
  Domain ownership state: **NOT OWNED — NOT CLAIMED**.

## Recheck governance
Re-run this verification before any registration decision and at each renewal-planning checkpoint. See
[Domain Ownership & Renewal Governance](../../domain/DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md).
