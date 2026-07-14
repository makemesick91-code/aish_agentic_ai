# Dependency Approval Matrix — Aish Agentic AI (Step 4)

**Title:** Step 4 Dependency Approval Matrix
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Approval records are planning decisions, not installation records.
**Rule refs:** `.claude/rules/25` (dependencies & supply chain), `.claude/rules/04` (secrets/license), `.claude/rules/09` (gates).
**Canonical:** Master Source v2.4.0 §68 (dependency governance), §34 (core stack); PRD v1.3.0.
**AFR refs:** AFR-095, AFR-096, AFR-097, AFR-098.
**Point-in-time research date:** 2026-07-13. **Evidence:** [`../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`](../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md).

## Non-claims

- Nothing is installed, provisioned, or deployed. No lock file exists.
- Version ranges and posture are point-in-time research (2026-07-13) from official sources; they are re-verified at implementation.
- "Approval status" is a **planning decision** about eligibility, not a claim that a package is present in a project.

## Approval status vocabulary (literal strings)

| Status | Meaning |
|--------|---------|
| **APPROVED FOR IMPLEMENTATION** | Eligible to be added now, subject to supply-chain governance and pinning. |
| **APPROVED WITH CONDITIONS** | Eligible only when the stated condition (usually version pin / compatibility) holds. |
| **EVALUATE DURING IMPLEMENTATION** | Not yet decided; a concrete selection/version is fixed at implementation. |
| **REJECTED** | Not eligible; a governed decision is required to reconsider. |

Every dependency below carries: purpose · official source · license · supported version range · compatibility · security posture · maintenance status · replacement option · upgrade policy · pinning policy · approval status · implementation phase.

## Core runtime

### Laravel (framework)
- **Purpose:** Application framework (modular monolith). **Official source:** `laravel/framework` (Packagist, official Laravel). **License:** MIT. **Supported version range:** `^12.0` (baseline); 13 exists but out of baseline. **Compatibility:** PHP 8.3+; canonical §34. **Security posture:** active security support for the 12 line. **Maintenance status:** actively maintained (first-party). **Replacement option:** none (canonical framework). **Upgrade policy:** minor/patch within 12 line auto-eligible; major (13) needs ADR + Master Source update. **Pinning policy:** pin to `^12`, lock file authoritative. **Approval status:** APPROVED FOR IMPLEMENTATION (Laravel 12). **Implementation phase:** Phase 1 (SaaS foundation).

### PHP (language runtime)
- **Purpose:** Runtime. **Official source:** php.net. **License:** PHP License. **Supported version range:** 8.4 baseline, min 8.3; 8.5 = evaluate. **Compatibility:** Laravel 12. **Security posture:** 8.3/8.4 in active + security support. **Maintenance status:** active. **Replacement option:** none. **Upgrade policy:** patch auto; minor within support; 8.5 evaluate. **Pinning policy:** pin platform PHP in `composer.json` `config.platform`. **Approval status:** APPROVED FOR IMPLEMENTATION (PHP 8.4). **Implementation phase:** Phase 1.

### PostgreSQL (database)
- **Purpose:** Primary datastore (shared-schema multi-tenant). **Official source:** postgresql.org. **License:** PostgreSQL License. **Supported version range:** 16–17 baseline; 18 = evaluate. **Compatibility:** Laravel 12 / PDO. **Security posture:** in support window. **Maintenance status:** active (5-year major support). **Replacement option:** none for baseline. **Upgrade policy:** minor auto; major via migration + backup/restore test. **Pinning policy:** pin server major in provisioning. **Approval status:** APPROVED FOR IMPLEMENTATION (PostgreSQL 17). **Implementation phase:** Phase 1.

### Redis (cache + queue)
- **Purpose:** Cache and queue backend. **Official source:** redis.io / `redis/redis`. **License:** RSALv2 / SSPL (source-available). **Supported version range:** 7.x. **Compatibility:** Laravel cache/queue drivers. **Security posture:** maintained; **license posture flagged**. **Maintenance status:** active. **Replacement option:** **Valkey** (OSI-licensed drop-in). **Upgrade policy:** minor auto within 7.x; license re-review each major. **Pinning policy:** pin server major. **Approval status:** APPROVED WITH CONDITIONS (license reviewed; Valkey evaluated as OSI alternative). **Implementation phase:** Phase 1.

