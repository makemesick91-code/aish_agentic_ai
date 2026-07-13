# Dependency Baseline — Aish Agentic AI (Step 4)

**Title:** Step 4 Dependency Baseline
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. DOCUMENTATION BASELINE COMPLETE. APPLICATION IMPLEMENTATION NOT STARTED.
**Rule refs:** `.claude/rules/25` (dependencies & supply chain), `.claude/rules/04` (secrets), `.claude/rules/08`/`.claude/rules/20` (architecture).
**Canonical:** Master Source v2.4.0 §68 (dependency governance), §34 (core stack); PRD v1.3.0.
**AFR refs:** AFR-095, AFR-096, AFR-097, AFR-098.
**Point-in-time research date:** 2026-07-13 (Asia/Makassar). **Evidence:** [`../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`](../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md).

## Non-claims (truthful state)

- **Nothing is installed.** No `composer install`, `npm install`, or equivalent has been run; **no lock file exists; nothing is installed**.
- No infrastructure is provisioned, no service is deployed, no runtime is verified.
- All version numbers below are **point-in-time research** from official sources on 2026-07-13; they are not an installation record and may drift.
- This document fixes the **planned** dependency baseline only. Selection is finalized at implementation through the approval workflow in the sibling documents.

## Purpose

This baseline records the planned technology dependency set for the Aish Agentic AI modular monolith so that implementation does not reopen settled stack decisions and every dependency enters through a governed approval path. It is the entry point for four sibling documents:

- [Dependency Approval Matrix](DEPENDENCY_APPROVAL_MATRIX.md) — per-dependency approval record.
- [Supply-Chain Governance](SUPPLY_CHAIN_GOVERNANCE.md) — registry, verification, SBOM, scanning.
- [Upgrade & Security-Patch Policy](UPGRADE_AND_SECURITY_PATCH_POLICY.md) — cadence, pinning, emergency patches.

## Core runtime baseline (planned)

| Component | Baseline (planned) | Minimum | Canonical basis | Newer-major status |
|-----------|--------------------|---------|-----------------|--------------------|
| Framework | **Laravel 12** | Laravel 12 | Master Source §34 | Laravel 13 (2026-03-17) exists → EVALUATE DURING IMPLEMENTATION (needs ADR + Master Source update) |
| Language | **PHP 8.4** | PHP 8.3 | Canonical "PHP 8.3+" | PHP 8.5 → EVALUATE DURING IMPLEMENTATION |
| Database | **PostgreSQL 17** | PostgreSQL 17 | Master Source §34, §68.4; ADR 0038 | PostgreSQL 18 → EVALUATE DURING IMPLEMENTATION (no separate lower floor approved) |
| Cache/Queue | **Redis 7.x** | Redis 7.x | Master Source §34 | Valkey (OSI-licensed drop-in) → EVALUATE (license posture) |
| Web server | **Nginx stable** | Nginx stable | Master Source §34 | Track stable line |
| Build tooling | **Node.js 24 LTS** | Node.js 24 LTS | Build only (not runtime) | Node.js 26 LTS (Oct 2026) → EVALUATE |
| CSS | **Tailwind CSS 4** | Tailwind CSS 4 | Frontend baseline | Verify Blade/Vite integration at implementation |
| JS interactivity | **Alpine.js — EVALUATE DURING IMPLEMENTATION** | — | Frontend baseline | Version NOT VERIFIED this pass |

**Laravel 12** is the fixed framework baseline. Adopting **Laravel 13** is an architecture change and is out of scope for this baseline; it must go through an ADR plus a Master Source update before it can move from EVALUATE DURING IMPLEMENTATION to an approved baseline.

## First-party and framework-adjacent packages (planned)

| Package | Purpose | Baseline status | Pinning note |
|---------|---------|-----------------|--------------|
| Laravel Fortify | Authentication scaffolding (canonical §34) | APPROVED WITH CONDITIONS | Pin to Laravel 12 line |
| Laravel Sanctum | API / SPA token auth (canonical §34) | APPROVED WITH CONDITIONS | Pin to Laravel 12 line |
| Spatie laravel-permission | RBAC + branch scoping (canonical §34) | APPROVED WITH CONDITIONS | Verify Laravel 12 compatibility |
| Laravel Horizon (or equivalent) | Queue monitoring / worker supervision | EVALUATE DURING IMPLEMENTATION | Redis-backed; confirm at implementation |
| AWS SDK for PHP + Flysystem | S3-compatible object storage client | EVALUATE DURING IMPLEMENTATION | Pin adapter to storage class |
| PHPUnit / Pest | Test framework | EVALUATE DURING IMPLEMENTATION | One primary framework selected at implementation |
| PHPStan / Larastan | Static analysis | EVALUATE DURING IMPLEMENTATION | Ruleset level fixed at implementation |
| Laravel Pint | Code style | EVALUATE DURING IMPLEMENTATION | Style ruleset pinned |
| `composer audit` | Dependency vulnerability audit | APPROVED FOR IMPLEMENTATION (tooling) | Native Composer command |
| OpenTelemetry-compatible instrumentation | Tracing/metrics export | EVALUATE DURING IMPLEMENTATION | Vendor-neutral; see observability plan |
| Sentry-compatible error tracking | Error/exception capture | EVALUATE DURING IMPLEMENTATION | Confirm data-residency at implementation |
| Feature-flag library | Controlled rollout / kill switch support | EVALUATE DURING IMPLEMENTATION | Must support tenant scoping |
| Backup tooling | DB + object backup/restore | EVALUATE DURING IMPLEMENTATION | Tested restore required before pilot |

