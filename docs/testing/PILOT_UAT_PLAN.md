# Pilot User Acceptance Test (UAT) Plan

**Document:** Pilot UAT Plan
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Nature of this document

This is a **PLANNED** UAT plan. It defines how the pilot will be user-accepted **after** the
application is implemented and readiness is met. No UAT has run; runtime is **NOT STARTED / NOT
READY** (Persona §19). This plan does not authorize execution.

- Canonical Step 2 source: [`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) (§4 personas, §9 use cases, §13 operation phases, §14 metrics, §15 evidence, §16 GO/WATCH/NO-GO).
- Acceptance tests executed within UAT: [`PILOT_ACCEPTANCE_TEST_CATALOG.md`](PILOT_ACCEPTANCE_TEST_CATALOG.md).
- Traceability: [`STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`](STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md).
- Rule authority: [`../../.claude/rules/09-testing-and-quality-gates.md`](../../.claude/rules/09-testing-and-quality-gates.md), [`../../.claude/rules/13-git-ci-release-and-go-tag.md`](../../.claude/rules/13-git-ci-release-and-go-tag.md).
- Canonical map: Master Source §50 (testing), §54 (release gates), §59 (DoD); PRD §24, §30.

---

## 1. Objectives

Confirm, with named pilot roles, that the implemented system safely and consistently performs the
pilot journeys (Persona §3) and passes the hard safety/correctness gates (Persona §14.1) before a
GO/WATCH/NO-GO decision for limited expansion.

---

## 2. Participants (named-role UAT team)

The five primary personas are the UAT participants; one person may hold compatible roles per the
minimum role coverage (Persona §5). Named individuals are assigned at pilot preparation (Persona
§13.1) and recorded in the pilot readiness evidence — not in this repository.

| UAT role | Persona | Responsibility in UAT |
|---|---|---|
| Executive Sponsor | Business Owner (§4.2) | Owner dashboard acceptance; scope/policy/GO decision |
| Pilot Coordinator | Corporate Admin (§4.3) | Configuration, evidence capture, defect logging |
| Branch Manager | Branch Manager (§4.4) | Triage/assignment/SLA acceptance |
| Recovery Assignee | Customer Service (§4.5) | Private recovery contact and closure acceptance |
| Reputation Approver | Reputation Approver (§4.6) | Review draft/approval/publish acceptance |
| Independent reviewer (optional) | Read-only Analyst/Auditor (§4.7) | Reconciliation and audit verification |

Customers/guardians (§4.1) participate only via synthetic/consented test interactions; doctors,
nurses, cashiers, and clinic admin (§4.9) are not required to operate the console.

---

## 3. UAT scenarios mapped to customer journeys

Each scenario runs the relevant acceptance tests from the catalog. Scenarios map to
[`../product/PILOT_CUSTOMER_JOURNEYS.md`](../product/PILOT_CUSTOMER_JOURNEYS.md) and
[`../product/PILOT_WORKFLOW_STATES.md`](../product/PILOT_WORKFLOW_STATES.md), which exist in this Step 2 baseline.

| Scenario | Journey | Acceptance tests |
|---|---|---|
| S1 Onboarding & survey setup | Configure tenant/branch/survey | AT-P0-01, AT-P0-02 |
| S2 Event → invitation → response | Post-visit feedback capture | AT-P0-03, AT-P0-04, AT-P0-05 |
| S3 Analysis → recovery | Negative feedback to recovery closure | AT-P0-06, AT-P0-07, AT-P0-08, AT-P0-09, AT-P0-10 |
| S4 Google review lifecycle | Connect → sync → reply | AT-P0-11, AT-P0-12, AT-P0-13 |
| S5 Insight & evidence | Dashboards, audit, export | AT-P0-14, AT-P0-15 |
| S6 Degraded operation | AI/provider failure | AT-P0-16 |
| S7 Safety gates | Cross-cutting hard gates | AT-GATE-01 … AT-GATE-06 |

If Google access is BLOCKED (Persona §12), S4 runs with the Google scope marked BLOCKED and its
targets excluded; the pilot still proceeds on CSAT/recovery (mock ≠ success).

---

## 4. Entry criteria

UAT may begin only when:

- Application implementation for the pilot P0 scope is complete and internally tested.
- The pilot readiness items (see [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) and [`../product/PILOT_READINESS_CHECKLIST.md`](../product/PILOT_READINESS_CHECKLIST.md)) are verified or explicitly BLOCKED with a recorded decision.
- Named UAT participants and roles are assigned and trained (Persona §13.1).
- A non-production environment with synthetic/pseudonymous test data exists (§6).
- Acceptance-test catalog and traceability matrix are current.

---

## 5. Exit criteria

UAT is complete when:

- All hard-gate tests (AT-GATE-01 … 06) pass — any failure is NO-GO (Persona §16).
- All P0 acceptance tests pass or have an owner-approved remediation plan.
- No unresolved critical incident (Persona §16).
- Evidence (Persona §15) is captured and tenant-safe.
- A GO/WATCH/NO-GO recommendation is produced.

---

## 6. Environment and test-data rules

- UAT runs in a **non-production** environment. Real customer PII MUST NOT be used or stored
  (Persona §15; Rule 04).
- Test data is synthetic or pseudonymous; medical/prohibited fields (Persona §8.2) MUST NOT appear
  in any test payload, prompt, or evidence.
- Secrets/tokens are referenced from a secure store, never committed (Rule 04).
- Multi-tenant isolation is exercised with at least two tenants to validate AT-GATE-01.

---

## 7. Defect severity and triage

| Severity | Definition | Handling |
|---|---|---|
| Critical | Any hard-gate breach: cross-tenant exposure, unauthorized/unapproved publish, PII/medical leak, falsified success, uncontrolled duplicate, critical permission failure | Immediate stop-the-line; NO-GO until fixed and retested |
| High | Core P0 journey broken or SLA/severity handling wrong | Must fix before GO; WATCH at most with verified remediation |
| Medium | Non-blocking functional defect or usability issue | Fix or scheduled remediation; may WATCH |
| Low | Cosmetic / minor | Backlog |

Triage is run by the Pilot Coordinator with the relevant persona owner; every defect is logged with
reproduction, evidence, and severity. Critical defects follow the incident path (Persona §14.1).

---

## 8. Sign-off roles

| Sign-off | Owner | Scope |
|---|---|---|
| Functional acceptance | Branch Manager + Recovery Assignee | S1–S3, S5–S6 |
| Reputation/public-reply acceptance | Reputation Approver | S4 |
| Security/privacy & isolation acceptance | Independent reviewer / Pilot Coordinator | S7 hard gates |
| Final GO/WATCH/NO-GO | Business Owner (Executive Sponsor) | Overall pilot |

No single role may self-approve a high-risk action; meaningful approval separation is preserved
(Persona §5; Rule 05).

---

## 9. Mapping to GO / WATCH / NO-GO (Persona §16)

| Outcome | UAT condition |
|---|---|
| **GO (limited expansion)** | All hard gates pass; no unresolved critical incident; workflow usable by named roles; primary operational targets substantially met or with approved remediation; honest recoverable failure paths; acceptable cost; owner approves next scope |
| **WATCH** | No critical safety breach, but adoption/response/cost/AI-quality/integration targets need improvement; expansion limited until corrective action verified |
| **NO-GO** | Any of: cross-tenant exposure, unauthorized publishing, PII/medical leakage, falsified success state, uncontrolled duplicate action, critical permission failure, unresolved critical incident, or missing evidence for a release-critical claim |

The GO/WATCH/NO-GO decision and its evidence are recorded outside this repository as pilot evidence
(Persona §15); this plan does not itself assert any outcome.

**Status:** UAT plan documented (PLANNED). Application implementation NOT STARTED; pilot runtime NOT
STARTED / NOT READY. No UAT has been executed.