### Nginx (web server / reverse proxy)
- **Purpose:** Reverse proxy, TLS termination. **Official source:** nginx.org. **License:** BSD-2-Clause. **Supported version range:** stable line. **Compatibility:** PHP-FPM. **Security posture:** stable line patched. **Maintenance status:** active. **Replacement option:** Caddy (evaluate only). **Upgrade policy:** track stable. **Pinning policy:** pin distro package. **Approval status:** APPROVED FOR IMPLEMENTATION (stable). **Implementation phase:** Phase 1.

## First-party / auth / RBAC

| Package | Purpose | Source | License | Version range | Security posture | Maintenance | Replacement | Approval status | Phase |
|---------|---------|--------|---------|---------------|------------------|-------------|-------------|-----------------|-------|
| Laravel Fortify | Auth backend | `laravel/fortify` | MIT | tracks Laravel 12 | active | first-party | none | APPROVED WITH CONDITIONS (pin to Laravel 12 line) | Phase 1 |
| Laravel Sanctum | API/SPA tokens | `laravel/sanctum` | MIT | tracks Laravel 12 | active | first-party | Passport (evaluate) | APPROVED WITH CONDITIONS (pin to Laravel 12 line) | Phase 1 |
| Spatie laravel-permission | RBAC + branch scoping | `spatie/laravel-permission` | MIT | current major | active | Spatie (well-maintained) | native gates (evaluate) | APPROVED WITH CONDITIONS (verify Laravel 12 compat) | Phase 1 |

## Operations / tooling

| Package | Purpose | Source | License | Version range | Security posture | Maintenance | Replacement | Approval status | Phase |
|---------|---------|--------|---------|---------------|------------------|-------------|-------------|-----------------|-------|
| Laravel Horizon | Queue monitoring / worker supervision | `laravel/horizon` | MIT | tracks Laravel 12 | active | first-party | custom supervisor (evaluate) | EVALUATE DURING IMPLEMENTATION | Phase 3 |
| AWS SDK for PHP | S3-compatible client | `aws/aws-sdk-php` | Apache-2.0 | current major | active | AWS | league/flysystem-aws-s3 | EVALUATE DURING IMPLEMENTATION | Phase 2 |
| league/flysystem | Storage abstraction | `league/flysystem` | MIT | current major | active | The PHP League | native | EVALUATE DURING IMPLEMENTATION | Phase 2 |
| Pest | Test framework | `pestphp/pest` | MIT | current major | active | maintained | PHPUnit | EVALUATE DURING IMPLEMENTATION | Phase 1 |
| PHPUnit | Test framework | `phpunit/phpunit` | BSD-3-Clause | current major | active | Sebastian Bergmann | Pest | APPROVED FOR IMPLEMENTATION (base test runner) | Phase 1 |
| PHPStan | Static analysis | `phpstan/phpstan` | MIT | current major | active | maintained | Psalm | APPROVED FOR IMPLEMENTATION | Phase 1 |
| Larastan | Laravel-aware static analysis | `larastan/larastan` | MIT | current major | active | maintained | PHPStan base | APPROVED WITH CONDITIONS (Laravel 12 compat) | Phase 1 |
| Laravel Pint | Code style | `laravel/pint` | MIT | current | active | first-party | PHP-CS-Fixer | APPROVED FOR IMPLEMENTATION | Phase 1 |
| composer audit | Vulnerability audit | Composer built-in | MIT (Composer) | Composer 2.x | active | maintained | Roave SecurityAdvisories | APPROVED FOR IMPLEMENTATION | Phase 1 |
| OpenTelemetry PHP | Tracing/metrics | `open-telemetry/opentelemetry` | Apache-2.0 | current major | active | CNCF | vendor SDK | EVALUATE DURING IMPLEMENTATION | Phase 3 |
| Sentry Laravel | Error tracking | `sentry/sentry-laravel` | MIT | current major | active | Sentry | Bugsnag/Flare (evaluate) | EVALUATE DURING IMPLEMENTATION | Phase 3 |
| Laravel Pennant | Feature flags | `laravel/pennant` | MIT | tracks Laravel 12 | active | first-party | custom flags | EVALUATE DURING IMPLEMENTATION | Phase 2 |
| spatie/laravel-backup | Backup tooling | `spatie/laravel-backup` | MIT | current major | active | Spatie | pg_dump + object sync | EVALUATE DURING IMPLEMENTATION | Phase 3 |

## Build / frontend

