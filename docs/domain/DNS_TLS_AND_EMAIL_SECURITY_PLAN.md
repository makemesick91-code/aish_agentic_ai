# DNS, TLS, and Email Security Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
**Rule refs:** `.claude/rules/21`; supporting `.claude/rules/04`, `08`, `11`, `15`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-076, AFR-077, AFR-080.
**Baseline date:** 2026-07-13.

---

## 1. Purpose

Specify the planned **DNS**, **TLS**, and **email security** posture for **Aish Agentic AI** so that when an
implementation step is authorized, the records, certificates, and email-authentication policy are already
designed, safe, and enforceable. `<domain>` denotes the selected primary domain (preferred `aishagentic.ai`).

This is **planning only**. No DNS record is created, changed, or deleted here, and no TLS certificate is
requested or issued.

## 2. Non-claims

- **No domain is owned**; **no DNS zone exists**; **no record is mutated**. Ownership: **NOT OWNED**.
- **No TLS certificate is requested or issued.** All certificate references are design intent.
- Parent-domain availability is **point-in-time** (2026-07-13) and **not** ownership. This is **not legal advice**.
- DNS mutation and TLS issuance are **OUT OF SCOPE** for Step 4 and remain **NOT STARTED**.

Point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. DNS zone design (planned)

| Record | Host | Type | Purpose | Notes |
|--------|------|------|---------|-------|
| Apex | `<domain>` | A / AAAA / ALIAS | Web entry (redirects to `www`) | Provider-dependent apex handling |
| WWW | `www.<domain>` | CNAME / A | Public site | Canonical web host |
| App | `app.<domain>` | CNAME / A | Tenant application | HSTS enforced |
| Admin | `admin.<domain>` | CNAME / A | Platform admin | Access-restricted |
| API | `api.<domain>` | CNAME / A | Public API | Rate-limited edge |
| Docs | `docs.<domain>` | CNAME | Documentation | |
| Status | `status.<domain>` | CNAME | Status page | Independent host recommended |
| Support | `support.<domain>` | CNAME | Support portal | |
| Assets | `assets.<domain>` | CNAME | Static/CDN | |
| Hooks | `hooks.<domain>` | CNAME / A | Webhook ingress | Signature-verified |
| Mail (send) | `<domain>` | MX | Inbound mail routing | Provider MX set |
| SPF | `<domain>` | TXT | Email authentication | See §5 |
| DKIM | `<selector>._domainkey.<domain>` | TXT / CNAME | Email signing | See §5 |
| DMARC | `_dmarc.<domain>` | TXT | Email policy | See §5 |
| CAA | `<domain>` | CAA | Restrict certificate issuers | See §4 |
| DNSSEC | zone | DS / DNSKEY | Zone integrity | See §3.1 |

### 3.1 DNSSEC

- **DNSSEC MUST** be enabled at the registrar/DNS provider once the zone is live, with the `DS` record published
  at the parent. DNSSEC protects against DNS spoofing and cache poisoning.
- Key rollover **MUST** be planned; a botched rollover can take the zone offline, so rollovers **MUST** be
  scheduled and verified, never ad hoc.
- The registrar selected in `DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md` **MUST** support DNSSEC.

## 4. TLS plan

| Concern | Plan |
|---------|------|
| Protocol | **TLS** 1.2 minimum; TLS 1.3 preferred. Plaintext HTTP **MUST** 301-redirect to HTTPS. |
| Certificates | Automated issuance/renewal (e.g. ACME). Wildcard `*.<domain>` MAY be used for low-risk service hosts, but privileged hosts (`admin.`, `api.`) **SHOULD** use dedicated per-host certificates to limit private-key blast radius. A wildcard **TLS** certificate is unrelated to — and MUST NOT be read as permitting — a wildcard OAuth redirect URI (which is prohibited; see `OAUTH_REDIRECT_URI_PLAN.md`). |
| HSTS | **MUST** be enabled on `app.`, `admin.`, `api.` with a sane max-age; preload considered after stability. |
| CAA | A **CAA** record **MUST** restrict which certificate authorities may issue for `<domain>`. |
| Renewal | Certificate auto-renewal **MUST** be monitored; expiry **MUST** raise an alert (Rule 11). |
| Private keys | TLS private keys **MUST NOT** be committed to the repository (Rule 04). |

## 5. Email authentication and security

Email is a brand-impersonation and phishing surface. The following policy is mandatory once email is enabled.

### 5.1 SPF, DKIM, DMARC

