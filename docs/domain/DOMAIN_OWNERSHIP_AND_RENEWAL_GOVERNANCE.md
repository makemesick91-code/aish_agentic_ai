# Domain Ownership and Renewal Governance — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
**Rule refs:** `.claude/rules/21`; supporting `.claude/rules/04`, `07`, `11`, `13`, `15`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-077, AFR-078.
**Owner:** Aish Tech Solution · **Baseline date:** 2026-07-13.

---

## 1. Purpose

Fix the **ownership, registrar-security, renewal, and recovery governance** for every domain **Aish Agentic AI**
plans to register, so that ownership sits with the **organization**, accounts are hardened, renewals never lapse,
and account recovery cannot be hijacked. This governance binds all domains named in `DOMAIN_STRATEGY.md`.

This is **planning only**. No domain is registered, transferred, or configured here.

## 2. Non-claims

- **No domain is owned, registered, or transferred.** Ownership state: **NOT OWNED**.
- Availability of candidate domains is **point-in-time** (2026-07-13) and **not** ownership. **Not legal advice.**
- No registrar account is created and no DNS/TLS is provisioned here.
- Registration, DNS mutation, and TLS issuance are **OUT OF SCOPE** for Step 4 and **NOT STARTED**.

Point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. Ownership principle

- Every domain **MUST** be registered to the **organization** **Aish Tech Solution** as the legal registrant —
  **never** a personal developer account, personal email, or individual employee's name.
- The registrant **organization** contact, admin contact, and technical contact **MUST** use organization-owned
  role addresses (e.g. `domains@` / `security@`), not personal inboxes that leave with an individual.
- Ownership records, once real, **MUST** be tracked in an internal registry (registrar, registrant org, creation
  date, expiry date, auto-renew state, DNSSEC state). This planning document defines the schema; it holds no
  live secrets.
- Domain ownership is a security-critical asset; loss of a domain can enable phishing and account takeover, so
  ownership governance outranks convenience (Rule 04 decision-priority).

## 4. Registrar selection criteria (mandatory)

The chosen registrar **MUST** support all of the following; a registrar missing any **MUST NOT** be selected:

| # | Criterion | Requirement |
|---|-----------|-------------|
| R1 | **DNSSEC** | Registrar/DNS supports DNSSEC signing and DS management. |
| R2 | Registrar lock | Supports registrar lock (clientTransferProhibited). |
| R3 | **Transfer lock** | Supports and defaults to **transfer lock** to prevent unauthorized transfers. |
| R4 | **MFA** | Account access protected by **MFA** (TOTP or hardware key); SMS-only is insufficient. |
| R5 | WHOIS privacy | Supports WHOIS/RDAP privacy while keeping org as registrant of record. |
| R6 | DNS API | Provides an authenticated DNS API for controlled automation. |
| R7 | Organization ownership | Supports **organization**-level ownership, not just individual accounts. |
| R8 | Audit & roles | Supports role-based access and an audit log of account actions. |

## 5. Account security controls (enforceable)

- Registrar account access **MUST** require **MFA**; privileged actions (transfer, nameserver change, DNSSEC
  change, contact change) **MUST** require re-authentication.
- **Transfer lock** and registrar lock **MUST** be enabled on every domain and **MUST NOT** be disabled except
  during an approved, logged transfer window.
- Registrar credentials, API tokens, and DNS API keys **MUST NOT** be committed to the repository and **MUST**
  be stored in a secret manager (Rule 04).
- Access **MUST** follow least privilege: only a small, named set of organization operators may hold registrar
  admin rights; changes **MUST** be logged and auditable (Rule 07).
- WHOIS privacy **MUST** be enabled while the registrant of record remains the **organization**.

## 6. Recovery-account governance

- The registrar account recovery email/phone **MUST** be an **organization**-controlled role account with its own
  MFA, **not** a personal account.
- Recovery paths (backup email, recovery codes, hardware-key backups) **MUST** be documented internally and
  stored securely; recovery codes **MUST NOT** be committed to the repository.
- At least two named organization operators **MUST** be able to recover access, to avoid single-person lockout,
  while still enforcing MFA and audit.
- A compromised registrar or DNS account is a security incident and **MUST** trigger the incident runbook
  (Rule 11): lock account, rotate credentials, review audit log, verify DNSSEC/nameservers unchanged.

## 7. Renewal governance

