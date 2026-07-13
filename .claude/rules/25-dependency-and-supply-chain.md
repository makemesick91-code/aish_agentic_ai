---
id: "25"
title: Dependency Baseline and Supply-Chain Governance
domain: security
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §68; §34 (core stack)"
  - "PRD v1.3.0 (dependency governance)"
  - "ADR 0038 (refines 0031); AFR-095..098"
supersede: "Only via a versioned Master Source update; official-source and no-unapproved-install rules are permanent."
---

# Rule 25 — Dependency Baseline and Supply-Chain Governance

## Purpose
Keep dependencies minimal, verified against official sources, pinned, and free of supply-chain risk.

## Scope
Dependency baseline, approval, version pinning, supply-chain controls, and upgrades.

## Rules
- Dependencies **MUST** be researched against official sources; version-sensitive facts **MUST NOT** rely on memory
  alone (AFR-095). The canonical baseline is Laravel 12 / PHP 8.4 (min 8.3) / PostgreSQL 17 / Redis 7.x per ADR 0038.
- No package **MUST** be installed and no lock file generated during planning steps; a framework-major change (e.g.
  Laravel 13) requires an ADR + Master Source update (AFR-096).
- Supply chain **MUST** enforce official registry only, package-name/typosquat verification, lock-file review,
  dependency review, vulnerability scanning, and an SBOM (AFR-097).
- Dependency approval **MUST** use the controlled vocabulary — `APPROVED FOR IMPLEMENTATION` / `APPROVED WITH
  CONDITIONS` / `EVALUATE DURING IMPLEMENTATION` / `REJECTED`; upgrades follow the pinning + emergency-patch policy
  and abandoned-package handling (AFR-098).

## Required checks
- `scripts/docs/check-step4-coverage.sh` verifies the baseline, the no-install/no-lock statement, the approval
  vocabulary, and supply-chain controls (official registry, typosquat, SBOM, pinning, emergency patch).

## Evidence
- `docs/dependencies/*`; `docs/evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`.

## Related canonical sections
- Master Source v2.4.0 §68, §34; PRD v1.3.0; ADRs 0038, 0031, 0009, 0018; rules 04, 08, 15.

## Supersession
Official-source verification and the no-unapproved-install rule are permanent; the version baseline may advance via
a recorded decision (framework-major changes require an ADR).