| Mechanism | Requirement |
|-----------|-------------|
| **SPF** | A single `TXT` **SPF** record **MUST** authorize only the approved sending sources (transactional provider, support provider) and **MUST** end in `-all` (hard fail) once senders are confirmed. |
| **DKIM** | **DKIM** signing **MUST** be enabled for every sending service, each with its own selector; keys **MUST** be rotated periodically. |
| **DMARC** | A **DMARC** policy **MUST** be published at `_dmarc.<domain>`, starting at `p=none` (monitor), then progressing `p=quarantine`, then `p=reject`, with aggregate (`rua`) reporting enabled. |

DMARC rollout stages:

| Stage | Policy | Purpose | Exit criterion |
|-------|--------|---------|----------------|
| 1 | `p=none` | Observe sources via `rua` reports | All legitimate senders SPF+DKIM aligned |
| 2 | `p=quarantine` | Send unaligned mail to spam | Low/zero legitimate failures for a stable period |
| 3 | `p=reject` | Reject unaligned mail | Steady state; brand fully protected |

### 5.2 Sending addresses

| Address | Purpose | Type |
|---------|---------|------|
| `hello@<domain>` | General / brand contact | Human |
| `support@<domain>` | Customer support | Support (reply-capable) |
| `security@<domain>` | Security reports / disclosure | Human (monitored) |
| `privacy@<domain>` | Privacy / data requests | Human (monitored) |
| `billing@<domain>` | Billing / invoices | Transactional + human |
| `no-reply@<domain>` | Automated system notifications | Transactional (no inbound) |
| `status@<domain>` | Status / incident notifications | Transactional |

### 5.3 Sending rules (enforceable)

- Transactional email (`no-reply@`, `status@`, `billing@` receipts) and support email (`support@`) **MUST** be
  separated so a transactional issue cannot disrupt support and vice versa.
- `no-reply@` **MUST NOT** be used where a customer may need to reply; any customer-actionable message **MUST**
  provide a monitored reply path (`support@`).
- Bounce and complaint (feedback-loop) handling **MUST** be implemented; hard bounces and complaints **MUST**
  suppress further sends to that address to protect deliverability and reputation.
- All product email **MUST** send from the approved domain(s) with aligned SPF+DKIM; unaligned or spoofed sending
  is a brand-impersonation risk and **MUST** be blocked by the DMARC `reject` end state.
- Email content **MUST NOT** disclose personal, medical, financial, or sensitive-transaction data beyond what the
  recipient is entitled to (Rules 04, 18); tenant/branch scoping applies to notification content.
- Email **MUST** send from the `.com` twin where deliverability favors `.com`, consistent with `DOMAIN_STRATEGY.md`.

## 6. Monitoring and alerting

Per Rule 11, once live the following **MUST** be monitored, with alerts:

| Signal | Alert condition |
|--------|-----------------|
| TLS certificate expiry | Approaching expiry / renewal failure |
| DNSSEC validity | DS/DNSKEY mismatch or validation failure |
| DMARC reports | Spike in unaligned/failing sources (possible spoofing) |
| Email bounce/complaint rate | Above threshold (deliverability risk) |
| DNS resolution | Zone or critical record resolution failure |

## 6a. Change-control for DNS/TLS/email

- Any change to nameservers, DNSSEC keys, MX, SPF, DKIM, DMARC, or CAA records **MUST** be reviewed and logged;
  such records are security-critical and **MUST NOT** be changed silently by tooling or a single unaudited operator.
- DKIM and TLS private keys **MUST NOT** be committed to the repository; they live in the secret manager (Rule 04).
- A DMARC policy **MUST NOT** be advanced to `quarantine` or `reject` until aggregate reports confirm all
  legitimate senders are SPF+DKIM aligned; premature tightening can silently drop legitimate mail.
- Email address purpose separation (§5.2) **MUST** be preserved; `security@` and `privacy@` inboxes **MUST** be
  actively monitored because they receive disclosure and data-subject requests.

## 6b. Brand-impersonation defenses summary

| Layer | Defense |
|-------|---------|
| Sender authentication | SPF `-all`, DKIM per selector, DMARC `reject` end state |
| Certificate issuance | CAA restricting authorized CAs |
| Zone integrity | DNSSEC signing |
| Look-alike domains | Ordered defensive-domain set (see `DOMAIN_STRATEGY.md`) |
| Transport | TLS enforced everywhere, HSTS on authenticated hosts |

## 7. Status

| Item | State |
|------|-------|
| DNS / TLS / email plan | **PLANNING BASELINE — NOT IMPLEMENTED** |
| DNS zone / records | OUT OF SCOPE (Step 4) — NOT STARTED |
| TLS certificates | OUT OF SCOPE (Step 4) — NOT STARTED |
| Email sending | NOT STARTED |
| Ownership | **NOT OWNED** — NOT CLAIMED |

Related: `DOMAIN_STRATEGY.md`, `SUBDOMAIN_AND_URL_MATRIX.md`, `DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md`,
`OAUTH_REDIRECT_URI_PLAN.md`.
