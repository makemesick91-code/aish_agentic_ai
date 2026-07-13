# Test Strategy — Aish Agentic AI

Canonical: Master Source §50, §59. Rule: `.claude/rules/09`, `03`, `05`. PRD §23, §30.

## Test categories (Master Source §50)
- **Functional:** auth, tenant creation, user invitation, role assignment, survey create/publish,
  invitation, response, CSAT calc, ticket create, SLA, escalation, Google OAuth, location mapping, review
  sync, draft, approval, publish, subscription, billing, metering, export, audit.
- **Multi-tenant:** Tenant A ≠ Tenant B; branch scoping; no leakage in search/export; queue tenant context;
  cache/storage/AI retrieval/analytics tenant-scoped (`.claude/rules/03`).
- **AI evaluation:** full adversarial dataset incl. prompt injection, PII, sarcasm, mixed language, threats,
  medical/legal/fraud allegations (`../ai/AI_EVALUATION_BASELINE.md`).
- **Security:** broken access control, cross-tenant, privilege escalation, OAuth leakage, CSRF/XSS/SQLi,
  file upload, webhook forgery, rate-limit bypass, prompt injection, tool abuse, secret exposure, audit
  tampering, session fixation, IDOR, SSRF (`../security/THREAT_MODEL_BASELINE.md`).
- **Performance:** survey/review load, dashboard load, large tenant, multi-branch reporting, queue backlog,
  AI concurrency, API throttling, bulk import/export, notification burst.

## Definition of Done (Master Source §59; PRD §30)
Scope complete · code + review · safe migration · correct permissions · tenant isolation tested · tests
pass · relevant security & AI evaluation pass · UI states complete · audit available · docs updated · CI
pass · deployment (if required) · runtime smoke · external integration verified · evidence available · no
critical issue · truthful status · Master Source updated if material.

## This documentation foundation
The applicable "tests" are the documentation-as-code gates (`RELEASE_GATES.md`, `.claude/rules/13`) run by
`scripts/docs/validate.sh` and CI. Application test suites apply when implementation begins (NOT STARTED).
