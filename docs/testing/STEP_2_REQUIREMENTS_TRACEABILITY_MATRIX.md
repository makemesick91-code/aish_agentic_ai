# Step 2 Requirements Traceability Matrix

**Document:** Step 2 Requirements Traceability Matrix
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Purpose and scope

This matrix traces every Step 2 requirement/decision from the canonical source
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) to its rule
authority, derived documentation, and planned acceptance test. It complements the
foundation-level [`../quality/REQUIREMENTS_TRACEABILITY_MATRIX.md`](../quality/REQUIREMENTS_TRACEABILITY_MATRIX.md)
and follows [`../../.claude/rules/09-testing-and-quality-gates.md`](../../.claude/rules/09-testing-and-quality-gates.md)
and [`../../.claude/rules/12-documentation-living-source-versioning.md`](../../.claude/rules/12-documentation-living-source-versioning.md).

**Validation status** legend: `Baseline documented` (design agreed), `PLANNED` (test defined, not
run — application NOT STARTED). No row is claimed as implemented or runtime-verified.

Acceptance tests referenced (`AT-*`) live in
[`PILOT_ACCEPTANCE_TEST_CATALOG.md`](PILOT_ACCEPTANCE_TEST_CATALOG.md).

All derived pilot documents named below now exist in this Step 2 baseline (data boundary, manual
fallback, customer journeys, workflow states, readiness checklist, etc.) and are referenced by name.
Where a path is shown it resolves to an existing file; no reference is a broken link.

---

## 1. Personas

| Step 2 requirement (Persona §) | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Customer / Wali external persona (§4.1) | MS §14, PRD §7 | 01,04 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md); [`../product/PERSONAS_BASELINE.md`](../product/PERSONAS_BASELINE.md) | AT-P0-05 | Baseline documented |
| Business Owner / Executive Sponsor (§4.2) | MS §14 | 01 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-14 | Baseline documented |
| Pilot Coordinator / Corporate Admin (§4.3) | MS §14 | 01 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-01, AT-P0-15 | Baseline documented |
| Branch Manager (§4.4) | MS §14, §17 | 01,03 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-08, AT-P0-14 | Baseline documented |
| Recovery Assignee / Customer Service (§4.5) | MS §14 | 01,05 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-09, AT-P0-10 | Baseline documented |
| Reputation Approver (§4.6) | MS §14, §16 | 01,06 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-13, AT-GATE-03 | Baseline documented |
| Read-only Analyst / Auditor (support, §4.7) | MS §14, §37 | 01,07 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-15 | Baseline documented |
| Platform Support / AI Ops (support, §4.8) | MS §14, §51 | 01,11 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-GATE-01, AT-P0-16 | Baseline documented |
| Doctor/nurse/cashier/clinic admin (stakeholder, §4.9) | MS §14 | 01 | [`PILOT_SCOPE_AND_BOUNDARIES.md`](../product/PILOT_SCOPE_AND_BOUNDARIES.md) | AT-P0-16 | Baseline documented |
| DaengtisiaMS integration (system persona, §4.10) | MS §35, §39 | 08,04 | [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md) | AT-P0-03 | Baseline documented |

---

## 2. Pilot scope, branch, and role coverage

| Step 2 requirement (Persona §) | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Daengtisia Pusat recommended pilot branch (§2, §2.2) | MS §17 | 01,02 | [`PILOT_SCOPE_AND_BOUNDARIES.md`](../product/PILOT_SCOPE_AND_BOUNDARIES.md) | AT-P0-01 | Baseline documented |
| Single branch / single Google location start (§2.3) | MS §16, §38 | 06 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-P0-11 | Baseline documented |
| Minimum role coverage (§5) | MS §17 | 01,04 | [`PILOT_PERSONA_MATRIX.md`](../product/PILOT_PERSONA_MATRIX.md) | AT-P0-01 | Baseline documented |
| Core stays multi-tenant/multi-branch (§2.3) | MS §15.1, §17 | 03 | [`../security/TENANT_ISOLATION.md`](../security/TENANT_ISOLATION.md) | AT-GATE-01 | Baseline documented |

---

## 3. Invitation, survey, and data boundary

