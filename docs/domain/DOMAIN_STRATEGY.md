# Domain Strategy — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
Application/domain implementation: **NOT STARTED**.
**Rule refs:** `.claude/rules/21` (Domain & DNS governance); supporting `.claude/rules/01`, `04`, `13`, `15`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-074, AFR-075, AFR-076, AFR-077, AFR-078, AFR-079, AFR-080.
**Owner:** Aish Tech Solution · **Timezone:** Asia/Makassar · **Baseline date:** 2026-07-13.

---

## 1. Purpose

This document fixes the Step 4 **domain strategy** for **Aish Agentic AI**: which primary domain the product
should register, the ordered fallback set, defensive registrations, the top-level-domain (TLD) trade-offs, and
the decision principles that bind every downstream domain, DNS, TLS, email, and OAuth-redirect planning document.

This is **planning only**. It records intent and criteria. It does **not** purchase, reserve, or claim any
domain, and it does **not** mutate DNS or issue certificates.

## 2. Non-claims (truthful boundary)

- **No domain is owned, purchased, reserved, or claimed.** The ownership state is **NOT OWNED**.
- Domain availability recorded in the evidence file is **point-in-time** (verified 2026-07-13) and can change
  at any moment. Availability is **not** ownership and **not** registerability (premium/reserved/hold names exist).
- This document is **not legal advice**. Trademark, brand, and regulatory screening notes are non-legal and must
  be confirmed by qualified counsel before any binding registration.
- No DNS record is created, changed, or deleted here. No TLS certificate is requested or issued.
- Domain purchase, DNS mutation, and TLS issuance are **OUT OF SCOPE** for Step 4 and remain **NOT STARTED**.

See the point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. Strategic objectives

| # | Objective | Rationale |
|---|-----------|-----------|
| O1 | Name reads as the product | Reduce recall friction; the domain should say "Aish Agentic AI" at a glance. |
| O2 | Signal the AI category | A `.ai` TLD communicates the Agentic AI positioning without extra words. |
| O3 | Own the `.com` twin | `.com` remains the global default for email trust and defensive coverage. |
| O4 | Protect the brand | Register a small, ordered defensive set to blunt typosquatting and confusion. |
| O5 | Keep operations safe | Organization ownership, registrar security, DNSSEC, and renewal governance from day one. |
| O6 | Stay generic | The domain must serve the multi-tenant, multi-branch product, not any single pilot tenant. |

## 4. Primary domain decision

The **preferred primary domain** is **`aishagentic.ai`**.

- It reads as the product name ("Aish Agentic") and the `.ai` TLD signals the AI category (Objectives O1, O2).
- The product name **Aish Agentic AI** MUST NOT change to fit a domain; the domain follows the name, never the
  reverse. Any product-name change requires an explicit product-owner decision (Rule 01).

Because `.ai` carries higher cost and renewal risk (see §6), the strategy pairs the primary with its `.com` twin
for email and defensive use (see §7).

### 4.1 Fallback order

If the preferred primary domain cannot be registered at checkout (taken, premium-priced, registry-reserved, or on
hold), the following **fallback** order MUST be used, highest priority first:

| Priority | Domain | Role | Notes |
|----------|--------|------|-------|
| Primary | `aishagentic.ai` | Preferred primary | Reads as product name; `.ai` category signal. |
| Fallback 1 | `aishagenticai.com` | First fallback | Full name in a `.com`; strong email trust. |
| Fallback 2 | `aishagentic.com` | Second fallback | Shorter `.com`; also the `.com` twin of the primary. |

A fallback becoming the primary is a **material decision**: it MUST be recorded in `docs/decisions/DECISION_LOG.md`
and trigger a Master Source impact analysis (Rule 12). Fallback selection MUST NOT silently occur in tooling.

## 5. Defensive registration set

Defensive domains reduce brand confusion and typosquatting exposure. They are **planned**, not registered. The
recommended registration order, by priority, is:

| Priority | Domain | Defensive purpose | TLD cost category |
|----------|--------|-------------------|-------------------|
| D1 | `aishagenticai.com` | `.com` twin / primary email + defensive | Standard `.com` |
| D2 | `aishagentic.com` | Short `.com` twin of the primary | Standard `.com` |
| D3 | `aishcx.ai` | Short brand-adjacent CX handle (AI) | Higher `.ai` |
| D4 | `aishcx.com` | Short brand-adjacent CX handle (`.com`) | Standard `.com` |
| D5 | `getaish.ai` | Marketing / call-to-action variant | Higher `.ai` |
| D6 | `aishcustomer.ai` | Descriptive brand-adjacent variant | Higher `.ai` |

