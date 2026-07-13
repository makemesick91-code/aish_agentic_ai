# ADR 0038 — Dependency Baseline and Version-Pinning Policy

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no package installed, no lock file**
- **Owner:** Supply-Chain Architect
- **Rule:** `.claude/rules/25` (AFR-095..098) · **Canonical:** Master Source v2.4.0 §68; §34; PRD v1.3.0
- **Refines:** ADR 0031 (dependency & supply-chain governance) with a Step 4 baseline.

## Context
Implementation needs an approved, researched dependency baseline (versions verified against official sources,
not memory) without installing anything or generating a lock file.

## Decision
Baseline (point-in-time research 2026-07-13): **Laravel 12**, **PHP 8.4** (min 8.3), **PostgreSQL 17**,
**Redis 7.x** (Valkey noted as an OSI-licensed option), Nginx stable, **Node.js 24 LTS** (build tooling),
**Tailwind CSS 4**, Fortify/Sanctum and Spatie Permission approved-with-conditions on the Laravel 12 line.
Newer majors (Laravel 13, PostgreSQL 18, PHP 8.5) are **EVALUATE DURING IMPLEMENTATION**; adopting a framework
major requires an ADR + Master Source update. Approval vocabulary: APPROVED FOR IMPLEMENTATION / APPROVED WITH
CONDITIONS / EVALUATE DURING IMPLEMENTATION / REJECTED. See
[Dependency Baseline](../../dependencies/DEPENDENCY_BASELINE.md) and
[Dependency Approval Matrix](../../dependencies/DEPENDENCY_APPROVAL_MATRIX.md).

## Alternatives
- **Adopt Laravel 13 now** — rejected for the baseline: canonical §34 is Laravel 12; adopting 13 is an architecture change needing an ADR.
- **Pin to latest of everything** — rejected: stability and compatibility risk; baseline favors proven majors.
- **No pinning policy** — rejected: supply-chain risk.

## Consequences
A stable, governance-consistent baseline; upgrades and framework-major changes follow explicit policy.

## Impacts
- **Security:** official-source verification, vulnerability scanning, and SBOM reduce supply-chain risk.
- **Privacy:** dependencies handling PII (auth, storage) are pinned and reviewed.
- **Tenant isolation:** no dependency choice weakens tenancy; permission/auth libs are first-party/approved.
- **Database:** PostgreSQL 17 baseline; driver compatibility verified at implementation.
- **Operational:** pinning + emergency-patch policy defined; abandoned-package handling documented.
- **Cost:** open-source baseline; commercial tooling (error tracking, monitoring) as planning cost categories.

## Verification / fitness function
`check-step4-coverage.sh` asserts the Laravel 12 baseline, the "no package installed / no lock" statement, and
the approval vocabulary (V4-DEP-01/02). No `composer`/`npm` command is run.

## Related
Requirement: Master Source v2.4.0 §68, §34; PRD v1.3.0. Application rules: AFR-095..098. Rules: 25, 04, 08.
ADRs: 0031, 0018 (frontend), 0009 (Laravel modular monolith).

## Evidence
[Dependency version research](../../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md),
`docs/dependencies/*` (baseline, approval matrix, supply-chain, upgrade policy).

## Non-claims
No package is installed; no `composer.lock`/`package-lock.json` exists; nothing is claimed as used. Versions are
point-in-time research, not an installation record.

## Rollback
The baseline is advisory until implementation; a version change before install is a recorded decision, and a
framework-major change requires an ADR + Master Source update.
