# Pilot RACI — Aish Agentic AI

**Document:** Pilot RACI and Compensation Authority (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §4 (personas), §9 (use cases), §10 (recovery), §11
(compensation), §12 (Google review). RACI = Responsible, Accountable, Consulted, Informed. No activity
here is running; the pilot has not started (implementation NOT STARTED).

---

## 1. Persona legend

- **BO** — Business Owner / Executive Sponsor
- **PC** — Pilot Coordinator / Corporate Admin
- **BM** — Branch Manager
- **RA** — Recovery Assignee / Customer Service
- **RAP** — Reputation Approver
- **PS** — Platform Support / AI Operations (support role)
- **AN** — Read-only Analyst / Auditor (supporting)

A single person MAY hold compatible roles (Persona §5), but a combination MUST NOT remove meaningful
approval on high-risk actions.

## 2. RACI matrix

| Pilot activity | BO | PC | BM | RA | RAP | PS | AN |
|---|---|---|---|---|---|---|---|
| Tenant/branch onboarding (UC-P0-01) | A | R | C | I | I | C | I |
| Survey configuration (UC-P0-02) | A | R | C | I | I | I | I |
| Completed-service event intake (UC-P0-03) | I | A | I | I | I | R | I |
| Invitation creation/sending (UC-P0-04) | I | A | C | R | I | C | I |
| Feedback analysis + triage (UC-P0-06/07) | I | C | A | R | I | C | I |
| Recovery assignment/escalation (UC-P0-08) | C | I | A | R | I | I | I |
| Private customer contact (UC-P0-09) | I | I | A | R | I | I | I |
| Resolution/closure (UC-P0-10) | C | I | A | R | I | I | I |
| Google Business Profile connection (UC-P0-11) | A | R | I | I | C | C | I |
| Review sync (UC-P0-12) | I | C | I | I | C | R | I |
| Review reply approval/publish (UC-P0-13) | C | I | I | I | A/R | I | I |
| Dashboards (UC-P0-14) | A | C | R | I | I | I | C |
| Audit/evidence export (UC-P0-15) | A | R | I | I | I | C | R |
| Incident handling / degraded mode (UC-P0-16) | A | C | C | R | C | R | I |
| Compensation approval (§11) | A | I | R (recommends) | C | I | I | I |

**Reading notes:**

- Exactly one **A** (Accountable) per activity; **R** may be shared.
- Review reply publish (UC-P0-13): RAP is Accountable and Responsible; AI cannot self-publish; human
  approval is mandatory on pilot (Persona §12; `.claude/rules/06`).
- Private customer contact (UC-P0-09): RA is Responsible but MUST NOT promise compensation (see §3).
- Event intake and review sync are system-driven; PS is Responsible for the machine workflow and its
  audited health, never for public publishing (Persona §4.8, §4.10).

## 3. Compensation authority matrix (Persona §11)

Compensation authority is strictly bounded. No fictitious nominal limits are created here; permitted
amounts and remedies MUST come from approved Daengtisia policy (Persona §11).

| Role | Authority in the first pilot |
|---|---|
| AI agent | Suggest only; MUST NOT approve, promise, or execute compensation |
| Recovery Assignee (RA) | MAY apologize and propose non-financial next steps; MUST NOT make any financial commitment |
| Branch Manager (BM) | MAY recommend refund/discount/compensation; approval still required |
| Business Owner / designated approver (BO) | MAY approve per written tenant policy |
| Platform Support (PS) | No tenant compensation authority |

**Enforceable rules:**

- Compensation MUST NOT be tied to a positive or changed review (Persona §10.2, §12).
- Customers MUST NOT be asked to delete/alter a review as a condition of recovery (Persona §10.2).
- Every compensation approval MUST be audited and trace to written policy (Persona §11; `.claude/rules/07`).
- The approver of a high-risk compensation MUST NOT be the same person who requested it as sole authority
  (separation of duty; `PILOT_PERSONA_MATRIX.md` §3.1).

## 4. Truthful status

This RACI and authority matrix describe intended responsibilities for a pilot that has not run.
Application implementation, pilot readiness, and pilot runtime are NOT STARTED.
