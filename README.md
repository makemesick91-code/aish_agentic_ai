# Aish Agentic AI

**Aish Agentic AI** is a multi-tenant SaaS platform for Agentic AI Customer Experience,
CSAT / NPS / CES feedback operations, customer recovery, and Google Review management for
multi-branch businesses.

- **Owner:** Aish Tech Solution
- **Canonical repository:** <https://github.com/makemesick91-code/aish_agentic_ai>
- **Normalized identity:** `makemesick91-code/aish_agentic_ai`
- **Primary timezone:** Asia/Makassar
- **Initial market:** Indonesia · **Long-term:** Global

## Current stage

The documentation, persona/pilot, architecture, Step 4 planning, and CICD-CTRL-1 foundations are complete and
GO-tagged. **Step 5 — Runtime & Repository Bootstrap** (Master Source v2.6.0) turns this into a bootable
**Laravel 12** application: modular skeleton, `.env` contract, idempotent bootstrap/verify scripts,
Docker-Compose PostgreSQL 17 + Redis 7, truthful `/live` + `/ready` probes, queue + scheduler foundation, a
security-headers baseline, PHPUnit/Pint/PHPStan, and a real `backend-runtime-ci` gate.

Get started: [docs/getting-started/local-development.md](docs/getting-started/local-development.md)
(`make bootstrap` then `make verify`).

> **Runtime foundation: CODE COMPLETE and RUNTIME VERIFIED locally.**
> **Business/module implementation, deployment, pilot readiness, and production readiness: NOT STARTED.**
> This foundation does not claim the product is feature-complete, deployed, pilot-ready, or production-ready, and
> no domain is owned.

## Canonical documentation

Authoritative knowledge lives here:

- `CLAUDE.md` — concise instruction index and source-authority map for Claude Code.
- `.claude/rules/` — modular, enforceable product / architecture / security / AI / release rules.
- `docs/canonical/` — normalized Master Source and PRD, with preserved originals in `docs/canonical/source/`.
- `docs/` — product, architecture, security, AI, integrations, quality, operations, tooling, decisions, status, release.

## Source authority order

1. Latest explicit product-owner decision.
2. Highest-version canonical Master Source (`docs/canonical/MASTER_SOURCE.md`).
3. Newest approved PRD (`docs/canonical/PRD.md`).
4. Approved ADRs and decision-log entries.
5. Other repository documentation.
6. Generated or derived artifacts.
7. Knowledge-graph indexes and summaries (derived; never authoritative).

## Contributing & security

See `CONTRIBUTING.md` and `SECURITY.md` (added on the documentation-foundation branch).
Never commit secrets, credentials, tokens, `.env` files, database dumps, or private keys.