Rules:
- Defensive domains MUST redirect (301) to the canonical primary once registered; they MUST NOT host independent
  content, independent brands, or independent email that could be mistaken for the primary.
- The defensive set MUST stay small and ordered by value; unbounded defensive buying is out of scope and wasteful.
- Every defensive domain inherits the same ownership, security, DNSSEC, and renewal governance as the primary.

## 6. TLD trade-off: `.ai` vs `.com`

| Dimension | `.ai` (e.g. `aishagentic.ai`) | `.com` (e.g. `aishagenticai.com`) |
|-----------|-------------------------------|-----------------------------------|
| Category signal | Strong — signals AI product | Neutral |
| Global familiarity | Growing | Highest / default |
| Email trust & deliverability | Good, but `.com` still the safest default | Strongest |
| Minimum term | **2-year minimum** registration required | 1-year minimum available |
| Cost / renewal | Higher cost; higher renewal risk | Lower, stable |
| Registrar/feature support | Slightly narrower | Broadest |
| Recommended role | Primary brand + app surfaces | Email + defensive twin |

Decision: use `.ai` for the **primary brand surface** and always hold the **`.com` twin** for email and defense.
The higher `.ai` renewal risk MUST be actively managed by the renewal governance document.

## 7. `.com` twin requirement

Regardless of which primary is chosen, the strategy MUST hold the matching `.com` twin (`aishagenticai.com`
and/or `aishagentic.com`) for:

- **Email:** transactional and support email SHOULD send from the `.com` to maximize deliverability and trust.
- **Defensive coverage:** prevents a third party from operating a confusingly similar `.com`.
- **Redirect:** the twin 301-redirects to the canonical primary for web traffic.

## 8. Canonical host decision

| Concern | Decision |
|---------|----------|
| Canonical web host | `www.<primary>` for the public site; apex 301-redirects to `www` (or vice-versa) — chosen once, applied consistently. |
| Canonical product name in copy | Always **Aish Agentic AI** (Rule 01), independent of the domain string. |
| Tenant application host | `app.<primary>` (see `SUBDOMAIN_AND_URL_MATRIX.md`). |
| Redirect strategy | All non-canonical hosts and defensive domains 301 to the canonical host. |

## 9. Decision principles (enforceable)

- The product name **Aish Agentic AI** **MUST NOT** change without an explicit product-owner decision; the
  domain adapts to the name.
- The **preferred primary domain** **MUST** be `aishagentic.ai`; if unavailable at checkout, the **fallback**
  order in §4.1 **MUST** be followed and the change recorded.
- The `.com` twin of the primary **MUST** be held for email and defensive use.
- Availability **MUST** be re-verified immediately before any registration and at every renewal cycle; a stored
  availability result **MUST NOT** be treated as still-true at a later date.
- Domains **MUST** be registered to the **organization** (Aish Tech Solution), never a personal developer account
  (see `DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md`).
- Domain registration, DNS mutation, and TLS issuance **MUST NOT** be performed under Step 4; they remain
  **NOT STARTED** until an owner-approved implementation step opens them.
- The core product **MUST** stay generic; a pilot tenant (e.g. Klinik Gigi Daengtisia) **MUST NOT** be encoded
  into the primary brand domain.

## 10. Downstream documents

| Document | Scope |
|----------|-------|
| `DOMAIN_CANDIDATE_EVALUATION.md` | Scores all 7 candidates on brand, technical, and risk dimensions. |
| `SUBDOMAIN_AND_URL_MATRIX.md` | Subdomain → purpose → environment → TLS → auth matrix. |
| `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md` | DNS records, DNSSEC, TLS, SPF/DKIM/DMARC, email security. |
| `DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md` | Ownership, registrar security, renewal, recovery governance. |
| `OAUTH_REDIRECT_URI_PLAN.md` | Per-environment exact-match OAuth redirect URI governance. |

Point-in-time availability evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 11. Status and truthful state

| Item | State |
|------|-------|
| Domain strategy | **PLANNING BASELINE — NOT IMPLEMENTED** |
| Domain ownership | **NOT OWNED** — NOT CLAIMED |
| Domain registration | **NOT STARTED** |
| DNS mutation | OUT OF SCOPE (Step 4) — NOT STARTED |
| TLS issuance | OUT OF SCOPE (Step 4) — NOT STARTED |
| Application implementation | **NOT STARTED** |

This document attests planning readiness only. It does **not** assert domain ownership, DNS configuration, TLS
issuance, or any deployment. Domain ownership: **NOT owned**. Application implementation and deployment: **NOT STARTED**.
