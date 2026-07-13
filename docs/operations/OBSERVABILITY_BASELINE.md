# Observability Baseline — Aish Agentic AI

Canonical: Master Source §51, §21. Rule: `.claude/rules/11`. PRD §15.3.

## Required telemetry (Master Source §51)
Structured app/queue/API/integration logs · agent traces · tool-call traces · prompt version · model
version · token usage · cost · latency · error rate · retry rate · Google sync health · OAuth health ·
notification delivery · DB health · Redis health · storage health · backup status · restore status.

## Minimum alerts (Master Source §51)
High error rate · queue backlog · agent failure spike · Google sync failure · OAuth refresh failure ·
DB issue · storage issue · backup failure · high AI cost · PII guardrail failure · tenant-isolation anomaly.

## Principles
OpenTelemetry-compatible; Sentry (or equivalent) for errors. Telemetry MUST NOT contain secrets or
unredacted PII (`.claude/rules/04`). Metrics feed the Owner/Branch/CX/Reputation/AI-Operations dashboards
(Master Source §41). Operational gate must pass before a production release (`RELEASE... ` see below).

## Operational gate (Master Source §54)
Monitoring active · alerts active · logging active · queue worker active · backup active · restore tested ·
incident runbook available · support workflow available · rollback plan available — evidence required.

**Status:** observability baseline documented. Instrumentation at implementation (NOT STARTED).
