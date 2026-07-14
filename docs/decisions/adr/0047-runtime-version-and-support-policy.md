# ADR 0047 — Runtime Version and Support Policy

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 5 Runtime & Repository Bootstrap; runtime IMPLEMENTED (foundation), business modules NOT STARTED
- **Owner:** Principal Architect / DevOps Engineer
- **Rule:** `.claude/rules/29` (AFR-127) · **Canonical:** Master Source v2.6.0 §70; rules 08, 25

## Context
Step 5 turns the documentation repository into a bootable Laravel 12 application. The runtime language,
datastore, cache, and client versions must be explicit, production-supported, and identical across local, CI,
and documentation so a clean checkout is reproducible (rule 25, ADR 0038 baseline). The build must not drift
between a developer machine and CI.

## Decision
The runtime baseline is **Laravel 12** on **PHP 8.4** (composer requires `^8.3`, the supported minimum), with the
composer resolution platform pinned to `8.4.1`, **PostgreSQL 17**, **Redis 7**, **Node.js 22 LTS**, and Composer 2.
The default Redis client is **predis** (pure PHP, no extension), with phpredis noted as an optional production
performance optimization. CI installs PHP 8.4 and runs against PostgreSQL 17 + Redis 7 service containers; local
development is verified on PHP 8.5.x which satisfies `^8.3`. Framework-major changes (e.g. Laravel 13) require a
new ADR + Master Source update.

## Alternatives
- **Pin PHP to the local 8.5 only** — rejected: 8.5 is newer than the supported baseline and would drift from CI.
- **phpredis as the required client** — rejected as a hard default: it needs a compiled extension present in every
  environment; predis keeps the clean-checkout path extension-free.
- **SQLite as the primary datastore** — rejected: PostgreSQL is the canonical datastore (ADR 0038); SQLite is used
  only for the fast test suite.

## Consequences
The lockfile resolves against the 8.4.1 platform floor so CI and local installs match. Newer PHP (8.5) remains
usable locally. Redis works without a PHP extension everywhere. Version advances are deliberate, ADR-gated events.

## Impacts
- **Security:** pinned, audited dependencies (`composer audit`, `npm audit`); no unpinned installers.
- **Privacy:** none (version policy only).
- **Tenant isolation:** unaffected at this layer; tenant context arrives in the SaaS Foundation step.
- **Database:** PostgreSQL 17 is the canonical connection; SQLite is test-only.
- **Operational:** identical versions across local/CI/docs; reproducible clean-checkout bootstrap.
- **Cost:** minimal — standard runner images; no paid services introduced.

## Verification / fitness function
`scripts/runtime/preflight.sh` asserts PHP ≥ 8.3; `composer.json` pins `config.platform.php`; the CI backend job
uses PHP 8.4 + PostgreSQL 17 + Redis 7; `scripts/runtime/verify-runtime.sh` proves connectivity. RT-01, RT-05.

## Related
Requirement: Master Source v2.6.0 §70; PRD v1.3.0 (unchanged). Application rules: AFR-127, AFR-131. Rules: 29, 25,
08. ADRs: 0038, 0009.

## Evidence
`composer.json`, `composer.lock`, `.nvmrc`-equivalent (`package.json` engines + Node 22 in CI), `.env.example`,
`docs/architecture/runtime-bootstrap.md`; `docs/evidence/step-5/runtime/`.

## Non-claims
Selecting runtime versions does not claim the application is feature-complete, deployed, pilot-ready, or
production-ready. No infrastructure is provisioned by this decision.

## Supersession
The version baseline may advance via a recorded decision; a framework-major change requires a new ADR + Master
Source update. Reproducibility and official-source verification are permanent.
