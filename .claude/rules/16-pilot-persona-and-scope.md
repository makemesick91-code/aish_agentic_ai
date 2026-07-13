---
id: "16"
title: Pilot Persona, Tenant, and Branch Scope
domain: product
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.2.0 §13 (pilot tenant), §14 (persona), §63"
  - "PRD v1.1.0 §6, §7"
  - "Persona and Pilot Use Cases v1.0.0 §2, §4, §5"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 16 — Pilot Persona, Tenant, and Branch Scope

## Purpose
Fix the first-pilot tenant, the recommended branch, and the persona/branch scope so pilot planning stays
consistent and the core product stays generic.

## Scope
Persona modelling, pilot tenant/branch selection, role coverage, and branch-scoped access for the pilot.

## Rules
- The first pilot tenant **MUST** be **Klinik Gigi Daengtisia**. The recommended first pilot branch **MUST** be
  **Daengtisia Pusat**, recorded as a *recommendation* — **NOT** a claim that staff readiness or Google
  Business Profile access is confirmed. Final branch selection **MUST** pass readiness verification and any
  change **MUST** be recorded in `docs/decisions/DECISION_LOG.md` **without** narrowing product scope.
- The pilot **MUST** start from one branch and one Google location, while the core architecture **MUST**
  remain multi-tenant and multi-branch (see `.claude/rules/03`).
- Minimum role coverage **MUST** include Business Owner / Executive Sponsor, Pilot Coordinator / Corporate
  Admin, Branch Manager, Recovery Assignee / Customer Service, and Reputation Approver. Compatible roles
  **MAY** be combined, but a combination **MUST NOT** remove meaningful approval on high-risk actions.
- Dokter, perawat, kasir, and Admin Klinik **MAY** be stakeholders or event sources but **MUST NOT** be
  required to operate the Aish Agentic AI console in the first pilot; the pilot **MUST NOT** slow clinic
  service or duplicate medical documentation.
- Branch-scoped roles (e.g. Branch Manager, Recovery Assignee) **MUST** see only their branch's data.
- The core product **MUST** stay generic; clinic-specific mapping **MUST** live at the integration /
  configuration boundary, never hard-coded into the domain model.

## Required checks
- `scripts/docs/check-step2-coverage.sh` verifies persona coverage and the Daengtisia Pusat recommendation.
- Step 2 coverage matrix maps persona/scope decisions to derived docs.

## Evidence
- `docs/product/PERSONA_AND_PILOT_USE_CASES.md`, `docs/product/PILOT_PERSONA_MATRIX.md`,
  `docs/product/PILOT_SCOPE_AND_BOUNDARIES.md`, `docs/product/PILOT_RACI.md`.

## Related canonical sections
- Master Source §13, §14, §17, §63; PRD §6, §7; Persona §2, §4, §5.

## Supersession
The pilot tenant and generic-core constraints are permanent; the recommended branch may change only through a
recorded decision after readiness verification. Both require a Master Source update to alter scope.
