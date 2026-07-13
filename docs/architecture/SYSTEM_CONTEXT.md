# System Context — Aish Agentic AI

Canonical: Master Source §17, §34, §38, §39. Rule: `.claude/rules/08`. Derived summary; not implementation.

## Core stack (Master Source §34)
- **Backend:** Laravel 12 (PHP 8.3+) · **DB:** PostgreSQL · **Cache/Queue:** Redis (Laravel Queue)
- **Frontend:** Blade + Tailwind + Alpine, or Inertia/React · **Storage:** S3-compatible
- **Auth:** Fortify/Sanctum · **Permissions:** Spatie Permission · **Web:** Nginx
- **Errors:** Sentry (or equivalent) · **Observability:** OpenTelemetry-compatible
- **Deploy:** VPS / cloud VM / container / managed platform

## AI architecture boundary
```
Laravel Core → Queue or HTTP → AI Orchestrator → LLM Provider
```
For MVP, AI MAY be called from Laravel only with structured output, timeout, controlled retry, audit,
prompt versioning, cost logging, and guardrails. Split the AI orchestrator into a separate service
(Python/FastAPI, structured output, Redis, PostgreSQL, OpenTelemetry) when multi-agent complexity, tool
calling, providers, scaling, or tracing needs grow.

## External actors & systems
Customers (survey/feedback, untrusted input) · Tenant staff (by role/branch) · Platform operators ·
Google Business Profile (OAuth, reviews) · WhatsApp Business · Email/SMS providers · Slack/Teams ·
POS/CRM/ERP/CMS/HMS/e-commerce (integrations) · public API consumers (Master Source §39).

## Boundaries & invariants
- All heavy/external work runs on the queue carrying tenant context (`.claude/rules/03`, `08`).
- Tenant isolation on every surface (`.claude/rules/03`); secrets never in repo (`.claude/rules/04`).
- Truthful external states only (`.claude/rules/10`).

**Status:** architecture baseline documented. Implementation NOT STARTED.
