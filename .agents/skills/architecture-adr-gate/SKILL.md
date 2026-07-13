---
name: architecture-adr-gate
description: Validate ADRs (sequence, required sections, valid status, no TBD on fundamentals) and their traceability to AFRs, rules, and fitness functions. Read/validate only.
---

# Skill: architecture-adr-gate

**Trigger:** Adding/editing an ADR; before an architecture PR or GO.
**Non-trigger:** Non-architecture documentation edits.
**Inputs:** `docs/decisions/adr/*`, traceability matrix, AFR catalog.

## Workflow
1. Run `scripts/docs/check-adr.sh` (sequence 0001..N, required sections, status, no TBD on Step 3 fundamentals).
2. Confirm each Step 3 ADR maps to ≥1 AFR and ≥1 fitness function in the traceability matrix.
3. Confirm no orphan permanent decision (`check-step3-coverage.sh`).

## Safety boundaries
Read/validate only. MUST NOT merge, tag, or mutate. Does not weaken gates.

## Required output
Pass/fail per check with the offending ADR path on failure.

## Evidence
`docs/evidence/step-3/validation/`, `docs/quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md`.

## Failure behavior
On any failure, report the ADR and missing element; do not proceed to PR/GO.
