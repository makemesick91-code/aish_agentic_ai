# OAuth Redirect URI Plan — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
**Rule refs:** `.claude/rules/21`; supporting `.claude/rules/04`, `05`, `06`, `08`, `15`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-079, AFR-080.
**Baseline date:** 2026-07-13.

---

## 1. Purpose

Define the planned governance for **OAuth redirect URIs** for **Aish Agentic AI**, principally for the Google
integration (Google connection, review sync, and reply publishing) referenced conceptually by the Google
integration boundary. This ensures every redirect URI is **exact-match**, per-environment, wildcard-free, and
state-validated before any OAuth client is created. `<domain>` denotes the selected primary domain
(preferred `aishagentic.ai`).

This is **planning only**. No OAuth client is created and no redirect URI is registered with any provider here.

## 2. Non-claims

- **No domain is owned** and **no OAuth client/credential is created.** Ownership state: **NOT OWNED**.
- The redirect URIs below are **planned identifiers**, not registered endpoints.
- Parent-domain availability is **point-in-time** (2026-07-13); this is **not legal advice**.
- OAuth client creation, DNS mutation, and TLS issuance are **OUT OF SCOPE** for Step 4 and **NOT STARTED**.

Point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. Core principles (enforceable)

- Every OAuth **redirect URI** **MUST** be **exact-match** at the provider: exact scheme (`https`), exact host,
  exact path, no trailing-slash ambiguity. A provider that receives a redirect URI not on the exact allowlist
  **MUST** reject the flow.
- **Wildcard redirect URIs MUST NOT** be used. Per-subdomain, per-path, per-environment URIs only.
- Redirect URIs **MUST** be **per-environment** (production, staging, pilot, development) with distinct hosts;
  a production OAuth client **MUST NOT** accept a non-production redirect URI, and vice versa.
- Redirect URIs **MUST** use `https` only; `http` (except an explicitly separate local-development client on
  `localhost`, if ever needed) **MUST NOT** be registered for production.
- OAuth **state** **MUST** be generated, bound to the session, and validated on return (CSRF defense); a
  mismatched or missing state **MUST** abort the flow (Rule 04).
- Customer/review content is untrusted input and **MUST NOT** influence redirect handling or token scope
  (Rules 05, 06).
- OAuth tokens **MUST** be encrypted at rest; refresh tokens **MUST NOT** be stored in plaintext; tokens
  **MUST** support rotation and tenant-scoped revocation (Rules 04, 06).

## 4. Per-environment redirect URI plan

Google integration callback path (canonical): `/oauth/google/callback`.

| Environment | Host | Planned exact-match redirect URI | OAuth client |
|-------------|------|----------------------------------|--------------|
| Production | `app.<domain>` | `https://app.<domain>/oauth/google/callback` | Production client |
| Staging | `app.staging.<domain>` | `https://app.staging.<domain>/oauth/google/callback` | Staging client |
| Pilot | `app.pilot.<domain>` | `https://app.pilot.<domain>/oauth/google/callback` | Pilot client |
| Development | `app.dev.<domain>` | `https://app.dev.<domain>/oauth/google/callback` | Dev client |

Each environment **MUST** use a **separate** OAuth client with its **own** credentials and its **own** exact
allowlist. Credentials for one environment **MUST NOT** be reused in another.

### 4.1 Additional flows (if enabled)

| Flow | Planned exact-match redirect URI (production) | Notes |
|------|-----------------------------------------------|-------|
| Post-connect return | `https://app.<domain>/integrations/google/connected` | Optional; still exact-match, no wildcard. |
| Admin-initiated connect | `https://admin.<domain>/oauth/google/callback` | Only if platform-admin connect is designed; separate client. |

Any additional redirect URI **MUST** be added to this plan first, then registered exact-match; ad-hoc redirect
URIs **MUST NOT** be registered.

## 5. Redirect URI governance rules

