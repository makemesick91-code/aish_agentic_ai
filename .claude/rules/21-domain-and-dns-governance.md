---
id: "21"
title: Domain, DNS, TLS, and Email-Security Governance
domain: domain
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §68 (Domain strategy)"
  - "PRD v1.3.0 (domain & URL requirements)"
  - "ADR 0033; AFR-073..080"
supersede: "Only via a versioned Master Source update + owner decision; product-name and ownership rules are permanent."
---

# Rule 21 — Domain, DNS, TLS, and Email-Security Governance

## Purpose
Keep the domain, subdomain, DNS/TLS, and email strategy consistent, evidence-based, and free of false ownership claims.

## Scope
Domain selection, registrar and DNS governance, TLS planning, email authentication, and OAuth redirect domains.

## Rules
- The official product name **MUST** remain **Aish Agentic AI**; it **MUST NOT** change without an explicit
  product-owner decision (AFR-073).
- Domain availability **MUST** be point-in-time verified with recorded evidence (date, method, source); domain
  ownership **MUST NOT** be claimed without registration evidence. Availability is **not** ownership (AFR-076).
- Domains **MUST** be owned by the organization account (Aish Tech Solution), **MUST NOT** be a personal
  developer account; the registrar account **MUST** enforce MFA, transfer lock, a DNSSEC target, WHOIS privacy,
  and renewal monitoring (AFR-074, AFR-075).
- A **preferred primary domain** plus at least two fallbacks **MUST** be recorded before registration; the current
  recommendation is `aishagentic.ai` with `.com` fallbacks (AFR-078).
- Subdomain naming **MUST** be canonical (`www/app/admin/api/docs/status/support/assets/hooks`; non-prod
  `dev/staging/pilot`); non-production domains **MUST NOT** be confused with production (AFR-077).
- OAuth redirect URIs **MUST** be exact-match and per-environment; wildcard redirects **MUST NOT** be used (AFR-079).
- The email domain **MUST** enforce SPF, DKIM, and DMARC; no-reply limitations and impersonation risk **MUST** be
  documented (AFR-080).
- Domain purchase, DNS mutation, and TLS issuance are **out of scope** for planning steps and **MUST NOT** be
  performed without separate authorization.

## Required checks
- `scripts/docs/check-step4-coverage.sh` verifies domain docs, point-in-time availability evidence, DNS/TLS/email
  controls, ownership governance, and exact-match redirect governance.

## Evidence
- `docs/domain/*`; `docs/evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## Related canonical sections
- Master Source v2.4.0 §68; PRD v1.3.0; ADR 0033; ADRs 0021, 0022, 0025.

## Supersession
Product-name and no-false-ownership rules are permanent; the recommended domain may change only via a recorded
decision after re-verified availability and a Master Source update.
