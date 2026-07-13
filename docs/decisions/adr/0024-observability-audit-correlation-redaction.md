# ADR 0024 — Observability, Audit, Correlation, and Redaction

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** DevOps / Platform Architect
- **Rule:** `.claude/rules/07`, `11`, `20` (AFR-057..060) · **Canonical:** Master Source v2.3.0 §51; PRD v1.2.0 §15.3

## Context
The system must be observable and auditable without leaking secrets/PII, with correlation across request→job→
event→external call, and a distinct append-only audit trail.

## Decision
Emit structured logs, metrics, and traces with **prompt/model version, token, cost, latency, error/retry**
signals and **Correlation-Id** propagation. Maintain a separate **append-only, non-deletable, tenant-scoped
audit** log for important actions. A **redaction layer** strips secrets/PII/`MED` before logging or AI input.
Minimum alerts include a **tenant-isolation anomaly**. See
[Observability Architecture](../../architecture/OBSERVABILITY_ARCHITECTURE.md) and
[AI Observability & Cost](../../ai/AI_OBSERVABILITY_AND_COST_ARCHITECTURE.md).

## Alternatives
- **Logs as audit** — rejected: logs are mutable/rotated; audit must be immutable.
- **No redaction** — rejected: leaks PII/secrets into telemetry.

## Consequences
Debuggable, auditable, privacy-safe operations; requires a redaction layer and audit module.

## Impacts
- **Security:** guardrail failures + isolation anomalies are security events.
- **Privacy:** redaction mandatory; no `MED`/secret in telemetry.
- **Tenant isolation:** tenant-facing logs scoped (FF-TEN-14).
- **Database:** audit_logs/security_events (append-only).
- **Operational:** the core subject — signals + minimum alerts.
- **Cost:** AI token/cost logged and alertable.

## Verification / fitness function
FF-TEN-14, FF-AI-04, FF-DOC-03. Implementation: redaction + audit-immutability tests.

## Related
Requirement: Master Source §51; PRD §15.3. Application rule: AFR-057..060. ADRs: 0016, 0019, 0029.

## Evidence
`docs/architecture/OBSERVABILITY_ARCHITECTURE.md`, `docs/operations/OBSERVABILITY_AND_ALERTING_BASELINE.md`.

## Non-claims
No collector, dashboard, alert, or audit record runs in Step 3.

## Rollback / supersession
Audit immutability and redaction are permanent; superseded only by a governance ADR + Master Source update.
