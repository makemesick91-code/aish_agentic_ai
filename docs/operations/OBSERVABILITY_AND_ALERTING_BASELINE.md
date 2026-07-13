# Observability and Alerting Baseline (Step 3) — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §51 · **Rules:** `.claude/rules/07`, `11`, `20` ·
**ADR:** [0024](../decisions/adr/0024-observability-audit-correlation-redaction.md).

Extends the Step 1 [OBSERVABILITY_BASELINE](OBSERVABILITY_BASELINE.md) with the Step 3 architecture view; see
[Observability Architecture](../architecture/OBSERVABILITY_ARCHITECTURE.md) and
[AI Observability & Cost](../ai/AI_OBSERVABILITY_AND_COST_ARCHITECTURE.md).

## Signals
Structured app/queue/API/integration logs; agent + tool-call traces; prompt/model version; token/cost/latency;
error/retry; Google sync & OAuth health; notification delivery; DB/Redis/storage health; backup/restore status;
Correlation-Id propagation.

## Minimum alerts (Master Source §51)
High error rate · queue backlog · agent failure spike · Google sync failure · OAuth refresh failure ·
DB/storage issues · backup failure · high AI cost · PII guardrail failure · **tenant-isolation anomaly**.

## Privacy
Redaction before logging/AI input (no secret/PII/`MED`). Audit is a separate append-only, tenant-scoped trail.

## Assertion
No collector, dashboard, or alert runs in Step 3; this is a planned baseline.
