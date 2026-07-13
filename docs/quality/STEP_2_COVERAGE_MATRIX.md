# Step 2 Coverage Matrix — Persona and Pilot Use Cases

**Document:** Step 2 Coverage Matrix
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona and Pilot Use Cases v1.0.0
**Timezone:** Asia/Makassar

Proves every Step 2 decision is traceable: **Canonical decision → Rule file → Derived document → Validation /
evidence → Status.** Verified by `scripts/docs/check-step2-coverage.sh`. Rules `.claude/rules/16`–`19`.

| # | Step 2 decision | Canonical ref | Rule file | Derived document(s) | Validation / evidence | Status |
|---|-----------------|---------------|-----------|---------------------|------------------------|--------|
| S01 | Pilot tenant Klinik Gigi Daengtisia | Persona §2; MS §13 | `.claude/rules/16-pilot-persona-and-scope.md` | `product/PERSONA_AND_PILOT_USE_CASES.md`, `product/PILOT_SCOPE_AND_BOUNDARIES.md` | `check-step2-coverage.sh` | COVERED |
| S02 | Recommended branch Daengtisia Pusat (recommendation, verify readiness) | Persona §2.2 | `.claude/rules/16-pilot-persona-and-scope.md` | `product/PILOT_SCOPE_AND_BOUNDARIES.md`, `product/PILOT_READINESS_CHECKLIST.md` | `check-step2-coverage.sh`; `query-smoke.sh` | COVERED |
| S03 | Primary + supporting + external + system personas | Persona §4, §5; PRD §7 | `.claude/rules/16-pilot-persona-and-scope.md` | `product/PILOT_PERSONA_MATRIX.md`, `product/PILOT_RACI.md` | `check-step2-coverage.sh` | COVERED |
| S04 | Minimum role coverage + safe role combination | Persona §5 | `.claude/rules/16-pilot-persona-and-scope.md` | `product/PILOT_PERSONA_MATRIX.md` | `check-step2-coverage.sh` | COVERED |
| S05 | Branch scope for branch-scoped roles | Persona §4.4; MS §17 | `.claude/rules/16-pilot-persona-and-scope.md`, `03` | `product/PILOT_PERSONA_MATRIX.md`, `security/TENANT_ISOLATION.md` | Isolation tests (future) | COVERED |
| S06 | Invitation baseline (trigger, delay, window, cap, reminder, expiry) | Persona §7; PRD §10.5 | `.claude/rules/17-pilot-invitation-survey-and-fallback.md` | `integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`, `integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md` | `check-step2-coverage.sh`; `query-smoke.sh` | COVERED |
| S07 | Survey baseline (CSAT/CES/NPS, comment, conditional, consent) | Persona §6; PRD §10.6 | `.claude/rules/17-pilot-invitation-survey-and-fallback.md` | `product/PILOT_USE_CASE_CATALOG.md`, `product/PILOT_CUSTOMER_JOURNEYS.md` | `check-step2-coverage.sh` | COVERED |
| S08 | VisitCompleted event contract + honest fallbacks | Persona §7.3; MS §35 | `.claude/rules/17-pilot-invitation-survey-and-fallback.md`, `08` | `integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md` | `check-links.sh`; `check-step2-coverage.sh` | COVERED |
| S09 | Healthcare data boundary (prohibited fields) | Persona §8; MS §43 | `.claude/rules/18-pilot-privacy-approval-and-review-safety.md`, `04` | `security/PILOT_DATA_BOUNDARY.md`, `security/PILOT_PRIVACY_RULES.md` | `check-step2-coverage.sh`; `secret-scan.sh` | COVERED |
| S10 | Human approval for public/high-risk actions | Persona §12; MS §33; PRD §13 | `.claude/rules/18-pilot-privacy-approval-and-review-safety.md`, `05` | `ai/PILOT_AI_HUMAN_APPROVAL_RULES.md`, `ai/HUMAN_APPROVAL_MATRIX.md` | `check-step2-coverage.sh`; `query-smoke.sh` | COVERED |
| S11 | No review gating; equal Google Review access | Persona §12; MS §16.2 | `.claude/rules/18-pilot-privacy-approval-and-review-safety.md`, `06` | `security/PILOT_PUBLIC_REPLY_SAFETY.md`, `integrations/google/GOOGLE_REVIEW_POLICY.md` | Contradiction check; `check-step2-coverage.sh` | COVERED |
| S12 | Truthful external states (no success before verification) | Persona §12; MS §53 | `.claude/rules/18-pilot-privacy-approval-and-review-safety.md`, `10` | `product/PILOT_WORKFLOW_STATES.md`, `integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md` | `check-step2-coverage.sh` | COVERED |
| S13 | Manual fallback when AI/provider unavailable | Persona §9.1 (UC-P0-16) | `.claude/rules/17-pilot-invitation-survey-and-fallback.md` | `ai/PILOT_MANUAL_FALLBACK.md` | `check-step2-coverage.sh`; `query-smoke.sh` | COVERED |
| S14 | Severity / SLA / escalation / contact policy | Persona §10 | `.claude/rules/17`, `18` | `product/PILOT_USE_CASE_CATALOG.md`, `product/PILOT_WORKFLOW_STATES.md` | `check-step2-coverage.sh` | COVERED |
| S15 | Compensation authority (AI suggest-only; approval required) | Persona §11 | `.claude/rules/18-pilot-privacy-approval-and-review-safety.md`, `05` | `product/PILOT_RACI.md`, `ai/PILOT_AI_HUMAN_APPROVAL_RULES.md` | `check-step2-coverage.sh` | COVERED |
| S16 | P0 use cases UC-P0-01..16 fully specified | Persona §9.1 | `.claude/rules/16`, `17`, `18` | `product/PILOT_USE_CASE_CATALOG.md`, `testing/PILOT_ACCEPTANCE_TEST_CATALOG.md` | `check-step2-coverage.sh` | COVERED |
| S17 | Hard safety/correctness gates | Persona §14.1 | `.claude/rules/19-pilot-metrics-evidence-and-go-no-go.md` | `product/PILOT_SUCCESS_METRICS.md`, `product/PILOT_GO_WATCH_NO_GO.md` | `check-step2-coverage.sh` | COVERED |
| S18 | Operational targets are hypotheses, not results | Persona §14.2 | `.claude/rules/19-pilot-metrics-evidence-and-go-no-go.md` | `product/PILOT_SUCCESS_METRICS.md` | `check-step2-coverage.sh` | COVERED |
| S19 | Pilot GO / WATCH / NO-GO criteria | Persona §16 | `.claude/rules/19-pilot-metrics-evidence-and-go-no-go.md` | `product/PILOT_GO_WATCH_NO_GO.md`, `release/STEP_2_PERSONA_PILOT_GO_NO_GO.md` | `check-step2-coverage.sh` | COVERED |
| S20 | Evidence requirements (tenant-safe, no real PII) | Persona §15 | `.claude/rules/19`, `07` | `testing/PILOT_UAT_PLAN.md`, `testing/PILOT_ACCEPTANCE_TEST_CATALOG.md` | `secret-scan.sh`; `check-step2-coverage.sh` | COVERED |
| S21 | Threat & abuse cases + mitigations | Persona §17; MS §44 | `.claude/rules/18`, `04` | `security/PILOT_THREAT_AND_ABUSE_CASES.md` | `check-links.sh` | COVERED |
| S22 | Out-of-scope for first pilot | Persona §18; MS §48 | `.claude/rules/16`, `02` | `product/PILOT_SCOPE_AND_BOUNDARIES.md` | `check-step2-coverage.sh` | COVERED |
| S23 | AI evaluation plan (datasets, thresholds, no leakage) | Persona §14; MS §50 | `.claude/rules/19`, `09` | `ai/PILOT_AI_EVALUATION_PLAN.md`, `ai/AI_EVALUATION_BASELINE.md` | `check-step2-coverage.sh` | COVERED |
| S24 | Requirements traceability, no orphan P0 | Persona §9; PRD §23 | `.claude/rules/09`, `19` | `testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md` | `check-step2-coverage.sh` | COVERED |
| S25 | Step 3 boundary; no application-implementation claim | Persona §20; MS §6 | `.claude/rules/19-pilot-metrics-evidence-and-go-no-go.md` | `product/ROADMAP.md`, `product/OPEN_DECISIONS.md` | `query-smoke.sh` | COVERED |

## Rule → derived document mapping (Step 2)

- `16` → persona matrix, scope & boundaries, RACI.
- `17` → invitation baseline, event contract, survey/use-case catalog, manual fallback.
- `18` → data boundary, privacy rules, public-reply safety, human approval, workflow states.
- `19` → success metrics, GO/WATCH/NO-GO, readiness checklist, UAT plan, Step 2 release GO/NO-GO.

**Coverage: 25/25 Step 2 decisions COVERED across rules 16–19 and derived documents. No critical gap.**
Application implementation, deployment, pilot readiness, and pilot runtime remain **NOT STARTED**.
