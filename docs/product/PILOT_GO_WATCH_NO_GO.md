# Pilot GO / WATCH / NO-GO — Aish Agentic AI

**Document:** Pilot GO / WATCH / NO-GO Criteria (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §16. This defines the decision to expand the pilot after it
has run. It is evaluated only at the end of the pilot operation phase (Persona §13.4) and has NOT been
evaluated — the pilot has not run (implementation and runtime NOT STARTED).

---

## 1. Scope of this decision — not the Step 2 documentation GO tag

This document governs the **pilot-expansion** decision. It is distinct from the Step 2 documentation GO
tag and from the documentation-foundation GO tag `aish-agentic-ai-docs-foundation-v1.0.0-go`, which attests
documentation/tooling readiness only and NOT that the application is implemented, deployed, pilot-ready, or
production-ready (`.claude/rules/13`; CLAUDE.md §7). A documentation GO MUST NOT be read as a pilot GO.

| Decision | What it attests | Where defined |
|---|---|---|
| Documentation foundation GO tag | Docs/tooling readiness only | `.claude/rules/13`; `docs/release/` |
| Step 2 documentation baseline | Persona/pilot documentation complete | `PERSONA_AND_PILOT_USE_CASES.md` §19 |
| Pilot readiness | Preparation gates passed before runtime | `PILOT_READINESS_CHECKLIST.md` |
| Pilot-expansion GO/WATCH/NO-GO (this doc) | Evidence from a run pilot supports limited expansion | Persona §16 |

## 2. GO for limited expansion (Persona §16)

A GO requires ALL of the following, with evidence:

1. All hard safety/correctness gates pass (`PILOT_SUCCESS_METRICS.md` §1; Persona §14.1).
2. No unresolved critical incident.
3. Workflow is usable by the named pilot roles (Persona §5).
4. Primary operational targets are substantially met OR have an approved remediation plan (Persona §14.2).
5. AI/external failure paths are honest and recoverable (UC-P0-16).
6. Cost is measured and acceptable.
7. The owner approves the next rollout scope.

## 3. WATCH

- No critical safety breach, but adoption, response rate, cost, AI quality, or integration targets still
  need improvement.
- Expansion is limited until corrective action is verified (Persona §16).

## 4. NO-GO (any one is sufficient)

- Cross-tenant data exposure.
- Unauthorized publishing.
- PII / medical leakage.
- Falsified success state.
- Uncontrolled duplicate action.
- Critical permission failure.
- Unresolved critical incident.
- Missing evidence for a release-critical claim.

Any hard-gate breach (`PILOT_SUCCESS_METRICS.md` §1) is a NO-GO until fixed and re-tested (Persona §14.1).

## 5. Decision inputs

| Input | Source |
|---|---|
| Hard gate results | `PILOT_SUCCESS_METRICS.md` §1 |
| Operational target results | `PILOT_SUCCESS_METRICS.md` §2 (labelled hypotheses until measured) |
| Incident/failure log | `.claude/rules/11`; UC-P0-16 |
| Human-approval and publication evidence | UC-P0-13; `../security/PILOT_PUBLIC_REPLY_SAFETY.md` |
| Data reconciliation report | UC-P0-14; UC-P0-15 |
| Cost and usage report | `PILOT_SUCCESS_METRICS.md` §3 |
| Named-role adoption | `PILOT_PERSONA_MATRIX.md`; `PILOT_RACI.md` |

## 6. Truthful status

No GO, WATCH, or NO-GO has been decided. This document defines the criteria only. Any decision MUST follow
a run pilot with evidence and an owner decision recorded in the decision log, and MUST NOT be fabricated
(`.claude/rules/13`; CLAUDE.md §5). Current state: application implementation NOT STARTED; pilot runtime
NOT STARTED.
