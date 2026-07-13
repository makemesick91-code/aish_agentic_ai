# SaaS Foundation Risk Register (Step 4)

- **Status:** PLANNING BASELINE — NOT IMPLEMENTED · Application implementation: NOT STARTED
- **Rule ref:** `.claude/rules/26`
- **Canonical:** Master Source v2.4.0 §68 / §62; PRD v1.3.0 §22 / §23
- **AFR refs:** AFR-099..102, building on AFR-001..072
- **Non-claims:** No Laravel application, migration, or code has been created. Each risk below is a planning
  risk for a future implementation; none reflects an executed outcome. Architecture (ADRs 0009–0032, the
  Application Architecture Baseline, the Application Foundation Rules) is cited by name/number in prose only.

---

## Purpose

This register tracks the SaaS Foundation implementation risks with likelihood, impact, mitigation, and owner.
It is a planning artifact; it lists risk hypotheses to manage, not incidents that have occurred. Risks map to
epics in [SAAS_FOUNDATION_EPIC_CATALOG.md](SAAS_FOUNDATION_EPIC_CATALOG.md) and inform GO/WATCH/NO-GO decisions
in [SAAS_FOUNDATION_SPRINT_ROADMAP.md](SAAS_FOUNDATION_SPRINT_ROADMAP.md).

Likelihood and impact are Low / Medium / High planning estimates.

---

## Risk table

| ID | Risk | Likelihood | Impact | Mitigation | Owner |
|----|------|------------|--------|------------|-------|
| RISK-SF-01 | Cross-tenant data leakage from an unscoped query path | Medium | High | Enforce tenant context primitive (EPIC-SF-05) before any business table; isolation tests on every surface; fail-safe on missing scope | Architect + Security-Privacy |
| RISK-SF-02 | Privilege escalation via missing/weak authorization | Medium | High | RBAC + branch scoping (EPIC-SF-06) before privileged surfaces; escalation/IDOR tests; default-deny | Security-Privacy |
| RISK-SF-03 | Secret committed to the repository | Medium | High | Placeholder-only env templates; CI secret scanning + push protection; secret matrix; no `.env` read | Release/CI + Security-Privacy |
| RISK-SF-04 | Audit gap allowing an unaudited sensitive mutation | Medium | High | Audit trail (EPIC-SF-07) before sensitive-mutation epics; audit-coverage tests; append-only immutability | Security-Privacy |
| RISK-SF-05 | Queue job loses tenant context and processes cross-tenant | Medium | High | Mandatory context propagation into jobs/events (EPIC-SF-08); queue tenant-context tests | Architect |
| RISK-SF-06 | Duplicate external side effect from retry | Medium | High | Idempotency + outbox + dead-letter (ADR 0016, 0017); idempotent-retry tests; no success before verification | Architect |
| RISK-SF-07 | Cross-tenant object access in storage | Medium | High | Tenant/branch path prefixes + access control (EPIC-SF-09); storage isolation + signed-URL tests | Security-Privacy |
| RISK-SF-08 | Unsafe file upload (traversal, malicious content) | Medium | High | Upload validation; content-type/size checks; malicious-upload tests | Security-Privacy |
| RISK-SF-09 | False "deployed"/"verified" status without runtime evidence | Low | High | Evidence-based completion; deploy/rollback proven on non-production first (EPIC-SF-15); truthful-status vocabulary | Release/CI |
| RISK-SF-10 | Backup exists but restore is unverified | Medium | High | Tested restore is a hard pilot precondition (EPIC-SF-14); restore-verification evidence | Operations |
| RISK-SF-11 | Basic workflow inadvertently depends on AI availability | Low | High | Manual-without-AI constraint enforced; AI-unavailable fallback tests (EPIC-SF-10) | Architect + AI-Governance |
| RISK-SF-12 | MED/sensitive data reaches AI or a public surface | Low | High | AI boundary asserted at foundation; prohibited-data path tests; redaction; human approval downstream | Security-Privacy + AI-Governance |
| RISK-SF-13 | Scope creep pulls business features into the foundation | Medium | Medium | Explicit out-of-scope per epic/sprint; Master Source update required for any scope change (Rule 02, Rule 12) | Product + Architect |
| RISK-SF-14 | Sequence reordering breaks a dependency (e.g. features before tenancy) | Low | High | Fixed dependency map; reorder requires Master Source update | Architect |
| RISK-SF-15 | Foundation collapsed into one giant sprint, losing gates | Low | Medium | Nine-sprint roadmap mandated; no sprint collapse; per-sprint GO/WATCH/NO-GO | TPM |
| RISK-SF-16 | CI flakiness masks real failures or is weakened | Medium | Medium | Required checks not removable; failing-case proof; no gate weakening (Rule 09, Rule 13) | Release/CI |
| RISK-SF-17 | Traceability critical orphan (unmapped requirement) | Medium | Medium | Traceability audit in EPIC-SF-16; no critical orphan gate (AFR-069) | QA-Traceability |
| RISK-SF-18 | OAuth state/token handling weakness (leakage, no rotation) | Medium | High | OAuth state validation; token encryption; rotation support (EPIC-SF-04); OAuth-leakage tests | Security-Privacy |
| RISK-SF-19 | Rate-limit bypass on auth or API surfaces | Medium | Medium | Rate limiting on auth/API; rate-limit-bypass tests | Security-Privacy |
| RISK-SF-20 | Estimate/schedule optimism (planning classes treated as commitments) | Medium | Medium | Estimates are planning classes only; re-baseline per sprint; no committed dates claimed | TPM |
| RISK-SF-21 | Wrong-repository work (origin mismatch) | Low | High | Verify origin `makemesick91-code/aish_agentic_ai` before any git write; NO-GO on mismatch (Rule 00, Rule 13) | Release/CI |
| RISK-SF-22 | Secret/PII appears in logs or evidence | Low | High | Log redaction (EPIC-SF-13); evidence tenant-safe with no real PII; secret scan | Security-Privacy + Operations |

---

## Risk governance

- Each risk is reviewed at every sprint boundary; a materialized high-impact risk that breaches a hard gate is
  a **NO-GO** until fixed and retested.
- New risks discovered during a sprint are added here with likelihood/impact/mitigation/owner before the sprint
  closes.
- Superseded risks are marked superseded, never deleted.

Application implementation: NOT STARTED. This risk register is a PLANNING BASELINE — NOT IMPLEMENTED.
