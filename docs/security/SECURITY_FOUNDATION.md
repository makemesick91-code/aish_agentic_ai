# Security Foundation — Aish Agentic AI

Canonical: Master Source §43, §57. Rules: `.claude/rules/04`, `03`, `07`. Repository policy: `../../SECURITY.md`.

## Mandatory controls (Master Source §43)
TLS · encryption at rest · encrypted credentials · secret management · tenant isolation · role-based access ·
branch scoping · rate limiting · MFA · audit logs · backup + tested restore · session security · CSRF/XSS/
SQLi protection · secure file upload (+ malware scanning where needed) · webhook signatures · OAuth state
validation · token rotation · data retention/export/deletion · PII redaction · prompt-injection defense ·
AI output validation · approval workflow · kill switch · incident log · security alerting.

## Decision priority (Master Source §57)
Security → tenant isolation → privacy → policy compliance → correctness → auditability → reliability → UX →
performance → cost → automation → feature richness. **Automation and convenience never outrank security or accuracy.**

## Secrets
Never commit secrets, tokens, keys, `.env`, dumps, or backups. Use env vars + a secret manager. GitHub
secret-scanning **push protection is enabled**. Local gate: `scripts/docs/secret-scan.sh`
(`.claude/rules/04`). Any exposure ⇒ rotate/revoke immediately + incident record.

## Application security gates
Tenant isolation, permission, OAuth security, token encryption, prompt-injection test, PII guardrail,
audit, file-upload safety, and webhook-signature gates must pass with evidence before a product release
(`docs/quality/RELEASE_GATES.md`, Master Source §54). See also `TENANT_ISOLATION.md`, `PRIVACY_AND_PII.md`,
`PROMPT_INJECTION_DEFENSE.md`, `THREAT_MODEL_BASELINE.md`.

**Status:** security foundation documented. Runtime controls apply when implementation begins (NOT STARTED).