| Control | Requirement |
|---------|-------------|
| Auto-renew | Auto-renew **MUST** be enabled on the primary and all defensive domains. |
| Payment resilience | A backup payment method **MUST** be on file; card expiry **MUST** be monitored. |
| Expiry alerts | Expiry alerts **MUST** fire at 90, 60, 30, 14, and 7 days before expiry. |
| `.ai` term | `.ai` domains require a **2-year minimum** term and carry higher renewal cost/risk; the `.ai` renewal MUST be tracked distinctly. |
| Re-verification | Availability of any not-yet-registered fallback/defensive domain **MUST** be re-checked via RDAP before acting; a stored result is not current truth. |
| Ownership review | Registrant org, contacts, DNSSEC, and lock state **MUST** be reviewed at each renewal cycle. |
| Lapse = incident | An unintended expiry or near-expiry **MUST** be treated as an operational incident and recorded. |

## 8. Change control

- Changing the primary domain, adding/removing a defensive domain, or changing the registrar is a **material
  decision**: it **MUST** be recorded in `docs/decisions/DECISION_LOG.md` and assessed for a Master Source update
  (Rule 12).
- Nameserver, DNSSEC, and registrant changes **MUST** be reviewed and logged; they **MUST NOT** be made silently
  by tooling or a single unaudited operator.

## 8a. Roles and responsibilities

| Role | Responsibility |
|------|----------------|
| Product owner (Aish Tech Solution) | Approves domain strategy changes, registrar selection, and material decisions. |
| Domain operator (named, ≥2) | Holds registrar admin access under MFA; performs renewals and changes; logs actions. |
| Security | Owns `security@`, monitors registrar/DNS incidents, enforces MFA and lock policy. |
| Finance | Maintains backup payment method; monitors card expiry to prevent renewal lapse. |

No single individual **MUST** be the sole holder of registrar access, recovery, or payment; single-person
control is a continuity and takeover risk and is prohibited.

## 8b. Threats this governance mitigates

| Threat | Mitigation |
|--------|-----------|
| Domain hijack via transfer | Transfer lock + registrar lock + MFA + re-auth on privileged actions |
| Expiry lapse / drop-catch | Auto-renew + staged expiry alerts + backup payment |
| DNS spoofing / cache poisoning | DNSSEC with planned key rollover |
| Account takeover via recovery | Organization-controlled recovery accounts with their own MFA |
| Personal-account lock-in | Organization ownership + role addresses, never personal accounts |
| Unauthorized CA issuance | CAA record (see DNS/TLS plan) restricting issuers |

## 9. Governance table (schema for the future live registry)

| Field | Description |
|-------|-------------|
| Domain | Fully qualified domain name |
| Role | Primary / fallback / defensive |
| Registrant | Organization (Aish Tech Solution) |
| Registrar | Selected registrar meeting §4 criteria |
| Created / Expiry | Dates (populated only when real) |
| Auto-renew | Enabled (required) |
| Transfer lock | Enabled (required) |
| DNSSEC | Enabled (required once live) |
| MFA on account | Enabled (required) |

## 9a. Renewal calendar (planning template)

The following is a template for the future live renewal calendar; it holds no live dates until a domain is
actually registered.

| Checkpoint | Owner | Action |
|-----------|-------|--------|
| 90 days before expiry | Domain operator | Confirm auto-renew + payment method valid |
| 60 days before expiry | Domain operator | Review registrant, locks, DNSSEC, contacts |
| 30 days before expiry | Domain operator + Finance | Verify charge will succeed; escalate if at risk |
| 14 / 7 days before expiry | Domain operator | Final confirmation; treat any failure as an incident |
| `.ai` 2-year term boundary | Domain operator | Track distinctly; higher cost/renewal risk |

## 9b. Prohibited actions

- **MUST NOT** register any Aish domain under a personal account or personal email.
- **MUST NOT** disable transfer lock or registrar lock outside an approved, logged transfer window.
- **MUST NOT** commit registrar credentials, API tokens, DNS keys, or recovery codes to the repository.
- **MUST NOT** let a single individual hold sole registrar access, recovery, or payment control.
- **MUST NOT** allow a domain to lapse; an unintended expiry is an operational incident.

## 10. Status

| Item | State |
|------|-------|
| Ownership & renewal governance | **PLANNING BASELINE — NOT IMPLEMENTED** |
| Domain ownership | **NOT OWNED** — NOT CLAIMED |
| Registrar account | NOT STARTED |
| Registration / renewal | OUT OF SCOPE (Step 4) — NOT STARTED |

Related: `DOMAIN_STRATEGY.md`, `DNS_TLS_AND_EMAIL_SECURITY_PLAN.md`,
point-in-time evidence `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.