The authoritative per-dependency record (license, source, version range, security posture, approval status, implementation phase) lives in the [Dependency Approval Matrix](DEPENDENCY_APPROVAL_MATRIX.md).

## Baseline principles

1. **Canonical-first.** Where Master Source §34 fixes a component (Laravel 12, PostgreSQL, Redis, Nginx, Fortify/Sanctum, Spatie permission), the baseline follows it; a newer major is recorded as EVALUATE DURING IMPLEMENTATION, never silently adopted.
2. **Least dependencies.** A package is added only when it earns its place against a first-party or standard-library alternative; redundant packages are rejected.
3. **Governed entry.** Every dependency passes the approval matrix and supply-chain governance before it may be referenced by application code.
4. **Pinning.** Direct dependencies are pinned; transitive dependencies are locked via the (future) lock file and reviewed. See the upgrade policy for the pin/lock rules.
5. **License hygiene.** Only OSI-approved or clearly permissive licenses are approved for implementation; source-available / restrictive licenses (e.g. Redis RSALv2/SSPL) are flagged with a replacement option (Valkey) to evaluate.
6. **Security posture.** Actively maintained, non-abandoned packages only; abandoned-package handling is defined in supply-chain governance.
7. **Truthful status.** No dependency is reported as installed, tested, or deployed until evidence exists.

## Version drift and re-verification

- The point-in-time research MUST be re-verified at the start of implementation and before any release gate; drift is expected across the interval since 2026-07-13.
- Re-verification updates the evidence file [`../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`](../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md) and, if a baseline changes, the approval matrix and (for material changes) the Master Source.
- End-of-life dates for PHP, PostgreSQL, and Laravel are tracked in the upgrade policy; a baseline component approaching EOL is escalated per that policy.

## Dependency tiers

| Tier | Definition | Change discipline |
|------|------------|-------------------|
| Tier 0 — Runtime | Laravel, PHP, PostgreSQL, Redis, Nginx | Canonical §34; majors need ADR + Master Source update |
| Tier 1 — First-party | Fortify, Sanctum, Spatie permission, Pint, Horizon, Pennant | Pin to framework line; standard governed upgrade |
| Tier 2 — Standard libraries | AWS SDK/Flysystem, PHPUnit/Pest, PHPStan/Larastan, OTel SDK | Dependency review + compatibility test |
| Tier 3 — Build/frontend | Node.js, Vite, Tailwind, Alpine.js | Build-time only; not a runtime service |

- A dependency's tier sets how strict its change discipline is; Tier 0 changes are the most tightly governed.
- The Redis license flag applies at Tier 0: the Valkey OSI-licensed drop-in is the recorded replacement option to evaluate.

## Ownership and responsibilities

| Responsibility | Owner (role) |
|----------------|--------------|
| Propose a new dependency | Requesting engineer |
| Approve / set status | Supply-Chain & Operations Architect |
| License/security posture review | Security-Privacy reviewer |
| Framework-major decision | Product owner (via ADR + Master Source update) |
| Lock-file + SBOM at release | Release governance |

## Relationship to Step 3 and Step 4

- This baseline extends the Step 3 architecture contract (modular monolith, canonical stack) into a concrete, governed dependency plan.
- It is a Step 4 planning artifact alongside the operations plans (deployment target, backup/restore, observability, rollback) under `docs/operations/`.
- Both remain **PLANNING BASELINE — NOT IMPLEMENTED** until application implementation begins and produces evidence.

## Status

Dependency baseline documented for **Laravel 12** and the planned stack. No lock file exists; nothing is installed. Selection is finalized at implementation via the approval matrix and supply-chain governance. **PLANNING BASELINE — NOT IMPLEMENTED.**
