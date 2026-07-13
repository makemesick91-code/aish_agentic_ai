# Pilot Success Metrics — Aish Agentic AI

**Document:** Pilot Success Metrics (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §14 (metrics) and §15 (evidence). This document separates
mandatory **hard safety/correctness gates** from **operational targets**, which are pilot hypotheses, not
results. No metric here is a measured outcome; the pilot has not run (implementation NOT STARTED).

---

## 1. Hard safety and correctness gates (mandatory — NO-GO if breached)

A confirmed breach of any gate below is a NO-GO until fixed and re-tested (Persona §14.1, §16). These are
non-negotiable and MUST NOT be weakened by a token-saving skill or convenience (`.claude/rules/09`, `14`).

| # | Hard gate | Threshold | Source of truth |
|---|---|---|---|
| G1 | Confirmed cross-tenant data exposure | Zero | Access/audit logs, isolation tests (`.claude/rules/03`) |
| G2 | Unauthorized public reply | Zero | Publication log + approval records (UC-P0-13) |
| G3 | Known PII / medical leakage in a public reply | Zero | Guardrail results + reply audit (`../security/PILOT_PUBLIC_REPLY_SAFETY.md`) |
| G4 | Public replies with recorded human approval | 100% | Approval records (UC-P0-13) |
| G5 | Critical incidents with owner, timeline, and audit evidence | 100% | Incident log (`.claude/rules/11`) |
| G6 | External action called "success" before provider verification | Never | Publication/sync states (`PILOT_WORKFLOW_STATES.md`) |
| G7 | Duplicated invitation / ticket / reply from retry or idempotency failure | Zero | Idempotency keys + reconciliation (UC-P0-04, 07, 16) |

Additional structural gates (Persona §14.1, §16; `.claude/rules/03`): queue, cache, file storage, search,
export, AI-retrieval, analytics, and notifications MUST all be tenant-scoped; an audit trail MUST exist for
all important actions; the manual workflow MUST remain usable when AI is unavailable.

## 2. Operational targets (hypotheses — NOT results)

The following are pilot hypotheses to be tested, not guarantees and not evidence of implementation
(Persona §14.2). They MUST NOT be reported as achievements before the pilot runs and reconciles data.

| Metric | Initial target |
|---|---:|
| Eligible invitation creation success | ≥ 90% |
| Delivery success for sendable invitation | ≥ 85% |
| Survey response rate | ≥ 20% |
| Completion rate after survey opened | ≥ 80% |
| Negative feedback triaged | ≥ 95% |
| Critical/high first response within SLA | ≥ 90% |
| Google reviews with reply/approved disposition within 48 h | ≥ 90% |
| Median Google reply time | < 24 h |
| Structured AI output validity | ≥ 99% |
| Critical/high severity recall on evaluation set | ≥ 95% |
| Weekly active operational users | ≥ 80% of named operators |
| Dashboard / source reconciliation for release-critical KPI | 100% |

Human edit rate, cost per AI run, recovery rate, repeat-complaint rate, and rating trend are measured
without forcing a favorable threshold in the first pilot (Persona §14.2).

## 3. KPI dictionary (definition · source of truth · reconciliation)

| KPI | Definition | Source of truth | Reconciliation |
|---|---|---|---|
| Eligible invitation creation success | Invitations created ÷ eligible events | Event log + invitation records | Reconcile event count to invitation count per branch/day |
| Delivery success | Delivered ÷ sendable invitations | Provider receipts | Provider receipt vs invitation state (`shared` excluded from `delivered`) |
| Survey response rate | Responses submitted ÷ invitations opened path | Invitation + response records | Response IDs traced to invitation IDs |
| Completion rate | Submitted ÷ opened | Survey response states | Opened vs submitted counts |
| Negative feedback triaged | Negative feedback with a triage decision ÷ total negative | Analysis + ticket records | Negative analyses vs created tickets |
| Critical/high first response within SLA | Tickets meeting first-contact SLA ÷ critical/high tickets | Ticket SLA timeline | SLA clock vs acknowledgement/contact timestamps (Persona §10.1) |
| Google disposition ≤ 48 h | Reviews replied/approved within 48 h ÷ eligible reviews | Review + reply states | Review sync time vs approval/publish time |
| Median Google reply time | Median(publish/approve time − review time) | Reply publication log | Verified `published` timestamps only |
| Structured AI output validity | Valid structured outputs ÷ AI runs | AI run trace | Schema validation results |
| Critical/high recall | True critical/high detected ÷ labelled critical/high | AI evaluation set | Model output vs human labels (`../ai/PILOT_AI_EVALUATION_PLAN.md`) |
| Weekly active operators | Distinct named operators active ÷ named operators | Auth/audit logs | Active users vs named-role roster |
| Reconciliation | Release-critical KPI equal to source records | Dashboard vs source | 100% match required (UC-P0-14) |
| Cost per AI run | AI cost ÷ AI runs | Cost log | Cost log vs provider billing |

## 4. Truthful reporting rules

- Targets in §2 are hypotheses; reports MUST label them as such until pilot data exists (Persona §14.2).
- A dashboard value MUST reconcile to source records before it is release-critical (UC-P0-14; §3 above).
- No metric MAY be reported as achieved before the pilot runs; implementation and runtime are NOT STARTED.
- Evidence MUST be tenant-safe and MUST NOT store real customer PII in the repository (Persona §15).

See `PILOT_GO_WATCH_NO_GO.md` for how these gates and targets feed the pilot-expansion decision.
