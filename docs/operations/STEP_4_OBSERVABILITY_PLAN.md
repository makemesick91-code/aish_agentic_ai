# Step 4 Observability Plan — Aish Agentic AI

**Title:** Step 4 Observability Plan
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Instrumentation is planned; nothing is emitting yet.
**Rule refs:** `.claude/rules/11` (observability/backup/operations), `.claude/rules/05` (AI tracing/cost), `.claude/rules/03` (tenant isolation), `.claude/rules/04` (no secrets/PII in logs).
**Canonical:** Master Source v2.4.0 §51 (observability), §54 (operational gate); PRD v1.3.0.
**AFR refs:** AFR-095..098 (ops/dependency governance context).

## Non-claims

- Nothing is provisioned or instrumented; no telemetry is being collected.
- Tooling choices (OpenTelemetry-compatible, Sentry-compatible) are EVALUATE DURING IMPLEMENTATION in the dependency matrix.
- This plan extends the Step 3 `OBSERVABILITY_BASELINE.md` / `OBSERVABILITY_AND_ALERTING_BASELINE.md` into a Step 4 planning baseline.

## Purpose

Define the minimum signals, tracing, alerts, and dashboards required so that the workflow is observable, AI actions are traceable, tenant isolation is monitorable, and the operational gate can pass with evidence. Instrumentation uses vendor-neutral, OpenTelemetry-compatible export.

## Signal catalog (Master Source §51)

| Signal | Source | Planned instrumentation |
|--------|--------|-------------------------|
| Structured app logs | Laravel app | JSON logs with tenant/branch context, request id |
| Queue logs | Queue workers | Job lifecycle, retries, failures, tenant context |
| API logs | HTTP layer | Request/response metadata; **no** sensitive payloads/PII |
| Integration logs | Google, WhatsApp, DaengtisiaMS | Sync status, OAuth health, error codes |
| Agent/tool-call traces | AI orchestrator | Span per agent run + tool call; prompt/model version |
| Prompt/model version | AI layer | Recorded per run (`.claude/rules/05`) |
| Token usage / AI cost | AI layer | Per-run token + cost logging |
| Latency | App/queue/API/AI | Percentiles per operation |
| Error / retry rate | All layers | Counters per operation and tenant |
| Google sync & OAuth health | Google integration | Success rate, refresh status |
| Notification delivery | Notification layer | Sent/failed per channel |
| DB / Redis / storage health | Infra | Connections, memory, disk, replication |
| Backup / restore status | Backup jobs | Success/failure, last-good timestamp |

## Tracing

- Distributed tracing via an **OpenTelemetry-compatible** exporter (vendor-neutral); backend selected at implementation.
- Every AI agent run produces a trace with spans for each specialist agent and each tool call, carrying prompt version, model version, token usage, cost, and guardrail outcomes (`.claude/rules/05`).
- Traces carry tenant/branch context so activity is attributable and tenant-scoped.
- Error tracking via a **Sentry-compatible** service captures exceptions with context but **without** PII/medical/secret content.

## Metrics and dashboards (planned)

| Dashboard | Key metrics |
|-----------|-------------|
| Application health | Error rate, latency percentiles, throughput |
| Queue health | Backlog depth, job latency, failure rate |
| AI operations | Agent runs, tool-call rate, token/cost, guardrail blocks, human-approval queue depth |
| Integration health | Google sync success, OAuth refresh status, WhatsApp delivery |
| Infrastructure | DB/Redis/storage health, CPU/memory/disk |
| Backup/DR | Last successful backup, restore-drill status |

## Alerting (Master Source §51 minimum set)

| Alert | Trigger | Severity |
|-------|---------|----------|
| High error rate | Error rate over threshold | High |
| Queue backlog | Backlog over threshold / growing | High |
| Agent failure spike | AI run failures over threshold | High |
| Google sync failure | Sync failing repeatedly | Medium–High |
| OAuth refresh failure | Token refresh failing | High |
| DB / storage issues | Health check failing, disk pressure | Critical |
| Backup failure | Backup job failed / stale last-good | High |
| High AI cost | Cost over budget threshold | High |
| PII guardrail failure | Redaction/guardrail failure detected | Critical |
| Tenant-isolation anomaly | Cross-tenant access signal | Critical |

- Critical alerts (PII guardrail, tenant-isolation anomaly, DB failure) page the on-call and enter the incident runbook.
- Alert thresholds are tuned at implementation; the set above is the mandatory minimum.

## Privacy and tenant-safety constraints

- Logs, traces, and metrics **MUST NOT** contain secrets, credentials, tokens, or medical/PII content (`.claude/rules/04`, `.claude/rules/18`).
- Sensitive fields are redacted before emission; AI input redaction is recorded, not the raw sensitive content.
- Tenant-facing logs are tenant-scoped; no cross-tenant data appears in shared views (`.claude/rules/03`).
- Telemetry is stored with least-privilege access and retention aligned to data-governance rules.

## Health checks

- Liveness/readiness endpoints for app, queue worker, and integrations (no sensitive data in responses).
- Synthetic checks for critical flows (survey link resolve, feedback read) once implemented.
- Backup/restore status surfaced as a health signal.

## Log/trace retention and access

| Signal | Baseline retention | Access |
|--------|--------------------|--------|
| App/API/queue logs | 30 days | Least-privilege, audited |
| AI traces + cost | 90 days | Ops + AI-governance roles |
| Security/audit events | Per data-governance retention (long-lived, append-only) | Restricted |
| Metrics | 13 months (trending) | Ops |

- Retention aligns with data-governance rules; sensitive content is never retained in telemetry.

## SLIs and SLOs (planned)

| SLI | Planned SLO (hypothesis) |
|-----|--------------------------|
| API availability | ≥ 99.5% (pilot) |
| API latency p95 | Within a defined budget per endpoint |
| Queue job success rate | ≥ 99% |
| AI structured-output validity | ≥ 99% (ties to §54 AI gate) |
| Google reply human-approval coverage | 100% |

- SLO values are planning hypotheses until measured; they MUST NOT be reported as achieved without evidence.

## Operational gate linkage

- The observability signals, alerts, and dashboards above are prerequisites for the Master Source §54 operational gate.
- Gate evidence requires that alerts fire correctly in test and that traces/cost logging are present; none is claimed until implemented and evidenced.

## Status

Observability plan documented as a Step 4 planning baseline: signal catalog, **OpenTelemetry-compatible** tracing, AI cost/trace logging, dashboards, and the minimum alert set including PII-guardrail and tenant-isolation anomalies. Nothing is instrumented or emitting; tooling selection is EVALUATE DURING IMPLEMENTATION. **PLANNING BASELINE — NOT IMPLEMENTED.**
