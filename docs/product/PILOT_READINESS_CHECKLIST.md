# Pilot Readiness Checklist — Aish Agentic AI

**Document:** Pilot Readiness Checklist (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §13.1 (preparation ≤ 2 weeks), §5, §6, §8, §10, §12, §15.
These items MUST pass BEFORE pilot runtime begins. Because Step 2 is documentation only, every item is
marked **NOT STARTED** — this checklist defines what must later be verified; it does not certify readiness.
Pilot runtime MUST NOT begin until these gates pass (Persona §13; `PILOT_GO_WATCH_NO_GO.md`).

---

## Status legend

- **NOT STARTED** — not yet begun (current state for every item during the documentation phase).
- Later phases will use the truthful vocabulary (IN PROGRESS, CONFIGURED, VERIFIED) with evidence only.

## 1. People and roles

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-01 | Five named pilot users covering the minimum role set (Owner, Coordinator, Branch Manager, Recovery Assignee, Reputation Approver), compatible-role combinations recorded, no meaningful approval removed | Pilot Coordinator | Named-role roster + combination record (Persona §5) | NOT STARTED |
| R-02 | Role training completed for each named operator | Pilot Coordinator | Training completion record (Persona §15) | NOT STARTED |
| R-03 | Branch champion for Daengtisia Pusat (or approved alternative via decision log) designated | Business Owner | Decision-log entry (Persona §2, §13.1) | NOT STARTED |

## 2. Google and channels

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-04 | Google account/location ownership verified for an authorized Daengtisia representative | Business Owner / Integration Admin | Ownership verification (Persona §12, §13.1) | NOT STARTED |
| R-05 | WhatsApp survey-link channel and QR fallback prepared; email optional | Pilot Coordinator | Channel configuration (Persona §7.2; `../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`) | NOT STARTED |
| R-06 | Honest handling of Google BLOCKED state confirmed (pilot can run CSAT/recovery without Google) | Pilot Coordinator | BLOCKED-state plan (Persona §12) | NOT STARTED |

## 3. Policy and content

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-07 | Approved privacy notice and consent text | Business Owner | Approved text snapshot (Persona §6.3, §13.1) | NOT STARTED |
| R-08 | Approved contact policy (private channel, max 2 attempts / 3 business days, opt-out) | Business Owner | Contact policy record (Persona §10.2) | NOT STARTED |
| R-09 | Approved brand voice for Google replies | Reputation Approver | Brand voice guide (Persona §12.1) | NOT STARTED |
| R-10 | Approved Daengtisia compensation policy (amounts/remedies; no fictitious limits) | Business Owner | Compensation policy (Persona §11; `PILOT_RACI.md`) | NOT STARTED |

## 4. Configuration

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-11 | Versioned survey configured (CSAT 1–5, CES 1–5, NPS 0–10, comment, conditional follow-up) | Pilot Coordinator | Published survey version (Persona §6; UC-P0-02) | NOT STARTED |
| R-12 | Severity mapping and SLA baseline configured (Critical/High/Medium/Low) | Branch Manager | Severity/SLA config (Persona §10.1) | NOT STARTED |
| R-13 | Knowledge base and approved templates configured | Pilot Coordinator | KB/template record (Persona §13.1) | NOT STARTED |
| R-14 | Invitation rules configured (delay 30–120 min, window 09:00–20:00, cap 1/14 days, 1 reminder, 7-day expiry, opt-out) | Pilot Coordinator | Invitation-rule config (Persona §7.2) | NOT STARTED |

## 5. Data and integration

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-15 | Minimum data mapping validated; prohibited fields excluded (diagnosis, clinical notes, MRN, prescription, odontogram, etc.) | Pilot Coordinator | Mapping + prohibited-field test (Persona §8; `../security/PILOT_DATA_BOUNDARY.md`) | NOT STARTED |
| R-16 | DaengtisiaMS event contract (authenticated API/webhook) agreed; CSV/QR fallback labelled honestly | Integration Admin | Contract baseline (Persona §7.3; `../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`) | NOT STARTED |
| R-17 | Test records validated end-to-end (no real PII in repo) | Pilot Coordinator | Test-record set (Persona §13.1, §15) | NOT STARTED |
| R-18 | Baseline data or baseline-capture method established | Read-only Analyst | Baseline record (Persona §13.1) | NOT STARTED |

## 6. Safety controls

| # | Readiness item | Owner | Evidence required | Status |
|---|---|---|---|---|
| R-19 | Human-approval workflow active for every public reply | Reputation Approver | Approval-workflow config (Persona §12; UC-P0-13) | NOT STARTED |
| R-20 | Kill switch and idempotent controlled retry verified | Platform Support | Kill-switch/retry test (Persona §14.1; UC-P0-16) | NOT STARTED |
| R-21 | Tenant-isolation controls verified across all surfaces | Platform Support | Isolation test (`.claude/rules/03`) | NOT STARTED |
| R-22 | Audit trail active for all material actions | Platform Support | Audit configuration (`.claude/rules/07`) | NOT STARTED |

## 7. Gate to runtime

Pilot runtime (Persona §13.2 onward) MUST NOT start until items R-01…R-22 pass with evidence. Passing this
checklist establishes readiness only; it does not by itself grant pilot-expansion GO — that is decided in
`PILOT_GO_WATCH_NO_GO.md`. Truthful status remains: application implementation NOT STARTED; pilot readiness
and pilot runtime NOT STARTED.
