# Subdomain and URL Matrix — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
**Rule refs:** `.claude/rules/21`; supporting `.claude/rules/03`, `04`, `08`, `10`, `15`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-075, AFR-076, AFR-079.
**Baseline date:** 2026-07-13.

---

## 1. Purpose

Define the planned **subdomain map** for **Aish Agentic AI** — each host's purpose, environment, TLS posture, and
authentication requirement — so downstream DNS, TLS, and OAuth planning is consistent and tenant-safe. In the
tables below, `<domain>` is a placeholder for the primary domain selected in `DOMAIN_STRATEGY.md`
(preferred `aishagentic.ai`). No host is provisioned here.

## 2. Non-claims

- **No domain is owned** and **no DNS record is created**. Ownership state: **NOT OWNED**.
- Host names below are **planned identifiers**, not live endpoints. Nothing here resolves or serves traffic.
- This is **not legal advice**. Availability of the parent domain is **point-in-time** (2026-07-13).
- DNS mutation and TLS issuance are **OUT OF SCOPE** for Step 4 and **NOT STARTED**.

Point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. Environment model

| Environment | Host convention | Purpose | Public exposure |
|-------------|-----------------|---------|-----------------|
| Production | `<host>.<domain>` | Live product surfaces | Public (per host) |
| Staging | `staging.<domain>`, `app.staging.<domain>` | Pre-production verification | Restricted |
| Pilot | `pilot.<domain>`, `app.pilot.<domain>` | Controlled pilot tenant runtime | Restricted |
| Development | `dev.<domain>`, `app.dev.<domain>` | Internal development | Restricted / internal |

Non-production environments (`staging.`, `pilot.`, `dev.`) **MUST** be access-restricted (IP allowlist, auth
gate, or `noindex`) and **MUST NOT** be indexed by search engines. They **MUST NOT** carry real customer PII
beyond what pilot governance (Rules 16–19) permits.

## 4. Production subdomain matrix

| Subdomain (host) | Purpose | Environment | TLS | Auth requirement | Tenant scope |
|------------------|---------|-------------|-----|------------------|--------------|
| `www.<domain>` | Public marketing site (canonical web host) | Production | Required (HTTPS, HSTS) | None (public) | N/A |
| `<domain>` (apex) | Redirects to `www.<domain>` | Production | Required | None | N/A |
| `app.<domain>` | Tenant application (product UI) | Production | Required (HTTPS, HSTS) | Session + MFA-capable login | Tenant + branch scoped |
| `admin.<domain>` | Platform administration console | Production | Required (HTTPS, HSTS) | Privileged login + MFA + IP controls | Platform-admin only |
| `api.<domain>` | Public API | Production | Required (HTTPS, mTLS optional) | API key / OAuth token, per-tenant | Tenant scoped |
| `docs.<domain>` | Product & developer documentation | Production | Required | None (public) or gated | N/A |
| `status.<domain>` | Public status / incident page | Production | Required | None (public) | N/A |
| `support.<domain>` | Support portal / help center | Production | Required | Optional login | Tenant scoped when authed |
| `assets.<domain>` | Static assets / CDN origin | Production | Required | None (signed URLs for private) | N/A |
| `hooks.<domain>` | Inbound webhook ingress | Production | Required (HTTPS) | Signature verification per source | Tenant scoped by routing |

## 5. Non-production subdomain matrix

| Subdomain (host) | Purpose | Environment | TLS | Auth requirement |
|------------------|---------|-------------|-----|------------------|
| `staging.<domain>` | Staging site | Staging | Required | Access-restricted |
| `app.staging.<domain>` | Staging tenant app | Staging | Required | Login + access-restricted |
| `pilot.<domain>` | Pilot environment site | Pilot | Required | Access-restricted |
| `app.pilot.<domain>` | Pilot tenant app | Pilot | Required | Login + access-restricted |
| `dev.<domain>` | Development site | Development | Required | Internal only |
| `app.dev.<domain>` | Development tenant app | Development | Required | Internal only |

## 6. Host rules (enforceable)

- Every host **MUST** be served over **TLS**; plaintext HTTP **MUST** redirect to HTTPS. HSTS **MUST** be enabled
  on authenticated hosts (`app.`, `admin.`, `api.`).
- `admin.<domain>` **MUST** require privileged authentication plus MFA and SHOULD be additionally restricted
  (IP allowlist / VPN). It **MUST NOT** be reachable with ordinary tenant credentials.
- `app.<domain>` and `api.<domain>` **MUST** enforce tenant and branch scoping on every request; there **MUST
  NOT** be cross-tenant data exposure via any host (Rule 03).
- `hooks.<domain>` **MUST** verify webhook signatures and **MUST NOT** trust payload content to steer privileged
  behavior; inbound feedback/reviews are untrusted input (Rules 04, 05).
