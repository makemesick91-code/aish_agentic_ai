# AGENTS.md — Aish Agentic AI (Codex semantic instructions)

Concise entry point for Codex and any coding agent. This file is a **pointer**, not a copy of canonical
knowledge. It stays in sync with `CLAUDE.md` and `.claude/rules/` — there is **one** source of truth, not two.

## Authority (read before acting)
1. Latest explicit product-owner decision.
2. Master Source — `docs/canonical/MASTER_SOURCE.md` (active **v2.3.0**).
3. PRD — `docs/canonical/PRD.md` (active **v1.2.0**).
4. Approved ADRs — `docs/decisions/adr/` (0001–0032) and `docs/decisions/DECISION_LOG.md`.
5. Application Foundation Rules — `docs/architecture/APPLICATION_FOUNDATION_RULES.md` (AFR-001..072).
6. Other repo docs → derived artifacts → graph/index (**never** authoritative).

Full rules live in `.claude/rules/00`–`20`. Nested `AGENTS.md` files add area-specific rules
(`docs/*/AGENTS.md`, `scripts/AGENTS.md`, `app/AGENTS.md`, `tests/AGENTS.md`).

## Canonical repository
Target only `makemesick91-code/aish_agentic_ai`. Verify normalized `origin` before any git write; on mismatch
stop with `NO-GO: WRONG REPOSITORY`.

## Non-negotiable (AFR summary)
- Multi-tenant SaaS: tenant/branch isolation on **every** surface (AFR-006..020).
- Human approval for public/high-risk actions; **no review gating**; equal Google Review access (AFR-027,028).
- Customer content is **untrusted**; prompt-injection defense + tool allowlisting (AFR-049,050).
- No personal/medical/financial data in public output or AI input by default (AFR-046,048).
- Idempotency + outbox + retry + dead-letter; **no external success before provider verification** (AFR-031..036).
- Manual fallback: core workflow works without AI (AFR-045). Secrets never committed; OAuth encrypted (AFR-023,024).
- Audit + cost + prompt/model versioning recorded (AFR-043,044,059). Truthful states only (AFR-036,068).

## Mandatory validation before commit/PR
`scripts/docs/validate.sh` (documentation + Step 2 + Step 3 gates), `scripts/docs/secret-scan.sh`,
`scripts/codex/check-agents.sh`. Never weaken, skip, or fake a gate (AFR-054,066,072).

## Forbidden
Force-push · tag move/delete/recreate · history rewrite · reading `.env`/secrets/keys/dumps · destructive DB/
production commands · bypassing branch protection or approval · claiming the application is implemented/deployed/
pilot-ready without evidence (AFR-066,067,068).

## Truthful status (current)
Documentation, Step 2 persona/pilot, and (on GO) Step 3 architecture are documentation/governance baselines.
**Application implementation: NOT STARTED.** Deployment, pilot readiness, pilot runtime, and production
readiness: **NOT STARTED.**
