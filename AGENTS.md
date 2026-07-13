# AGENTS.md — Aish Agentic AI (Codex semantic instructions)

Concise entry point for Codex and any coding agent. This file is a **pointer**, not a copy of canonical
knowledge. It stays in sync with `CLAUDE.md` and `.claude/rules/` — there is **one** source of truth, not two.

## Authority (read before acting)
1. Latest explicit product-owner decision.
2. Master Source — `docs/canonical/MASTER_SOURCE.md` (active **v2.4.0**).
3. PRD — `docs/canonical/PRD.md` (active **v1.3.0**).
4. Approved ADRs — `docs/decisions/adr/` (0001–0041) and `docs/decisions/DECISION_LOG.md`.
5. Application Foundation Rules — `docs/architecture/APPLICATION_FOUNDATION_RULES.md` (AFR-001..104).
6. Other repo docs → derived artifacts → graph/index (**never** authoritative).

Full rules live in `.claude/rules/00`–`27`. Nested `AGENTS.md` files add area-specific rules
(`docs/*/AGENTS.md`, `scripts/AGENTS.md`, `app/AGENTS.md`, `tests/AGENTS.md`).

## Canonical repository
Target only `makemesick91-code/aish_agentic_ai`. Verify normalized `origin` before any git write; on mismatch
stop with `NO-GO: WRONG REPOSITORY`.

## Step 4 planning baselines (AFR-073..104)
- Product name "Aish Agentic AI" fixed; domains org-owned; **availability ≠ ownership** (AFR-073,074,076).
- Six isolated environments; **no production data in local/test/CI/staging**; per-environment secrets (AFR-087..091).
- Dependency baseline Laravel 12/PHP 8.4/PostgreSQL 17/Redis 7.x; **no package installed** in planning (AFR-095,096).
- SaaS Foundation sequence: tenant context before features; observability + tested restore before pilot (AFR-099..101).
- Planning ≠ implementation; the Step 4 GO tag attests planning readiness only (AFR-103,104).

## Non-negotiable (AFR summary)
- Multi-tenant SaaS: tenant/branch isolation on **every** surface (AFR-006..020).
- Human approval for public/high-risk actions; **no review gating**; equal Google Review access (AFR-027,028).
- Customer content is **untrusted**; prompt-injection defense + tool allowlisting (AFR-049,050).
- No personal/medical/financial data in public output or AI input by default (AFR-046,048).
- Idempotency + outbox + retry + dead-letter; **no external success before provider verification** (AFR-031..036).
- Manual fallback: core workflow works without AI (AFR-045). Secrets never committed; OAuth encrypted (AFR-023,024).
- Audit + cost + prompt/model versioning recorded (AFR-043,044,059). Truthful states only (AFR-036,068).

## Mandatory validation before commit/PR
`scripts/docs/validate.sh` (documentation + Step 2 + Step 3 + Step 4 gates), `scripts/docs/secret-scan.sh`,
`scripts/codex/check-agents.sh`. Never weaken, skip, or fake a gate (AFR-054,066,072).

## Forbidden
Force-push · tag move/delete/recreate · history rewrite · reading `.env`/secrets/keys/dumps · destructive DB/
production commands · bypassing branch protection or approval · claiming the application is implemented/deployed/
pilot-ready without evidence (AFR-066,067,068).

## Truthful status (current)
Documentation, Step 2 persona/pilot, Step 3 architecture (GO tagged), and Step 4 domain/branding/environment/
SaaS-Foundation planning are documentation/governance/planning baselines. No domain is owned; no package is
installed; nothing is deployed. **Application implementation: NOT STARTED.** Domain ownership, deployment, pilot
readiness, pilot runtime, and production readiness: **NOT STARTED.**
