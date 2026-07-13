# Configuration and Secret Matrix — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/24` (configuration & secrets), `.claude/rules/23` (environments); supporting `.claude/rules/04`, `07`, `15`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. **No secret is stored, committed, or given a real
> value in this repository.** No hosting or billing provider is selected (WATCH). This is a planning specification.

## 1. Classification vocabulary

Every configuration item **MUST** be classified as exactly one of the following:

- **Public configuration** — non-sensitive, safe to expose (e.g. app name).
- **Internal configuration** — non-secret but not for public display.
- **Secret** — sensitive value that grants no direct external identity but must stay confidential.
- **Credential** — authenticates to an external system (tokens, passwords, keys).
- **Tenant setting** — per-tenant value stored in the database, not in `.env`.
- **Environment setting** — differs per environment, non-secret.
- **Feature flag** — toggles behavior.
- **Build-time setting** — fixed at build/compile.
- **Runtime setting** — read at process start / runtime.

## 2. Core secret rules (MUST)

- Secrets and credentials **MUST NOT be committed** to git in any form, in any environment.
- `.env` files **MUST NOT** be committed; only `.env.example` with **placeholder** values (never real values)
  is tracked.
- Secret material **MUST** be **environment-specific**: `local`, `test`, `CI`, `staging`, `pilot`, and
  `production` each use separate values from a secret manager or CI secret store; a value **MUST NOT** be
  reused across environments.
- A developer's personal secret **MUST NOT** equal an organization production secret; personal dev keys
  **MUST** be sandbox/test-scoped.
- OAuth client secrets and Google credentials **MUST** be encrypted at rest; access tokens **MUST** be
  encrypted; refresh tokens **MUST NOT** be stored in plaintext (Rule 04, Master Source §43).
- Every secret and credential **MUST** have a documented rotation procedure and owner.
- `scripts/docs/secret-scan.sh` **MUST** pass; GitHub push protection **MUST** stay enabled.

## 3. Configuration item classification

| Item | Classification | Committed? | Source | Per-environment | Rotation |
|------|----------------|-----------|--------|-----------------|----------|
| `APP_ENV` | Environment setting | Placeholder in `.env.example` | Env | Yes | n/a |
| `APP_URL` / domain URLs | Environment setting | Placeholder | Env / secret mgr | Yes | On domain change |
| Database DSN/host | Internal configuration | Placeholder | Secret manager | Yes | On rotation |
| Database password | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Redis host / prefix | Environment setting | Placeholder | Secret manager | Yes | n/a |
| Redis auth/password | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Queue connection | Runtime setting | Placeholder | Env | Yes | n/a |
| Storage bucket / region | Environment setting | Placeholder | Env | Yes | n/a |
| Storage access key/secret | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Mail DSN / API key | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| WhatsApp provider token | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Google OAuth client ID | Internal configuration | Placeholder | Secret manager | Yes | On rotation |
| Google OAuth client secret | Credential | **MUST NOT be committed** | Secret manager (encrypted) | Yes | Scheduled |
| Google refresh token | Credential | **MUST NOT be committed** (never plaintext) | Encrypted store | Yes | On rotation |
| AI provider API key | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| `APP_KEY` / encryption keys | Secret | **MUST NOT be committed** | Secret manager | Yes | On rotation (with re-encrypt plan) |
| Session config/driver | Runtime setting | Placeholder | Env | Yes | n/a |
| Session secret/cookie | Secret | **MUST NOT be committed** | Secret manager | Yes | On rotation |
| Logging level/channel | Runtime setting | Placeholder | Env | Yes | n/a |
| Monitoring endpoint/key | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Sentry DSN | Secret | **MUST NOT be committed** | Secret manager | Yes | On rotation |
| Backup destination/creds | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| Webhook signing secret | Secret | **MUST NOT be committed** | Secret manager | Yes | Scheduled |
| API rate-limit config | Internal configuration | Placeholder | Env | Yes | n/a |
| Feature flags | Feature flag | Non-secret defaults may be tracked | Flag service / config | Yes | n/a |
| Billing provider key | Credential | **MUST NOT be committed** | Secret manager | Yes | Scheduled |

## 4. `.env.example` policy

`.env.example` (a planned future file — not created in Step 4) **MUST** list every key from §3 with a
placeholder value only, for example `GOOGLE_OAUTH_CLIENT_SECRET=__set_in_secret_manager__`. It **MUST NOT**
contain any real value, and reviewers **MUST** reject any PR that introduces a real secret. Real values live
only in the per-environment secret manager or CI secret store.

## 5. Secret path separation

Each environment references secrets under an **environment-specific** path (see
[ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md)), e.g. `secret/aish/{env}/...`. A process
running in one environment **MUST NOT** be able to read another environment's secret path. `pilot` and
`production` secret paths **MUST** be the most restricted, with least-privilege access and audit.

## 6. Rotation and incident response

- Rotation ownership and cadence **MUST** be recorded per credential (see §3).
- On suspected exposure, the affected credential **MUST** be rotated immediately, the leak scanned for, and a
  security event logged (Rule 04, Rule 11).
- Rotation **MUST NOT** cause silent data loss; `APP_KEY`/encryption-key rotation **MUST** follow a documented
  re-encryption plan before the old key is retired.

## 7. Relationship to other environment documents

Data allowed alongside these secrets is governed by [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md).
Which environment holds which secret path is fixed by [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md).
Promotion of configuration changes follows [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md).
