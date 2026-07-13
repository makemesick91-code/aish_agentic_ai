# Security Policy — Aish Agentic AI

Security, tenant isolation, privacy, policy compliance, correctness, auditability, and reliability
outrank convenience and automation. This document is the repository-level summary; the enforceable
detail lives in `.claude/rules/04-security-privacy-and-secrets.md`,
`.claude/rules/03-multi-tenant-and-branch-isolation.md`, and `docs/security/`.

## Secrets — never commit

The following MUST NEVER be committed to this repository in any form:

- `.env` / `.env.*` files (an `.env.example` with placeholder keys only is allowed),
- API keys, OAuth client secrets, access/refresh tokens, session secrets,
- private keys, certificates, keystores (`*.pem`, `*.key`, `*.p12`, `*.pfx`),
- Google / provider service-account JSON,
- database dumps, backups, or logs containing customer, credential, or PII data.

Credentials are referenced through environment variables and a secure secret manager only.
GitHub secret-scanning **push protection is enabled** on this repository. If a secret is ever
exposed, treat it as compromised: rotate/revoke it immediately, then purge and document the incident.

## Reporting a vulnerability

Report suspected vulnerabilities privately to the product owner (Aish Tech Solution). Do not open a
public issue containing exploit detail, customer data, or credentials. Include: affected area,
reproduction steps, impact, and any evidence with secrets/PII redacted.

## Foundational security controls (product)

Tenant isolation on all surfaces; encrypted credentials and tokens; role-based access with branch
scoping; MFA; audit logging; TLS and encryption at rest; prompt-injection defense and AI output
validation; human approval for public/high-risk actions; backup with tested restore; kill switch and
incident logging. See `docs/security/SECURITY_FOUNDATION.md` and `docs/security/THREAT_MODEL_BASELINE.md`.

## AI-specific security

Customer feedback and Google reviews are **untrusted input**. System instructions are isolated from
customer content; customer content never determines tool calls; tool arguments are validated and tools
are allowlisted; guardrails redact PII/medical/financial data and block unsafe output. See
`docs/security/PROMPT_INJECTION_DEFENSE.md` and `.claude/rules/05-ai-governance-and-human-approval.md`.

## Scope of this repository today

This repository currently contains the documentation and Claude rules foundation only. No application
runtime, database, or production credentials exist here yet. Application security gates apply when
implementation begins (see `docs/quality/RELEASE_GATES.md`).
