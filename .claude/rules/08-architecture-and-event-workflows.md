---
id: "08"
title: Architecture and Event-Driven Workflows
domain: architecture
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §17, §19, §34, §35, §36, §39, §42"
  - "PRD §11, §22"
supersede: "Only via a versioned Master Source update or an approved ADR."
---

# Rule 08 — Architecture and Event-Driven Workflows

## Purpose
Fix the technical baseline, domain boundaries, and event-driven workflow model.

## Scope
System architecture, service boundaries, event catalog, API design, and integration structure.

## Rules
- The core stack **MUST** follow Master Source §34: Laravel 12 (PHP 8.3+), PostgreSQL, Redis (cache +
  queue), S3-compatible storage, Fortify/Sanctum auth, Spatie Permission, Nginx, Sentry, OpenTelemetry-compatible observability.
- AI **MAY** be called from Laravel for MVP only when structured output, timeout, controlled retry, audit,
  prompt versioning, cost logging, and guardrails exist; the AI orchestrator **MUST** be split out when
  multi-agent complexity, tool calling, providers, scaling, or tracing needs grow (Master Source §34).
- Heavy work and external integrations **MUST** use the queue with tenant context.
- Canonical events (Master Source §35) — e.g. `TransactionCompleted`, `SurveyResponseSubmitted`,
  `FeedbackAnalyzed`, `HighRiskFeedbackDetected`, `RecoveryTicket*`, `GoogleReview*`, `AgentRunFailed`,
  `SLABreached` — **MUST** drive workflows; see `docs/architecture/EVENT_CATALOG.md`.
- The public API **MUST** enforce API key/OAuth, tenant scoping, rate limit, idempotency, validation,
  audit, pagination, versioning, webhook signatures, retry-safety, consistent errors, and no sensitive
  data in logs (Master Source §39; PRD §18.2).
- RAG/knowledge retrieval **MUST** send only minimum relevant, tenant/branch-filtered context (Master Source §42).

## Required checks
- `docs/architecture/SYSTEM_CONTEXT.md`, `DOMAIN_MAP.md`, `EVENT_CATALOG.md` exist and trace to §34-§42.
- Architecture-affecting changes require an ADR under `docs/architecture/adr/`.

## Evidence
- `docs/architecture/SYSTEM_CONTEXT.md`, `DOMAIN_MAP.md`, `EVENT_CATALOG.md`, `docs/architecture/adr/`.

## Related canonical sections
- Master Source §17, §19, §34, §35, §36, §39, §42; PRD §11, §22.

## Supersession
Large architecture changes are major-version events and require an ADR plus Master Source update.
