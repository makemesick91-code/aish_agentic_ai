# Step 8 — Feedback Operations Foundation — Tag Verification & Release Evidence

**Status:** IN PROGRESS toward GO — CODE COMPLETE and TESTED locally; **NOT merged, NOT tagged, NOT CI-green-on-CI,
NOT clean-checkout-verified** against real PostgreSQL 17 + Redis 7 at authoring time. Tag/CI/clean-checkout fields
below are **PENDING** and will be filled at tag time.

## Release manifest
- **Scope:** Step 8 Feedback Operations Foundation (Master Source v2.10.0 §74; ADRs 0060–0062; AFR-188..210; Claude
  rule 33).
- **Base branch:** `main`.
- **Feature branch:** `feature/step-8-feedback-operations-foundation`.
- **PR:** PENDING (`[STEP-8] Feedback Operations Foundation`).
- **Final head SHA (authoritative CI target):** PENDING.
- **Authoritative Full CI run:** PENDING — must be **success** (Classify, Backend runtime CI on real PostgreSQL 17 +
  Redis 7 incl. `aish:verify-step-8`, Full documentation CI, Workflow security CI, **Required Gate**).
- **Merge commit (default branch):** PENDING.
- **Post-merge integrity run:** PENDING (`main-post-merge`, lightweight).

## GO tag
- **Name:** `aish-agentic-ai-step-8-feedback-operations-foundation-v1.0.0-go`
- **Type:** annotated (to be verified `git cat-file -t` = `tag`).
- **Tag object:** PENDING — must be **local == remote**.
- **Peeled commit (`^{}`):** PENDING — must be **local == remote == `origin/main`**.
- **Full CI on tag:** must be none — tag creation must **not** trigger Full CI (rule 28 / AFR-116).
- **GitHub Release:** PENDING (to be published from the exact tag).
- **Immutability:** the tag will not be moved by any post-tag evidence sync; prior GO tags remain immutable.

## Clean-checkout verification (merged SHA)
PENDING. From a fresh clone checked out at the merged SHA, `composer install` from the lockfile, `.env` pointed at the
running containers, `scripts/runtime/verify-step-8.sh` against **real PostgreSQL 17 + Redis 7** — target result
**PASS**:
- `migrate:fresh` (PostgreSQL) — PENDING
- `aish:verify-saas-core` (Step 6 isolation regression) — PENDING
- `aish:verify-sf-05` (SF-05 regression) — PENDING
- `aish:verify-step-7` (Step 7 survey regression) — PENDING
- `aish:verify-step-8` (18 positive + negative checks: idempotent projection, reconcile idempotency, lifecycle
  guards, scope-validated assignment, membership-revocation fail-close, tenant-isolated tags, append-only
  notes/timeline, private + MIME-validated attachments, permission-aware content search, bounded bulk, requester-
  scoped export, CSV-injection guard, entitlement fail-closed, usage idempotency, cross-tenant isolation) — PENDING
- no secret value leaked into verification logs — PENDING
- hermetic test suite: **352 passed** locally at authoring time; CI re-run PENDING

## Independent Step 8 security review (precondition gate)
COMPLETE — **PASS after fixes**, no unresolved critical/high/medium (F-1 HIGH export-download re-authorization; F-2/F-3
LOW hardening — all FIXED with regression coverage; 14/14 other vectors PASS)
(`docs/evidence/step-8-independent-security-review.md`).

## Local quality gates (final candidate)
Pint clean; PHPStan (Larastan) **0 errors**; `aish:verify-step-8` 18 checks pass on SQLite; hermetic suite **352
passing**; `scripts/docs/validate.sh` **all gates pass** (markdown, links, version-consistency, foundation coverage,
ADR structure, rule frontmatter, secret scan, Graphify build/drift/query-smoke); CI topology / workflow-security /
change-classifier / required-gate validators pass. `composer audit` + `npm audit` and `npm run build` — to be
re-confirmed on the final head.

## Scope of this tag
Will attest feedback-operations foundation readiness **only** — NOT deployment, pilot, or production readiness. Google
Review anti-gating preserved. AI/recovery/SLA/Google/agent/RAG/billing modules and deployment/pilot/production remain
**NOT STARTED**; no domain is owned; nothing is deployed.