| # | Rule |
|---|------|
| U1 | Redirect URIs **MUST** be **exact-match**; no wildcard, no open redirect, no path prefix matching. |
| U2 | Each environment **MUST** have its own OAuth client and its own redirect-URI allowlist. |
| U3 | Redirect URIs **MUST** be `https` and **MUST** resolve only to Aish-owned hosts under `<domain>`. |
| U4 | OAuth **state** **MUST** be validated on every callback; PKCE SHOULD be used where the provider supports it. |
| U5 | Adding/changing a redirect URI is a change-controlled action, logged and reviewed; it **MUST NOT** be silently changed. |
| U6 | The application **MUST NOT** redirect the user to any URL derived from untrusted input after the callback (open-redirect prevention). |
| U7 | Tokens obtained via the flow **MUST** be tenant-scoped, encrypted, rotatable, and revocable per tenant (Rule 06). |
| U8 | A mock/unavailable Google integration **MUST NOT** be reported as a successful OAuth connection (truthful states, Rules 06, 10). |

## 5a. Why exact-match and no wildcard

| Risk | How exact-match + no-wildcard defends |
|------|----------------------------------------|
| Open redirect / token theft | An attacker cannot substitute an attacker-controlled callback host, because only the exact allowlisted URI is accepted. |
| Subdomain takeover | A dangling subdomain cannot be used as a redirect target, since it is not on the exact allowlist. |
| Environment cross-contamination | A production authorization cannot be redirected to staging/dev, because each environment has a distinct client and allowlist. |
| Path smuggling | Path-prefix or wildcard matching is disallowed, so appended paths/query cannot redirect the code elsewhere. |

## 5b. State and token handling summary

| Control | Requirement |
|---------|-------------|
| `state` parameter | Cryptographically random, session-bound, single-use, validated on return; mismatch aborts the flow. |
| PKCE | SHOULD be used where the provider supports it. |
| Scopes | Minimal scopes only (least privilege) for the Google integration's needs. |
| Token storage | Access tokens encrypted at rest; refresh tokens never plaintext; rotation supported. |
| Revocation | Tenant can disconnect Google and delete credentials; tokens revoked and purged (Rules 06, 07). |
| Audit | Connect, refresh-failure, and disconnect events audited (Rule 07). |

## 6. Provider registration checklist (future implementation)

When an implementation step is authorized, before creating each OAuth client:

- [ ] Confirm the target host (`app.<domain>` etc.) exists, is TLS-served, and is Aish-owned.
- [ ] Register only the exact-match redirect URIs listed in §4 for that environment.
- [ ] Confirm no wildcard and no extra redirect URI is present on the client.
- [ ] Store client credentials in the secret manager (never in the repo).
- [ ] Verify state validation and token encryption in the integration code.
- [ ] Record the client and its redirect-URI allowlist in the integration inventory.

This checklist is **planning guidance**; none of its steps are performed under Step 4.

## 6a. Google integration boundary (conceptual reference)

The Google integration (connection, review sync, AI reply drafting, human-approved publish) is governed by the
Google review policy and integration boundary (Rules 05, 06; Google integration documentation). This plan covers
only the **redirect-URI and OAuth-flow governance** for that boundary; it does not restate review policy. Key
inherited constraints:

- Every Google review reply **MUST** pass recorded human approval before publication; OAuth connection alone
  authorizes nothing to be auto-published.
- Review gating/manipulation is prohibited; OAuth scope selection **MUST NOT** enable behavior that violates the
  anti-gating policy.
- A mock or unavailable Google integration **MUST NOT** be reported as a successful connection (truthful states).

## 6b. Anti-patterns (prohibited)

| Anti-pattern | Why prohibited |
|--------------|----------------|
| Wildcard redirect URI (`https://*.<domain>/callback`) | Enables redirect to attacker-controlled subdomains. |
| Shared OAuth client across environments | Blurs blast radius; a dev leak compromises production. |
| `http://` redirect for production | Token interception risk; production is HTTPS-only. |
| Skipping `state` validation | CSRF / login-CSRF exposure. |
| Redirecting to a user-supplied URL post-callback | Open-redirect exposure. |
| Storing refresh tokens in plaintext | Violates Rule 04 token handling. |

## 7. Status

| Item | State |
|------|-------|
| OAuth redirect URI plan | **PLANNING BASELINE — NOT IMPLEMENTED** |
| OAuth clients / credentials | NOT STARTED |
| Redirect URIs registered with provider | OUT OF SCOPE (Step 4) — NOT STARTED |
| Ownership | **NOT OWNED** — NOT CLAIMED |

Related: `DOMAIN_STRATEGY.md`, `SUBDOMAIN_AND_URL_MATRIX.md`, `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md`,
point-in-time evidence `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.
