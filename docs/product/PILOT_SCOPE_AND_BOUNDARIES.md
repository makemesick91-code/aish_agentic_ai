# Pilot Scope and Boundaries — Aish Agentic AI

**Document:** Pilot Scope and Boundaries (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

This document derives from `PERSONA_AND_PILOT_USE_CASES.md` (Persona §2, §3, §9.2, §9.3, §17, §18)
and the canonical Master Source / PRD. Every boundary below traces to those sources. It makes no claim
that application code, database, AI runtime, Google integration, deployment, or pilot runtime exists.

---

## 1. Pilot objective

The first pilot MUST prove that Aish Agentic AI can safely and consistently (Persona §3): collect
post-service feedback, compute CSAT / NPS / CES, detect negative / high-risk / repeated feedback,
create and track customer recovery tickets, assist staff without replacing human decisions, synchronize
Google Reviews when authorized access exists, draft safe replies routed to human approval, and present
accurate action-oriented dashboards — while preserving audit trail and truthful external states, and
remaining usable when AI or an external provider is degraded.

The pilot MUST be evaluated on reliability, safety, response, recovery operation, adoption, and evidence.
It MUST NOT be evaluated by forcing a rise in public rating (Persona §2 item 19).

## 2. Pilot subject

- **Pilot tenant:** Klinik Gigi Daengtisia.
- **Recommended first pilot branch:** Daengtisia Pusat. This is a recommendation because it is the main
  location; it is NOT a claim of staff or Google Business Profile readiness. If readiness is not met, the
  owner MAY substitute one alternative branch through the decision log without changing product scope
  (Persona §2 item 2). Final branch is subject to readiness verification.
- The pilot MUST start from **one branch and one Google location**. The core architecture MUST remain
  multi-tenant and multi-branch regardless (Persona §2 item 3; Master Source §17; `.claude/rules/03`).

## 3. In scope for the first pilot

The first pilot is limited to a single tenant, single branch, and single Google location, covering:

- **Onboarding and configuration:** tenant, pilot branch, timezone (Asia/Makassar), roles, survey,
  severity mapping, SLA, knowledge base, consent/privacy text (Persona §9.1 UC-P0-01, UC-P0-02; §13.1).
- **Completed-service event intake:** eligible `VisitCompleted` from DaengtisiaMS via authenticated
  API/webhook, with controlled CSV/manual import and on-site QR as honestly-labelled fallbacks
  (Persona §7.3, §9.1 UC-P0-03).
- **Invitation and survey:** unique WhatsApp survey link (primary), QR fallback (mandatory), email
  (optional); CSAT/CES/NPS + optional comment + conditional follow-up (Persona §6, §7, UC-P0-04, UC-P0-05).
- **Feedback analysis with manual fallback:** sentiment, topic, severity, risk, summary, confidence,
  suggested action; manual classification when AI is unavailable (Persona §9.1 UC-P0-06).
- **Customer recovery:** automatic ticket creation, triage, assignment, escalation, private contact,
  resolution and closure under severity/SLA baseline (Persona §9.1 UC-P0-07…UC-P0-10; §10).
- **Google Review with human approval:** connect profile, sync reviews, draft reply, human approval,
  publish — every reply human-approved on pilot; no gating (Persona §9.1 UC-P0-11…UC-P0-13; §12;
  `.claude/rules/06`).
- **Dashboards, audit, and export:** owner/branch dashboards reconciled to source, permissioned audit
  export (Persona §9.1 UC-P0-14, UC-P0-15).
- **Degraded-mode operation:** survey, manual triage, ticketing, approval, and audit remain usable when
  AI or the provider fails (Persona §9.1 UC-P0-16).

The full P0 catalog is documented in `PILOT_USE_CASE_CATALOG.md`.

## 4. Explicitly out of scope for the first pilot (Persona §18)

The following MUST NOT be built or enabled in the first pilot without a new versioned decision
(`.claude/rules/02`, `.claude/rules/12`):

- Auto-publish of Google replies (all replies MUST pass human approval — Persona §12; `.claude/rules/06`).
- Automated refund, discount, or compensation (AI is suggest-only — Persona §11).
- Medical diagnosis or clinical advice.
- Sending a full medical record to the AI provider (prohibited-field list — Persona §8.2).
- Voice / call agent.
- All social media channels beyond Google.
- Multi-branch rollout before first-branch evidence exists.
- Advanced churn prediction.
- Full marketing automation.
- Native mobile app.
- Replacing the DaengtisiaMS clinical workflow.

## 5. Deferred (P1 / P2) items

These are recognized but MUST remain deferred until earned by evidence.

**P1 — valuable at stabilization (Persona §9.2):** single reminder with opt-out and delivery audit;
weekly owner digest; approved knowledge-base templates and branch information; root-cause trend and
repeated-complaint detection; saved filters and assignment queue; pilot scorecard and cost-per-run
report; safe bulk assignment without bulk auto-publish.

**P2 — deferred until evidence supports (Persona §9.3):** fully automated WhatsApp Business Platform
delivery; multi-branch pilot expansion; controlled low-risk auto-publish (only under Master Source §16.4
preconditions); advanced predictive analytics; social media inbox beyond Google; voice/call agent;
automated refund/discount/compensation.

## 6. Generic-core boundary

The core product MUST stay generic (Persona §17 risk row; `.claude/rules/01`). Clinic-specific concerns —
DaengtisiaMS mapping, healthcare prohibited fields, dental terminology — MUST live at the integration and
configuration boundary, not inside the core domain model. A pilot tenant (Klinik Gigi Daengtisia) MUST NOT
narrow the core to a single industry. See `DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md` (integration boundary)
and `PILOT_DATA_BOUNDARY.md` (data boundary).

## 7. Truthful status

Step 2 completes documentation only. Application implementation, deployment, pilot readiness, and pilot
runtime are all NOT STARTED. No mock or manual import MAY be presented as real-time integration success
(Persona §7.3, §12). This document does not authorize any runtime action.