| Package | Purpose | Source | License | Version range | Security posture | Maintenance | Replacement | Approval status | Phase |
|---------|---------|--------|---------|---------------|------------------|-------------|-------------|-----------------|-------|
| Node.js | Build tooling runtime | nodejs.org | MIT-style | 24 LTS | active LTS | active | — | APPROVED FOR IMPLEMENTATION (24 LTS, build only) | Phase 1 |
| Vite | Asset bundler | `vite` (npm) | MIT | current major | active | active | Mix (evaluate) | EVALUATE DURING IMPLEMENTATION | Phase 1 |
| Tailwind CSS | CSS framework | `tailwindcss` (npm) | MIT | 4.x | active | active | plain CSS | APPROVED WITH CONDITIONS (verify Blade/Vite v4 integration) | Phase 1 |
| Alpine.js | Lightweight JS interactivity | `alpinejs` (npm) | MIT | NOT VERIFIED this pass | unknown this pass | active | Livewire/native | EVALUATE DURING IMPLEMENTATION | Phase 1 |

## Rejected / not adopted (examples, governed)

| Item | Reason | Status |
|------|--------|--------|
| Laravel 13 (as baseline) | Newer major than canonical §34 baseline; architecture change | EVALUATE DURING IMPLEMENTATION (requires ADR + Master Source update) — not REJECTED |
| Auto-refund / autonomous-action packages | Out of MVP scope (Master Source §48) | REJECTED (needs versioned decision) |
| Any package pulling unpinned installer scripts | Supply-chain risk | REJECTED |

## Implementation-phase mapping

| Phase | Scope | Dependencies entering |
|-------|-------|-----------------------|
| Phase 1 — SaaS foundation | Multi-tenant, auth, RBAC, survey/CSAT | Laravel 12, PHP 8.4, PostgreSQL 17, Redis 7.x, Nginx, Fortify, Sanctum, Spatie permission, PHPUnit, PHPStan, Pint, Node 24, Vite, Tailwind 4, Alpine.js (evaluate) |
| Phase 2 — Feedback/recovery/storage | Feedback inbox, recovery tickets, object storage | AWS SDK / Flysystem, Pennant (evaluate) |
| Phase 3 — AI, integrations, observability | Agentic orchestration, Google, telemetry, backup | Horizon, OpenTelemetry, Sentry, spatie/laravel-backup (all evaluate) |

- A dependency MUST NOT be added earlier than its phase without a governed decision.
- Each phase re-verifies the point-in-time research and refreshes this matrix.

## Compatibility notes

- Fortify, Sanctum, Horizon, Pint, and Pennant track the Laravel release line and MUST be pinned to the Laravel 12 line until a framework-major decision is made.
- Larastan and Spatie laravel-permission MUST be confirmed compatible with Laravel 12 before their status can move to APPROVED FOR IMPLEMENTATION.
- Tailwind CSS 4 requires verifying the Blade/Vite v4 integration path before use.
- Redis remains APPROVED WITH CONDITIONS due to its RSALv2/SSPL license; Valkey (OSI-licensed drop-in) is the evaluated replacement option.

## Approval workflow

1. Propose dependency with purpose + first-party/standard-library alternative considered.
2. Verify official source and package name (typosquat check — see supply-chain governance).
3. Record license, version range, posture, maintenance, replacement option in this matrix.
4. Assign approval status from the literal vocabulary above.
5. On implementation: pin, lock, `composer audit` / vulnerability scan, SBOM entry, evidence.

## Status

Approval matrix documented as planning decisions. Baseline entries such as Laravel 12, PHP 8.4, PostgreSQL 17, PHPUnit, PHPStan, Pint carry **APPROVED FOR IMPLEMENTATION**; unresolved items (Horizon, OpenTelemetry, Sentry, Alpine.js, Pest-vs-PHPUnit choice, backup tooling) carry **EVALUATE DURING IMPLEMENTATION**. No lock file exists; nothing is installed. **PLANNING BASELINE — NOT IMPLEMENTED.**

### Step 7 addition (implemented)
`bacon/bacon-qr-code` (`^3.0`) — **APPROVED FOR IMPLEMENTATION**. Already resolved transitively via `laravel/fortify`
(2FA) and now promoted to a direct dependency for URL-only survey QR SVG rendering (ADR 0058). Official Packagist
source, pinned in `composer.lock`, pure-PHP SVG backend (no imagick/GD requirement), `composer audit` clean. No new
network dependency was downloaded (the package was already vendored).