- Non-production hosts (`staging.`, `pilot.`, `dev.`) **MUST NOT** be search-indexed and **MUST** be
  access-restricted.
- `no-reply@`, `status`, and other email/host identifiers **MUST** stay consistent with
  `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md`.
- Wildcard hosts **MUST NOT** be used to shortcut authentication or OAuth redirect matching (see
  `OAUTH_REDIRECT_URI_PLAN.md`).

## 6a. Tenant addressing model

For the MVP the tenant application is served from a **single shared host** (`app.<domain>`) with the tenant
resolved from the authenticated session, **not** from a per-tenant subdomain. This keeps tenant isolation an
application/authorization concern (Rule 03), avoids per-tenant TLS/DNS sprawl, and keeps OAuth redirect URIs
stable and exact-match (see `OAUTH_REDIRECT_URI_PLAN.md`).

| Decision | Rule |
|----------|------|
| Tenant resolution | From authenticated session + tenant context, on the shared `app.<domain>` host. |
| Per-tenant subdomains | **MUST NOT** be introduced in the MVP without an ADR + Master Source update. |
| Branch scoping | Enforced in-application; branch-scoped roles see only their branch (Rule 03). |
| Custom/vanity domains | Out of scope for MVP; any future support is a versioned decision. |

## 6b. Reserved and prohibited host names

| Concern | Rule |
|---------|------|
| Reserved hosts | `www`, `app`, `admin`, `api`, `docs`, `status`, `support`, `assets`, `hooks`, `staging`, `pilot`, `dev`, `mail`, `_dmarc`, `_domainkey` are reserved and **MUST NOT** be reassigned to tenant-controlled content. |
| Dangling records | Every host record **MUST** point to an active, owned target; dangling CNAMEs (subdomain-takeover risk) **MUST NOT** exist and **MUST** be removed when a target is decommissioned. |
| Look-alike hosts | Confusable host names **MUST NOT** be created under the primary; brand protection is handled by the defensive-domain set, not by look-alike subdomains. |

## 7. Canonical URL conventions

| Concern | Convention |
|---------|-----------|
| Canonical web | `https://www.<domain>/` (apex 301 → `www`) |
| Product app entry | `https://app.<domain>/` |
| API base | `https://api.<domain>/v1/` (versioned per Rule 08) |
| OAuth callback (prod) | `https://app.<domain>/oauth/google/callback` (exact-match; see OAuth plan) |
| Webhook ingress | `https://hooks.<domain>/<source>/<tenant-routing>` |
| Status | `https://status.<domain>/` |

### 7.1 Path conventions on `app.<domain>`

| Path prefix | Purpose | Auth |
|-------------|---------|------|
| `/login`, `/logout` | Authentication entry/exit | Public / session |
| `/dashboard` | Owner/branch dashboards | Session (tenant/branch scoped) |
| `/feedback`, `/recovery`, `/reviews` | Core workflow surfaces | Session (tenant/branch scoped) |
| `/integrations/google` | Google connection management | Session + approval-gated actions |
| `/oauth/google/callback` | OAuth callback (exact-match) | State-validated (see OAuth plan) |
| `/settings` | Tenant/user settings | Session (tenant scoped) |

### 7.2 Path conventions on `api.<domain>`

| Path prefix | Purpose |
|-------------|---------|
| `/v1/...` | Versioned public API (Rule 08) |
| `/v1/health` | Health/liveness (no sensitive data) |

## 7a. Environment isolation rules

- Production and non-production hosts **MUST** resolve to isolated infrastructure; a non-production host **MUST
  NOT** share credentials, data stores, or OAuth clients with production.
- Non-production data **MUST NOT** contain real customer PII beyond pilot governance allowances (Rules 16–19);
  synthetic/anonymized data is preferred for `dev.` and `staging.`.
- Robots exclusion (`noindex` / `robots.txt disallow`) **MUST** be applied to `staging.`, `pilot.`, and `dev.`
  hosts so pre-production surfaces never appear in search results.
- TLS **MUST** be enforced on non-production hosts too; there is no "internal only, so plaintext is fine"
  exception.

## 8. Status

| Item | State |
|------|-------|
| Subdomain / URL matrix | **PLANNING BASELINE — NOT IMPLEMENTED** |
| DNS records for these hosts | OUT OF SCOPE (Step 4) — NOT STARTED |
| TLS certificates | OUT OF SCOPE (Step 4) — NOT STARTED |
| Ownership | **NOT OWNED** — NOT CLAIMED |

Related: `DOMAIN_STRATEGY.md`, `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md`, `OAUTH_REDIRECT_URI_PLAN.md`.
