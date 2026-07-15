# Experience OS — Reliability & Observability Contract (Step 9 Design Baseline)

**Status:** DESIGN BASELINE — NOT IMPLEMENTED (no dashboard/alert is provisioned in Step 9)
**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Owner domain:** Cross-cutting (operations)
**Related:** rule 34; rule 11 (`docs/operations/OBSERVABILITY_BASELINE.md`, preserved); ADRs 0065, 0067, 0068
**Canonical repo:** makemesick91-code/aish_agentic_ai

---

## 1. Purpose

Define the observability signals, dashboards, alerts, and SLO candidates that the Experience OS expansion must emit so
that Customer 360, Recovery, Reputation, Conversations, and AI can be operated truthfully and safely. This refines —
it does not replace — the rule 11 `docs/operations/OBSERVABILITY_BASELINE.md`.

---

## 2. Required signals

| Signal | Description | Owner | Alert threshold (candidate) |
|--------|-------------|-------|-----------------------------|
| Structured logs | App/queue/API/integration logs carry tenant context; no PII/secrets/tokens (rule 30) | Cross-cutting | Log error-rate spike |
| Correlation & causation IDs | Every workflow carries a correlation id; tool/agent runs carry a trace id and causation chain | AI / Ledger | Missing-correlation rate |
| Queue metrics | Depth, throughput, retry rate, DLQ size per queue | Runtime | Backlog / DLQ growth |
| Backfill progress | processed/total/failed, last cursor, ETA per backfill job | Data platform | Stalled backfill (no progress) |
| Projection lag | Lag between source event `recorded_at` and projection apply | Ledger / Analytics | Lag above budget |
| Provider health | Per-channel/reputation provider up/down, latency, error rate | Adapter / Reputation | Provider error-rate / circuit-open |
| AI latency/failure/guardrail/cost | Per-agent latency, failure rate, guardrail pass/fail, token + cost | AI control plane | Cost ceiling approach; guardrail-failure spike |
| Identity-link confidence distribution | Distribution of deterministic vs suggested links; suggestion accept/reject | Identity Resolution | Anomalous suggestion volume |
| Merge/split audit anomalies | Rate of merges/splits; unusual bulk merges | Identity Resolution | Merge-rate anomaly |
| Cross-tenant anomaly | Any query/write crossing a tenant boundary | Security | **Any occurrence = page** |
| Dead-letter visibility | DLQ contents per domain, redrive status | Runtime | DLQ non-empty aging |
| Reconciliation status | Last run, drift found, drift resolved per `aish:*-reconcile` | Data platform | Reconcile drift > 0 unresolved |

---

## 3. Required dashboards

- **Tenant isolation & security:** cross-tenant anomaly counter (target 0), IDOR/403 trends, secret-scan status,
  audit immutability checks.
- **Queue & reliability:** queue depth/throughput/retry/DLQ; backfill progress; projection lag; reconciliation drift.
- **Provider & integration health:** per-provider up/down, latency, circuit-breaker state, moderation/unknown-state
  reconciliation.
- **AI operations:** per-agent latency/failure/guardrail/cost; approval queue depth and age; kill-switch state.
- **Customer 360 quality:** identity-link confidence distribution, merge/split rates, duplicate-suspect count.

---

## 4. Minimum alerts (extends rule 11)

High error rate; queue backlog; agent-failure spike; provider/OAuth failure; DB/Redis/storage issues; backup failure;
high AI cost (approaching ceiling); PII/guardrail failure; **tenant-isolation anomaly (page immediately)**;
merge/split anomaly; stalled backfill; unresolved reconciliation drift; stale-approval execution attempt.

---

## 5. SLO candidates

- Availability of `/ready` mandatory dependencies.
- Feedback projection latency (event → item) p95 within budget.
- Notification/message delivery acceptance latency p95.
- AI run success rate and p95 latency (once basic AI exists).
- Reconciliation drift resolved within a target window.
- Zero cross-tenant anomalies (hard objective, not a percentage).

## 6. Evidence retention

Traces, cost logs, guardrail results, audit, and reconciliation reports are retained per data-governance retention
(rule 07) and legal hold; evidence is tenant-safe and contains no raw PII/medical content.

## 7. Out of scope for Step 9

No dashboard, alert, or exporter is provisioned in Step 9. This contract is implemented incrementally alongside each
Wave-1+ capability and verified before pilot (rule 26: observability and tested restore precede pilot).