| Step 2 requirement (Persona §) | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Invitation baseline: trigger, 60-min delay (30–120), 09:00–20:00, cap 1/14d, 1 reminder, 7-day expiry, opt-out (§7.2) | MS §35, §39 | 08 | [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-P0-04 | Baseline documented |
| Eligibility rules; lawful path/QR; guardian for minors (§7.1) | MS §39 | 08,04 | [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-P0-04 | Baseline documented |
| Integration modes; manual/QR shown truthfully (§7.3) | MS §39, §53 | 08,10 | [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md) | AT-P0-03, AT-GATE-05 | Baseline documented |
| Survey baseline: CSAT/CES/NPS/comment/conditional; <2 min; no login; unique token (§6) | MS §11, PRD §10 | 08,10 | [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-P0-02, AT-P0-05 | Baseline documented |
| Minimum data allowed set (§8.1) | MS §36, §37 | 07,04 | [`../security/PRIVACY_AND_PII.md`](../security/PRIVACY_AND_PII.md) | AT-P0-03 | Baseline documented |
| Prohibited fields — diagnosis/clinical/odontogram/prescription/etc. (§8.2) | MS §43, §44 | 04,05 | [`../security/PRIVACY_AND_PII.md`](../security/PRIVACY_AND_PII.md); [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md) | AT-GATE-02 | Baseline documented |

---

## 4. Governance: approval, review policy, fallback, truthful states

| Step 2 requirement (Persona §) | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Human approval for all Google replies (§12, §4.6) | MS §16.4 | 05,06 | [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md); [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-P0-13, AT-GATE-03 | Baseline documented |
| No review gating / equal access (§6.3, §12) | MS §16.1, §16.2 | 06 | [`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md) | AT-GATE-06 | Baseline documented |
| Manual workflow works when AI unavailable (§9.1 UC-P0-16, §13) | MS §15.2 | 05,09,17 | [`../ai/PILOT_MANUAL_FALLBACK.md`](../ai/PILOT_MANUAL_FALLBACK.md) | AT-P0-16 | Baseline documented |
| Truthful external/delivery/publication states (§7.3, §12, §14.1) | MS §53, §15.7 | 10 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md); [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-GATE-05 | Baseline documented |
| Severity / SLA / escalation / contact policy (§10) | MS §33, PRD §13 | 05,07,17 | Persona §10; [`../product/PILOT_USE_CASE_CATALOG.md`](../product/PILOT_USE_CASE_CATALOG.md), [`../product/PILOT_WORKFLOW_STATES.md`](../product/PILOT_WORKFLOW_STATES.md) | AT-P0-07, AT-P0-08 | Baseline documented |
| Compensation authority (§11) | MS §33 | 05 | [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md) | AT-P0-09 | Baseline documented |
| Untrusted review/feedback input; prompt-injection defense (§12) | MS §44 | 04,05 | [`../security/PROMPT_INJECTION_DEFENSE.md`](../security/PROMPT_INJECTION_DEFENSE.md) | AT-P0-06, AT-P0-13 | Baseline documented |

---

## 5. P0 use cases (UC-P0-01 … UC-P0-16)

| Use case (Persona §9.1) | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| UC-P0-01 Onboard tenant/branch | MS §17 | 01,03 | [`PILOT_SCOPE_AND_BOUNDARIES.md`](../product/PILOT_SCOPE_AND_BOUNDARIES.md) | AT-P0-01 | PLANNED |
| UC-P0-02 Create/publish survey | MS §11 | 08 | [`../architecture/EVENT_CATALOG.md`](../architecture/EVENT_CATALOG.md) | AT-P0-02 | PLANNED |
| UC-P0-03 Receive completed-service event | MS §35, §39 | 08,04 | [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md) | AT-P0-03 | PLANNED |
| UC-P0-04 Create/send invitation | MS §35, §39 | 08 | [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-P0-04 | PLANNED |
| UC-P0-05 Customer fills feedback | MS §11 | 08,10 | [`../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`](../integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md) | AT-P0-05 | PLANNED |
| UC-P0-06 Analyze feedback + manual fallback | MS §12, §50 | 05,09 | [`../ai/AGENTIC_ARCHITECTURE.md`](../ai/AGENTIC_ARCHITECTURE.md) | AT-P0-06 | PLANNED |
| UC-P0-07 Auto-create recovery ticket | MS §33, §35 | 05,07 | [`../architecture/EVENT_CATALOG.md`](../architecture/EVENT_CATALOG.md) | AT-P0-07 | PLANNED |
| UC-P0-08 Triage/assign/escalate | MS §33 | 03,07 | [`../architecture/EVENT_CATALOG.md`](../architecture/EVENT_CATALOG.md) | AT-P0-08 | PLANNED |
| UC-P0-09 Contact customer privately | MS §33, §43 | 04,05 | [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md) | AT-P0-09 | PLANNED |
| UC-P0-10 Resolution and closure | MS §33, §59 | 07,09 | [`../architecture/EVENT_CATALOG.md`](../architecture/EVENT_CATALOG.md) | AT-P0-10 | PLANNED |
| UC-P0-11 Connect Google Business Profile | MS §38 | 06,04 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-P0-11 | PLANNED |
| UC-P0-12 Sync Google reviews | MS §38 | 06,08 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-P0-12 | PLANNED |
| UC-P0-13 Draft/approve/publish reply | MS §16, §29 | 05,06 | [`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md) | AT-P0-13 | PLANNED |
| UC-P0-14 Owner/branch dashboard | MS §19, §21 | 03,10 | [`../architecture/DOMAIN_MAP.md`](../architecture/DOMAIN_MAP.md) | AT-P0-14 | PLANNED |
| UC-P0-15 Audit and export evidence | MS §37 | 07 | [`../security/SECURITY_FOUNDATION.md`](../security/SECURITY_FOUNDATION.md) | AT-P0-15 | PLANNED |
| UC-P0-16 Operate when AI/provider fails | MS §15.2, §53 | 05,10,11 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-P0-16 | PLANNED |

---

## 6. Hard safety/correctness gates (Persona §14.1)

| Gate | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Zero cross-tenant data exposure | MS §15.1, §50 | 03 | [`../security/TENANT_ISOLATION.md`](../security/TENANT_ISOLATION.md) | AT-GATE-01 | PLANNED |
| No unauthorized public reply | MS §16.4 | 05,06 | [`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md) | AT-GATE-03 | PLANNED |
| No PII/medical leakage on public reply | MS §43 | 04,06 | [`../security/PRIVACY_AND_PII.md`](../security/PRIVACY_AND_PII.md) | AT-GATE-02 | PLANNED |
| 100% public reply human-approved | MS §16.4 | 05,06 | [`../ai/HUMAN_APPROVAL_MATRIX.md`](../ai/HUMAN_APPROVAL_MATRIX.md) | AT-GATE-03 | PLANNED |
| No duplicate external action from retry | MS §39 | 08 | [`../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`](../integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md) | AT-GATE-04 | PLANNED |
| No external success before provider verification | MS §53 | 10 | [`../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md`](../integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md) | AT-GATE-05 | PLANNED |
| No review gating / equal access | MS §16.2 | 06 | [`../integrations/google/GOOGLE_REVIEW_POLICY.md`](../integrations/google/GOOGLE_REVIEW_POLICY.md) | AT-GATE-06 | PLANNED |
| 100% critical incident has owner/timeline/audit | MS §33, §51 | 07,11 | [`../security/SECURITY_FOUNDATION.md`](../security/SECURITY_FOUNDATION.md) | AT-P0-08, AT-P0-10 | PLANNED |

---

## 7. Operational targets (pilot hypotheses, not results)

The operational targets in Persona §14.2 (invitation success ≥90%, delivery ≥85%, response ≥20%,
completion ≥80%, negative-triage ≥95%, critical/high first-response-in-SLA ≥90%, review disposition
in 48h ≥90%, median reply <24h, structured-output validity ≥99%, critical/high recall ≥95%, weekly
active operators ≥80%, KPI reconciliation 100%) are **hypotheses to be measured**, not guarantees.

| Requirement | Canonical | Rule | Derived doc | Acceptance test | Validation status |
|---|---|---|---|---|---|
| Operational targets treated as hypotheses (§14.2) | MS §19, §50 | 09 | [`../quality/TEST_STRATEGY.md`](../quality/TEST_STRATEGY.md) | AT-P0-14 (reconciliation) | PLANNED (measured in UAT) |

They are measured during pilot execution per [`PILOT_UAT_PLAN.md`](PILOT_UAT_PLAN.md); they do not
gate Step 2 documentation completion and MUST NOT be reported as achieved results.

---

## 8. Orphan check

Every P0 use case (UC-P0-01 … UC-P0-16) maps to a rule, a derived doc, and an acceptance test
(`AT-P0-01` … `AT-P0-16`). Every hard gate maps to a rule, a derived doc, and a gate test
(`AT-GATE-01` … `AT-GATE-06`). All personas, the recommended pilot branch, invitation/survey
baselines, data boundary, human approval, no-review-gating, manual fallback, truthful states,
severity/SLA, and operational-target hypotheses are traced above.

`Orphan critical requirements: none`
