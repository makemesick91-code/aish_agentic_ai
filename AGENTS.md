# AGENTS.md — Aish Agentic AI (Codex semantic instructions)

Concise entry point for Codex and any coding agent. This file is a **pointer**, not a copy of canonical
knowledge. It stays in sync with `CLAUDE.md` and `.claude/rules/` — there is **one** source of truth, not two.

## Authority (read before acting)
1. Latest explicit product-owner decision.
2. Master Source — `docs/canonical/MASTER_SOURCE.md` (active **v2.7.0**).
3. PRD — `docs/canonical/PRD.md` (active **v1.3.0**).
4. Approved ADRs — `docs/decisions/adr/` (0001–0046) and `docs/decisions/DECISION_LOG.md`.
5. Application Foundation Rules — `docs/architecture/APPLICATION_FOUNDATION_RULES.md` (AFR-001..133).
6. Other repo docs → derived artifacts → graph/index (**never** authoritative).

Full rules live in `.claude/rules/00`–`28`. Nested `AGENTS.md` files add area-specific rules
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

## CI runtime control (CICD-CTRL-1, AFR-105..126; rule 28)
- Local-first: run `scripts/ci/fast-local.sh` during work; `scripts/ci/full-local.sh` before marking a PR ready.
- Draft-first PR: open feature PRs as drafts (fast CI only); mark ready only after review — one full CI on the
  final head. A CI PASS is valid only for the exact tested SHA; a new commit requires a new full CI (AFR-105,109).
- Never trigger full CI from a feature-branch `push`; never bypass mandatory checks with `[skip ci]`; never
  create a post-tag full-CI PR; never unpin an action; never broaden `GITHUB_TOKEN` beyond need (AFR-110..123).
- Post-tag evidence defaults to a GitHub Release artifact (AFR-117). Report reruns truthfully — no "one run forever" claim (AFR-126).

## Mandatory validation before commit/PR
`scripts/docs/validate.sh` (documentation + Step 2 + Step 3 + Step 4 gates), `scripts/ci/fast-local.sh`
(classifier/gate/topology/workflow-security), `scripts/docs/secret-scan.sh`, `scripts/codex/check-agents.sh`.
Never weaken, skip, or fake a gate (AFR-054,066,072,124).

## Forbidden
Force-push · tag move/delete/recreate · history rewrite · reading `.env`/secrets/keys/dumps · destructive DB/
production commands · bypassing branch protection or approval · claiming the application is implemented/deployed/
pilot-ready without evidence (AFR-066,067,068).

## Truthful status (current)
Documentation, Step 2 persona/pilot, Step 3 architecture, and Step 4 domain/branding/environment/SaaS-Foundation
planning are GO-tagged documentation/governance/planning baselines. CICD-CTRL-1 (Master Source v2.5.0) adds CI
runtime-control governance; CI/release process is configured and evidenced. **Step 5 — Runtime & Repository
Bootstrap** (Master Source v2.6.0, ADRs 0047–0050, AFR-127..133, rule 29) makes the repository a bootable Laravel 12
application: runtime foundation is **CODE COMPLETE**, MERGED, and **GO TAGGED**
(`aish-agentic-ai-step-5-runtime-repository-bootstrap-v1.0.0-go`, peeled `77f9005`), **RUNTIME VERIFIED** from a
clean checkout against real PostgreSQL 17 + Redis 7 (health/queue/scheduler/migrate/asset), with a real
`backend-runtime-ci` gate. **Step 6 — SaaS Core Foundation** (Master Source v2.7.0, ADRs 0051–0053, rule 30)
delivers the consolidated SaaS core (canonical SPRINT-SF-01..SF-04 / EPIC-SF-04..09): secure auth/identity,
tenant/branch lifecycle + memberships, immutable fail-closed tenant context, tenant-scoped RBAC + policies,
append-only audit, and tenant isolation across DB/cache/queue/storage/logging. Step 6 is **CODE COMPLETE** and
**TESTED locally**, **IN PROGRESS toward GO** — NOT merged, NOT tagged, NOT CI-green-on-CI, and NOT
clean-checkout-verified; target GO tag `aish-agentic-ai-step-6-saas-core-foundation-v1.0.0-go`; evidence
forthcoming under `docs/evidence/step-6/`. No domain is owned; nothing is deployed. **Business/module
implementation: NOT STARTED.** Deployment, pilot readiness, pilot runtime, and production readiness: **NOT STARTED.**
