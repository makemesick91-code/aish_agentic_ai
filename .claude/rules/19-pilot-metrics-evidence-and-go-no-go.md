---
id: "19"
title: Pilot Metrics, Evidence, and GO/WATCH/NO-GO
domain: release
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.2.0 §6 (changelog/roadmap), §54, §59"
  - "PRD v1.1.0 §19, §24"
  - "Persona and Pilot Use Cases v1.0.0 §14, §15, §16, §19, §20"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 19 — Pilot Metrics, Evidence, and GO/WATCH/NO-GO

## Purpose
Keep pilot claims truthful: metrics are hypotheses until measured, evidence is mandatory, and status words map
to real state. Prevent false "pilot ready" or "implemented" claims.

## Scope
Pilot metrics, evidence requirements, pilot expansion GO/WATCH/NO-GO, and the Step 2 → Step 3 boundary.

## Rules
- Pilot operational targets (invitation ≥90%, delivery ≥85%, response ≥20%, completion ≥80%, negative triage
  ≥95%, critical/high SLA ≥90%, Google disposition ≤48h ≥90%, median reply <24h, structured AI validity ≥99%,
  critical/high recall ≥95%) **MUST** be treated as pilot **hypotheses**, never reported as achieved results
  without measured evidence.
- The hard safety/correctness gates (zero cross-tenant exposure; no unauthorized public reply; no known
  PII/medical leakage in public reply; 100% public reply human-approved; no duplicate external action from
  retry; no external success before provider verification; tenant-scoping on all surfaces; audit for every
  important action; manual works without AI) **MUST** all pass; a breach is **NO-GO** until fixed and retested.
- Pilot expansion GO **MUST** require all hard gates passing, no unresolved critical incident, usability by
  named pilot roles, targets substantially met or an approved remediation plan, honest recoverable
  failure paths, acceptable measured cost, and owner approval of the next scope. **WATCH** applies when no
  critical breach exists but adoption/response/cost/AI-quality/integration targets still need work. **NO-GO**
  applies on cross-tenant exposure, unauthorized publishing, PII/medical leakage, falsified success,
  uncontrolled duplicate action, critical permission failure, unresolved critical incident, or missing
  release-critical evidence.
- Completion **MUST** be evidence-based: a Step or pilot phase **MUST NOT** be called done without the
  required evidence, and evidence **MUST** be tenant-safe with no real customer PII in the repository.
- A documentation GO tag (including the Step 2 tag) attests documentation/tooling readiness **only**; it
  **MUST NOT** be read as application implementation, deployment, pilot readiness, pilot runtime, or
  production readiness — all of which remain **NOT STARTED**. "Pilot ready" **MUST NOT** be claimed without
  runtime evidence.
- Step 3 (Repository Application Architecture and ADR Foundation) **MUST NOT** begin as implementation within
  the Step 2 release; only a roadmap pointer is permitted. Step 3 starts after the Step 2 documentation
  release is merged and GO-tagged.

## Required checks
- `scripts/docs/check-step2-coverage.sh` verifies metrics-as-hypothesis, hard-gate, and GO/WATCH/NO-GO
  coverage plus truthful-status language.

## Evidence
- `docs/product/PILOT_SUCCESS_METRICS.md`, `docs/product/PILOT_GO_WATCH_NO_GO.md`,
  `docs/product/PILOT_READINESS_CHECKLIST.md`, `docs/release/STEP_2_PERSONA_PILOT_GO_NO_GO.md`,
  `docs/testing/PILOT_UAT_PLAN.md`.

## Related canonical sections
- Master Source §54, §59, §66.11 (GO tag scope); PRD §19, §24; Persona §14, §15, §16, §19, §20.

## Supersession
Truthful-status and evidence-based-completion constraints are permanent; superseded only by a higher-version
Master Source update.
