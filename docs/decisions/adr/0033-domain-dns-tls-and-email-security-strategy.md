# ADR 0033 — Domain, DNS, TLS, and Email-Security Strategy

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no domain owned, no DNS mutated, no TLS issued**
- **Owner:** Domain & DNS Architect
- **Rule:** `.claude/rules/21`, `04` (AFR-073..080) · **Canonical:** Master Source v2.4.0 §68; PRD v1.3.0

## Context
Step 4 must fix a domain, subdomain, DNS/TLS, and email-authentication strategy so implementation and OAuth
planning share a stable target — without purchasing a domain, mutating DNS, or issuing certificates. Domain
availability is point-in-time and must be evidence-based, never asserted as ownership.

## Decision
The official product name "Aish Agentic AI" is unchanged. Preferred primary domain **aishagentic.ai**; first
fallback **aishagenticai.com**; second fallback **aishagentic.com**; the primary's `.com` twin plus
`aishcx.ai`/`aishcx.com`/`getaish.ai`/`aishcustomer.ai` are defensive priorities. All seven candidates were
**AVAILABLE** at 2026-07-13 by RDAP (Verisign for `.com`, Identity Digital for `.ai`; method validated by
registered controls). Domains MUST be organization-owned (Aish Tech Solution), on an MFA-protected registrar
account supporting transfer lock, DNSSEC, WHOIS privacy, and a DNS API. Canonical subdomains: `www`, `app`,
`admin`, `api`, `docs`, `status`, `support`, `assets`, `hooks`; non-production `dev`, `staging`, `pilot`. Email
addresses `hello@/support@/security@/privacy@/billing@/no-reply@/status@` under SPF + DKIM + DMARC. OAuth
redirect URIs are exact-match, per-environment, no wildcards. See
[Domain Strategy](../../domain/DOMAIN_STRATEGY.md) and [OAuth Redirect URI Plan](../../domain/OAUTH_REDIRECT_URI_PLAN.md).

## Alternatives
- **`.com`-only** — rejected as primary: `.ai` best matches the product name/category; `.com` retained as fallback + defensive.
- **Register now / claim ownership** — rejected: out of Step 4 scope; availability ≠ ownership.
- **Wildcard OAuth redirect** — rejected: security risk; exact-match only.

## Consequences
A stable, evidence-based domain plan; registration, DNS, and TLS remain out of scope and are executed later
under the ownership/renewal governance with re-verified availability.

## Impacts
- **Security:** MFA + transfer lock + DNSSEC + exact-match redirects + email authentication reduce takeover/spoofing risk.
- **Privacy:** WHOIS privacy required; no personal registrant data exposed; no PII in DNS/email plan.
- **Tenant isolation:** per-environment subdomains keep tenant app surfaces separate from admin/api and from non-production.
- **Database:** none (no data model change).
- **Operational:** renewal monitoring, recovery-account governance, and re-verification cadence defined.
- **Cost:** `.ai` 2-year minimum + higher renewal; `.com` defensive registrations — recorded as planning cost categories only.

## Verification / fitness function
`scripts/docs/check-step4-coverage.sh` asserts domain docs, point-in-time availability evidence, SPF/DKIM/DMARC,
DNSSEC/TLS, MFA/transfer-lock governance, and exact-match redirect governance (V4-DOM-01..04); no false ownership claim.

## Related
Requirement: Master Source v2.4.0 §68; PRD v1.3.0. Application rules: AFR-073..080. Rules: 21, 04, 23.
ADRs: 0021 (Google integration), 0022 (OAuth credential encryption), 0025 (secrets).

## Evidence
[Domain availability verification](../../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md),
`docs/domain/*` (strategy, candidate evaluation, subdomain matrix, DNS/TLS/email plan, ownership governance, OAuth redirect plan).

## Non-claims
No domain is owned, purchased, or reserved. No DNS record is created; no TLS certificate is issued. Availability
is point-in-time only. This ADR is not legal or trademark advice. Domain ownership: **NOT OWNED — NOT CLAIMED**.

## Rollback
Domain/subdomain choices are reversible before registration; superseding requires a recorded decision and a
Master Source update. Registration itself is a later, separately-authorized action.
