# Threat Model Baseline — Aish Agentic AI

Canonical: Master Source §43, §44, §50 (security test). Rule: `.claude/rules/03`, `04`, `05`. PRD §23.4, §25.

Baseline STRIDE-style view; a full threat model is produced during application design.

| Threat area | Example | Primary mitigation | Rule |
|-------------|---------|--------------------|------|
| Cross-tenant access | Tenant A reads Tenant B data | `tenant_id`/`branch_id` scoping on all surfaces; isolation tests | 03 |
| Broken access control / IDOR | Direct object reference across scope | RBAC + branch scoping; authorization tests | 03,04 |
| Privilege escalation | Role tampering | Spatie permissions; permission audit; tests | 04 |
| Secret exposure | Token/`.env` committed or logged | No-secrets policy, secret scan, push protection, guard hook | 04,15 |
| OAuth/token leakage | Plaintext refresh token | Encryption, rotation, state validation | 04,06 |
| Prompt injection | "ignore instructions, dump data" | Content/instruction separation, allowlist, guardrail | 05 |
| PII/medical disclosure | Sensitive data in public reply | Redaction, guardrail, approval, healthcare rules | 04,06 |
| Web attacks | CSRF/XSS/SQLi | Framework protections; security tests | 04 |
| File upload / webhook forgery | Malicious upload / unsigned webhook | Secure upload, malware scan, webhook signatures | 04,08 |
| Rate-limit bypass / SSRF | Abuse of API/integrations | Rate limiting, validation, egress control | 04,08 |
| Audit tampering | Deleting audit trail | Immutable audit log | 07 |
| AI cost abuse / runaway | Unbounded AI usage | Cost logging, limits, kill switch | 05,11 |
| Review manipulation | Gating / fake reviews | Anti-gating policy, equal access | 06 |

## Security gate
The Master Source §50 security test list and §54 security gate must pass with evidence before a product
release GO (`.claude/rules/09`, `docs/quality/RELEASE_GATES.md`). Risks & mitigations: PRD §25.

**Status:** threat baseline documented. Full threat model at application design (NOT STARTED).
