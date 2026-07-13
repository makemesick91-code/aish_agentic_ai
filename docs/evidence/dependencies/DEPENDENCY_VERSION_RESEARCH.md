# Dependency Version Research — Point-in-Time Evidence

**Status:** POINT-IN-TIME RESEARCH — no package installed, no lock file generated. **PLANNING BASELINE — NOT IMPLEMENTED.**
**Rule:** `.claude/rules/25`; AFR-095, AFR-096. **Canonical:** Master Source v2.4.0 §68; §34 (core stack).
**Verification date:** 2026-07-13 (Asia/Makassar). **Method:** official-source web research (release/EOL pages).

## Findings (as researched 2026-07-13)
| Component | Latest / current | Actively supported | Step 4 approved baseline | Note |
|-----------|------------------|--------------------|--------------------------|------|
| Laravel | 13 (released 2026-03-17) | 12 (bug-fix → 2026-08-13, security → 2027-02-24) and 13 | **Laravel 12** (canonical §34) | Adopting 13 is an architecture change → requires ADR + Master Source update; 13 = EVALUATE DURING IMPLEMENTATION |
| PHP | 8.5.x (latest); 8.4 recommended for production | 8.2 (security), 8.3, 8.4, 8.5 | **PHP 8.4** (satisfies canonical "8.3+"), min 8.3 | 8.3 active → 2026-11-23; 8.4 active → 2028-12-31 |
| PostgreSQL | 18 (17 mainstream GA) | 14–18 | **PostgreSQL 17** (16 acceptable) | 5-year support/major; 18 = EVALUATE |
| Redis | 7.4.x | 7.x | **Redis 7.x** | License change (RSALv2/SSPL) — **Valkey** is an OSI-licensed drop-in replacement to evaluate for supply-chain/license posture |
| Nginx | stable 1.2x | stable line | **Nginx stable** | Reverse proxy / TLS termination |
| Node.js | 24 LTS (npm 11); 26 LTS in Oct 2026 | 24 LTS | **Node.js 24 LTS** (build tooling only) | Build-time; not a runtime service |
| Tailwind CSS | 4.x (v4 stable) | 4.x | **Tailwind CSS 4** | Verify Blade/Vite integration at implementation |
| Alpine.js | not confirmed this pass | — | **EVALUATE DURING IMPLEMENTATION** | Version NOT VERIFIED this pass; confirm at implementation |
| Fortify / Sanctum | tracks Laravel 12 | — | **APPROVED WITH CONDITIONS** (pin to Laravel 12 line) | First-party auth (canonical §34) |
| Spatie laravel-permission | current major | — | **APPROVED WITH CONDITIONS** (verify Laravel 12 compat) | RBAC (canonical §34) |

## Truthful limits
- Versions above are **research at a point in time**, not an installation record. No `composer`/`npm` command was
  run; **no lock file exists**; nothing is installed.
- Where a newer major exists than the canonical baseline (Laravel 13, PostgreSQL 18, PHP 8.5), the newer version is
  recorded as **EVALUATE DURING IMPLEMENTATION**; adopting it requires the governance in
  [Upgrade & Security-Patch Policy](../../dependencies/UPGRADE_AND_SECURITY_PATCH_POLICY.md) and, for framework
  majors, an ADR + Master Source update.
- Full per-dependency status lives in [Dependency Approval Matrix](../../dependencies/DEPENDENCY_APPROVAL_MATRIX.md).

## Sources (official / authoritative, researched 2026-07-13)
- Laravel releases & PHP requirement — laravel.com/docs releases, endoflife.date/laravel
- PHP supported versions — php.net/supported-versions.php, endoflife.date/php
- PostgreSQL releases — postgresql.org release notes, endoflife.date/postgresql
- Redis releases — github.com/redis/redis/releases, redis.io; Valkey — valkey.io
- Node.js releases — nodejs.org/en/about/previous-releases, endoflife.date/nodejs
- Tailwind CSS — npmjs.com/package/tailwindcss
