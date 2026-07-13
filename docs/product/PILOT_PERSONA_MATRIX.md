# Pilot Persona Matrix — Aish Agentic AI

**Document:** Pilot Persona Matrix (Step 2 derived)
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Derived from `PERSONA_AND_PILOT_USE_CASES.md` §4 (persona model) and §5 (minimum role coverage),
and Master Source §13 / PRD §7 (personas). No claim of application, deployment, or runtime is made.

---

## 1. How to read this matrix

Every persona below carries a data scope (tenant or branch), a permission summary, hard constraints, a
success indicator, and whether the persona MUST be a console operator in the first pilot. Data scope MUST
be enforced on every surface (`.claude/rules/03`). Constraints marked MUST / MUST NOT are enforceable.

## 2. Persona matrix

| Persona | Class | Goal | Key pages | Data scope | Permissions (summary) | Hard constraints | Success indicator | Required console operator in first pilot |
|---|---|---|---|---|---|---|---|---|
| Business Owner / Executive Sponsor (§4.2) | Primary | Know CX, branch risk, open complaints, Google reputation, needed action | Owner dashboard, executive summary, branch trend, critical ticket, SLA, Google rating, audit, pilot scorecard | Whole tenant | Set scope/policy/escalation; approve compensation and privacy; GO/NO-GO | MUST NOT bypass audit, privacy, permission, or public-reply approval | Finds most urgent issue and its PIC within 5 minutes | Yes |
| Pilot Coordinator / Corporate Admin (§4.3) | Primary | Set up tenant and keep pilot running | Tenant settings, branch, users, survey builder, campaign, integration health, notification rules, audit | Tenant (config) | Manage users/branch/survey/campaign/invitation rules/KB/consent/integration state; weekly evidence | MUST NOT grant excessive permission or publish replies without correct role | Config complete, reproducible, no direct production-data edit | Yes |
| Branch Manager (§4.4) | Primary | See branch feedback, assign PIC, meet SLA, reduce repeat root causes | Branch dashboard, action queue, feedback detail, ticket detail, SLA, root-cause report | Own branch only | Triage, assign, escalate, review resolution, review branch action | Sees only own branch; MUST NOT publish without approval role | All critical/high feedback acknowledged and assigned within SLA | Yes |
| Recovery Assignee / Customer Service (§4.5) | Primary | Contact customer privately, document outcome, complete recovery | My tasks, ticket detail, contact history, approved templates, resolution checklist | Assigned branch/tickets | Read ticket, use approved contact draft, log attempts, add notes, propose corrective action, request escalation | MUST NOT promise refund/discount/compensation, make legal admission, or publish public reply | Customer receives courteous follow-up; ticket evidence complete | Yes |
| Reputation Approver (§4.6) | Primary | Publish safe, consistent, timely Google Review replies | Review inbox, review detail, draft comparison, guardrail result, approval queue, publication log | Tenant (mapped Google location) | Assess AI draft, check privacy, edit, approve/reject, publish, monitor publication state | MUST NOT gate reviews, request ratings, reply aggressively, or use hidden auto-publish | Professional reply, no private/medical data, external state verified | Yes |
| Read-only Analyst / Auditor (§4.7) | Supporting | Validate trends, data accuracy, control effectiveness without changing data | Analytics, audit logs, pilot scorecard, data reconciliation, evidence export | Tenant (read-only) | Read-only; export MUST be permissioned and audited | MUST NOT mutate operational data | Reproduces KPI and traces material action | No (0–1 optional) |
| Platform Support / AI Operations (§4.8) | Supporting | Monitor tenant health, integration/queue/agent failure, cost, guardrail events | Platform admin, failed agent runs, integration health, cost, trace, incident log, support notes | Platform (audited) | Diagnose failures; support access audited | MUST NOT cross-tenant expose, silently mutate, or publish public content | Failures diagnosed without cross-tenant exposure or silent mutation | No (platform side) |
| Customer / Wali (§4.1) | External | Give feedback quickly and safely; request follow-up without repeating info | Public survey page only | No console; valid scoped expiring survey token only | MUST have opt-out; MUST NOT be shown or asked for medical data publicly | Survey completed in under 2 minutes; no medical data exposed | No (external) |
| Dokter, Perawat, Kasir, Admin Klinik (§4.9) | Stakeholder | Provide context or generate completed-service event | None required | None required in first pilot | Pilot MUST NOT slow clinic service or duplicate medical documentation | Service event produced without console burden | No |
| DaengtisiaMS integration (§4.10) | System | Send minimum eligible `VisitCompleted` events and receive durable acknowledgement | Machine interface (authenticated API/webhook) | Tenant/branch scoped events | Server-side auth required | Events MUST NOT carry diagnosis, clinical note, odontogram, prescription, or medical document | Events delivered exactly-once, prohibited fields absent | No (system persona) |

## 3. Minimum role coverage (Persona §5)

The pilot MAY run with five named people if compatible roles are combined. A combination MUST NOT remove
meaningful approval on high-risk actions (Persona §5).

| Coverage | Minimum | May be combined with |
|---|---:|---|
| Business Owner / Executive Sponsor | 1 | Reputation Approver |
| Pilot Coordinator / Corporate Admin | 1 | Integration Admin |
| Branch Manager | 1 | Recovery Approver |
| Recovery Assignee | 1–2 | Customer Service |
| Reputation Approver | 1 | Business Owner |
| Read-only Analyst / Auditor | 0–1 | Independent reviewer |

### 3.1 Separation-of-duty constraints

- The person acting as Recovery Assignee MUST NOT also be the sole approver of their own high-risk
  compensation request (Persona §11 compensation authority; §5).
- Combining Business Owner with Reputation Approver is permitted, but public-reply approval MUST still be
  a recorded, deliberate act — never a hidden auto-publish (Persona §12; `.claude/rules/06`).
- Excessive permission grants are prohibited; least privilege applies (Persona §4.3; `.claude/rules/04`).

## 4. Traceability

Persona goals and constraints map to the P0 use cases in `PILOT_USE_CASE_CATALOG.md`, the RACI in
`PILOT_RACI.md`, and the readiness items in `PILOT_READINESS_CHECKLIST.md`. Data-scope enforcement is
detailed in `PILOT_DATA_BOUNDARY.md`.
