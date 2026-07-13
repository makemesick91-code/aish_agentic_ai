# Observability Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §51 · PRD v1.2.0 §15.3 · **Rules:** `.claude/rules/07`, `11`, `20` ·
**ADR:** [0024](../decisions/adr/0024-observability-audit-correlation-redaction.md).

## 1. Signals (planned)
Structured app/queue/API/integration logs; agent + tool-call traces; prompt/model version; token usage; AI
cost; latency; error/retry rate; Google sync & OAuth health; notification delivery; DB/Redis/storage health;
backup/restore status. Correlation-Id threads request→job→event→external call (ADR 0016, 0024).

## 2. Audit vs telemetry
- **Audit** (Audit module) is append-only, non-deletable, tenant-scoped, and covers every important action
  (admin access, exports, deletions, approvals, publishes, role changes).
- **Telemetry** (logs/metrics/traces) is operational and **MUST** redact secrets and PII (no `MED` ever). No
  sensitive data in logs (`.claude/rules/04`, ADR 0024).

## 3. Minimum alerts (Master Source §51)
High error rate · queue backlog · agent failure spike · Google sync failure · OAuth refresh failure ·
DB/storage issues · backup failure · high AI cost · PII guardrail failure · **tenant-isolation anomaly**.

## 4. Redaction
A redaction layer strips PII/secret/`MED` fields before logging or AI input. Guardrail failures are recorded as
security events. Tenant-facing logs are tenant-scoped (no cross-tenant leakage, ADR 0015).

## 5. Truthful status
No collector, dashboard, or alert runs in Step 3. This is the planned baseline; operational evidence is future.
See [OBSERVABILITY_AND_ALERTING_BASELINE](../operations/OBSERVABILITY_AND_ALERTING_BASELINE.md).
