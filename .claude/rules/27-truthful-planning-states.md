---
id: "27"
title: Truthful Planning States
domain: governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §53 (truthful states); §68"
  - "PRD v1.3.0; CLAUDE.md §5 status vocabulary"
  - "AFR-103, AFR-104; ADRs 0033–0041"
supersede: "Permanent; truthful-status and evidence-based-completion constraints cannot be weakened."
---

# Rule 27 — Truthful Planning States

## Purpose
Prevent planning artifacts from being misread as implementation, ownership, or deployment.

## Scope
All Step 4 planning artifacts, status language, and the Step 4 release/GO-tag semantics.

## Rules
- Planning **MUST NOT** be reported as implementation. Specifically (AFR-103):
  - a **domain candidate** is **not** ownership (availability ≠ registered ≠ owned);
  - a **brand baseline** is **not** final creative approval;
  - a **deployment plan** is **not** deployed infrastructure;
  - a **dependency approval** is **not** an installed dependency;
  - a **sprint/epic plan** is **not** executed work.
- Planned artifacts **MUST** carry a truthful label such as `PLANNING BASELINE — NOT IMPLEMENTED`,
  `PLANNING TOKENS — NOT IMPLEMENTED IN UI`, or `PLANNED TOPOLOGY — NOT DEPLOYED`.
- Application implementation, deployment, pilot readiness, pilot runtime, and production readiness **MUST** be
  stated as `NOT STARTED` until evidenced.
- The Step 4 GO tag **MUST** attest planning/documentation readiness only; it **MUST NOT** be read as application
  implementation, deployment, pilot, or production readiness (AFR-104).
- Only the approved status vocabulary (CLAUDE.md §5) **MUST** be used, and only with evidence.

## Required checks
- `scripts/docs/check-step4-coverage.sh` asserts `NOT STARTED` in key docs, the presence of planning labels, and the
  absence of affirmative false ownership/implementation/deployment claims.

## Evidence
- `docs/quality/STEP_4_GO_WATCH_NO_GO.md`; all `docs/domain|brand|environments|dependencies|planning` docs.

## Related canonical sections
- Master Source v2.4.0 §53, §68; PRD v1.3.0; AFR-103, AFR-104; rules 10, 13, 19.

## Supersession
Permanent. Truthful-status and evidence-based-completion constraints cannot be weakened; superseded only by a
higher-version Master Source update that preserves these guarantees.
