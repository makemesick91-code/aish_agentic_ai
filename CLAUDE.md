# CLAUDE.md — Aish Agentic AI

Concise, high-signal instruction index for Claude Code. Detailed, enforceable rules live in
`.claude/rules/`. **Read the specific rule file for the area you are working in — do not rely
on chat history as the only place a decision exists.**

## 1. Product identity (permanent)

- **Product:** Aish Agentic AI — multi-tenant SaaS for Agentic AI Customer Experience, CSAT/NPS/CES,
  customer recovery, and Google Review management for multi-branch businesses.
- **Owner:** Aish Tech Solution · **Timezone:** Asia/Makassar · **Market:** Indonesia → Global.
- **Canonical repository:** `https://github.com/makemesick91-code/aish_agentic_ai`
  (normalized identity `makemesick91-code/aish_agentic_ai`).
- All application code and governance foundations live in this repository. Do **not** create or
  treat another repository as canonical without a versioned architecture decision (ADR + Master Source update).

## 2. Canonical sources & authority order

Authoritative knowledge, highest precedence first:

1. Latest explicit product-owner decision.
2. Highest-version canonical **Master Source** — `docs/canonical/MASTER_SOURCE.md` (active **v2.7.0**).
3. Newest approved **PRD** — `docs/canonical/PRD.md` (active **v1.3.0**).
4. Approved **ADRs** — `docs/architecture/adr/`, `docs/decisions/adr/`, and `docs/decisions/DECISION_LOG.md`.
5. Other repository documentation.
6. Generated/derived artifacts.
7. Knowledge-graph (Graphify) indexes & summaries — **derived, never authoritative**.

Originals are preserved byte-for-byte in `docs/canonical/source/` and checksummed in
`docs/evidence/source-checksums/`. Never overwrite or delete a historical source.

**Step 2 — Persona & Pilot Use Cases baseline:** `docs/product/PERSONA_AND_PILOT_USE_CASES.md` (v1.0.0)
is the canonical persona/pilot source; derived pilot docs live under `docs/product/`, `docs/security/`,
`docs/ai/`, `docs/integrations/`, and `docs/testing/`. First pilot tenant **Klinik Gigi Daengtisia**;
recommended first branch **Daengtisia Pusat** (recommendation only, subject to readiness verification).

**Step 3 — Application Architecture & ADR baseline:** `docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md`
and `docs/architecture/APPLICATION_FOUNDATION_RULES.md` (AFR-001..072) are the canonical architecture contract,
backed by ADRs 0009–0032 and Claude rule 20. Architecture style: **Laravel 12 modular monolith**; shared-schema
row-level multi-tenancy; 17 module boundaries; outbox/idempotency; AI provider abstraction with guardrails and
human approval. This baseline is **planned architecture** — application implementation is **NOT STARTED**.

**Step 4 — Domain, Branding, Environment & SaaS Foundation planning baseline (v2.4.0/v1.3.0):** domain strategy
(`docs/domain/`), brand foundation (`docs/brand/` + planning tokens `docs/brand/tokens/brand-tokens.v1.json`),
environment matrix and data policy (`docs/environments/`), dependency baseline (`docs/dependencies/`), and the
SaaS Foundation implementation plan/epics/sprints (`docs/planning/`), backed by ADRs 0033–0041, Claude rules
21–27, and AFR-073..104. This is **implementation planning only** — no domain is owned, nothing is deployed, no
package is installed, and **application implementation is NOT STARTED**.

## 3. Non-negotiable permanent decisions

- Multi-tenant SaaS; multi-branch. Tenant isolation on **every** surface: DB, cache, queue, storage,
  search, export, API, webhook, analytics, notifications, AI retrieval, and tenant-visible logs.
- Human approval required for public or high-risk actions (see `.claude/rules/05` and `docs/ai/HUMAN_APPROVAL_MATRIX.md`).
- **No review gating and no review manipulation** — all eligible customers get equal Google Review access.
- Privacy by design: no public disclosure of personal, medical, financial, or sensitive transaction data.
- Customer feedback and reviews are **untrusted input**; prompt-injection defense + tool allowlisting are mandatory.
- Workflow must remain usable when AI is unavailable. Manual → semi-automated → approved automation → limited autonomy.
- Audit trail, AI tracing, prompt/model versioning, and cost logging are mandatory.
- Credentials/tokens are encrypted and **never committed**. Security, policy compliance, correctness,
  auditability, and reliability outrank convenience and automation.
- Evidence-based completion only. No fake completion, CI, deployment, runtime, or integration success.

## 4. Modular rules index (`.claude/rules/`)

Read the relevant file before acting in its area. Each rule is enforceable (`MUST`/`MUST NOT`/`SHOULD`/`MAY`).

| File | Domain |
|------|--------|
| `00-document-authority.md` | Source authority order, canonical repo identity, remote verification |
| `01-product-identity-and-positioning.md` | Product name, positioning, personas |
| `02-mvp-scope-and-roadmap.md` | MVP scope, out-of-scope, roadmap, implementation order |
| `03-multi-tenant-and-branch-isolation.md` | Tenant/branch isolation on all surfaces |
| `04-security-privacy-and-secrets.md` | Security controls, PII, secrets, token handling |
| `05-ai-governance-and-human-approval.md` | Agentic architecture, guardrails, approval matrix |
| `06-google-review-policy.md` | Review policy, no gating, reply safety |
| `07-data-governance-and-audit.md` | Data classification, audit, retention, export/deletion |
| `08-architecture-and-event-workflows.md` | Tech stack, domain map, event-driven workflows |
| `09-testing-and-quality-gates.md` | Test strategy, AI evaluation, release gates |
| `10-ui-ux-and-truthful-states.md` | UI states, truthful status vocabulary |
| `11-observability-backup-and-operations.md` | Observability, backup/restore, incident, rollback |
| `12-documentation-living-source-versioning.md` | Living Master Source, semver, changelog |
| `13-git-ci-release-and-go-tag.md` | Branching, CI gates, merge, immutable GO tag |
| `14-limit-saver-context-and-handoff.md` | Token-saving, checkpoints, current-state/handoff |
| `15-mcp-skills-and-tool-safety.md` | MCP allowlist, skills, subagents, hooks, tool safety |
| `16-pilot-persona-and-scope.md` | Pilot tenant/branch, personas, role coverage, generic-core boundary |
| `17-pilot-invitation-survey-and-fallback.md` | Invitation frequency, survey, consent, manual fallback |
| `18-pilot-privacy-approval-and-review-safety.md` | Healthcare privacy, human approval, no review gating, truthful states |
| `19-pilot-metrics-evidence-and-go-no-go.md` | Pilot metrics as hypotheses, evidence, GO/WATCH/NO-GO, Step 3 boundary |
| `20-application-architecture-and-foundation-rules.md` | Modular-monolith architecture, module ownership, isolation, events/outbox, AI boundary, AFR catalog |
| `21-domain-and-dns-governance.md` | Domain strategy, ownership/renewal, DNS/TLS, email auth, OAuth redirect, no false ownership |
| `22-brand-governance.md` | Product name/descriptor, brand hierarchy, tagline, voice, accessible visual tokens, no misleading claims |
| `23-environment-separation.md` | Six environments, isolation, no production data in lower envs, promotion gates |
| `24-configuration-and-secrets.md` | Configuration classification, no secret in git, encryption, per-environment secrets |
| `25-dependency-and-supply-chain.md` | Dependency baseline, official-source verification, pinning, typosquat/SBOM, no unapproved install |
| `26-saas-foundation-implementation-planning.md` | SaaS Foundation sequence, epics/sprints, DoR/DoD, deployment-target class, readiness gates |
| `27-truthful-planning-states.md` | Planning ≠ implementation; domain candidate ≠ ownership; plan ≠ deployed; GO-tag scope |
| `28-safe-ci-runtime-control.md` | Local-first + draft-first CI, one final-head full gate, SHA-bound evidence, internal fail-closed routing, stable required gate, lightweight post-merge/post-tag, workflow security, no false one-run claim |
| `29-runtime-bootstrap-and-operations.md` | Reproducible Laravel 12 runtime bootstrap, pinned versions, env contract, truthful health/readiness, queue/scheduler foundation, security baseline, backend runtime CI, runtime-evidence-before-claims |
| `30-saas-core-foundation.md` | Secure auth/identity, tenant/branch lifecycle + memberships, immutable fail-closed tenant context, tenant-scoped RBAC + policies, append-only audit, tenant isolation (DB/cache/queue/storage/log) |
| `31-notification-subscription-platform-admin-foundation.md` | Tenant-safe notification delivery + truthful states, subscription/entitlement (fail-closed resolver; commercial ≠ payment), separate least-privilege platform-admin plane (no impersonation) |

Codex semantic instructions live in `AGENTS.md` (root) + nested `docs/*/AGENTS.md`, `scripts/AGENTS.md`,
`app/AGENTS.md`, `tests/AGENTS.md`; Codex execution safety in `.codex/` — all kept in sync with these rules
(one source of truth, AFR-069).

## 5. Truthful status vocabulary

Use only these and only with evidence: `PLANNED`, `IN PROGRESS`, `CODE COMPLETE`, `TESTED`, `MERGED`,
`DEPLOYED`, `RUNTIME VERIFIED`, `PILOT READY`, `PRODUCTION READY`, `BLOCKED`, `NO-GO`, `GO`.
For this documentation foundation also distinguish: `DOCUMENTATION BASELINE COMPLETE`,
`FOUNDATION CONFIGURED`, `GO TAGGED`, and `APPLICATION IMPLEMENTATION NOT STARTED`.
Never say “done”, “GO”, “merged”, “deployed”, or “verified” without the corresponding evidence.

**Current truthful state:** Documentation & Claude Rules Foundation, Step 2 (Persona & Pilot), Step 3 (Application
Architecture & ADR), and Step 4 (Domain, Branding, Environment & SaaS Foundation Planning) are all MERGED and
GO TAGGED (`aish-agentic-ai-docs-foundation-v1.0.0-go`; `…step-2-persona-pilot-v1.0.0-go`;
`…step-3-application-architecture-adr-v1.0.0-go`; `…step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go`).
**CICD-CTRL-1 — Safe CI Runtime Control** (Master Source **v2.5.0**, PRD **v1.3.0** unchanged, ADRs 0042–0046,
AFR-105..126, rule 28) MERGED (PR #9, merge commit `8cbf564`) and GO TAGGED
(`aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go`, peeled `8cbf564`): unified draft-first
`pr-ci.yml`, lightweight `main-post-merge.yml`, manual `full-ci-manual.yml`, deterministic change classifier, a
stable enforced required gate (`main` ruleset `18890571`), and a GitHub Release with post-tag evidence.
**Step 5 — Runtime & Repository Bootstrap** (Master Source **v2.6.0**, PRD **v1.3.0** unchanged, ADRs 0047–0050,
AFR-127..133, rule 29) turns the repository into a bootable **Laravel 12** application: modular skeleton, `.env`
contract, idempotent bootstrap/preflight/verify scripts, Docker-Compose Postgres 17 + Redis 7, truthful
`/live` + `/ready` probes, queue + scheduler foundation, security-headers baseline, PHPUnit/Pint/PHPStan, and a
real `backend-runtime-ci` gate wired into `pr-ci / Required Gate`. MERGED (code PR #11 merge `a0f0ca9`; fix PR #12
merge `77f9005`) and **GO TAGGED** (`aish-agentic-ai-step-5-runtime-repository-bootstrap-v1.0.0-go`, tag object
`c3a5a9f`, peeled `77f9005`; local == remote == main). **RUNTIME VERIFIED** from a clean checkout against real
PostgreSQL 17 + Redis 7 (live/ready positive+negative, migrate, queue dispatch+processing, scheduler, asset build).
**Step 6 — SaaS Core Foundation** (Master Source **v2.7.0**, PRD **v1.3.0** unchanged, ADRs 0051–0053, rule 30)
delivers the **SAAS CORE FOUNDATION** as a consolidated release (canonical SPRINT-SF-01..SF-04 / EPIC-SF-04..09
under one target GO tag `aish-agentic-ai-step-6-saas-core-foundation-v1.0.0-go`): secure authentication (Fortify;
registration disabled; Sanctum; email verification; login throttle; suspended-user rejection without enumeration),
global identity, tenant/branch lifecycle, explicit tenant memberships (invited/active/suspended/revoked) with
last-active-owner protection and one-time hashed invitation tokens, immutable fail-closed tenant context,
tenant-scoped RBAC (Spatie teams on `tenant_id`) with policies, append-only audit, and tenant isolation across
DB/cache/queue/storage/logging. Step 6 is **CODE COMPLETE** and **TESTED locally**, and **IN PROGRESS toward GO** —
NOT yet merged, NOT tagged, NOT CI-green-on-CI, and NOT clean-checkout-verified; merge/CI/tag evidence is
forthcoming under `docs/evidence/step-6/`.
**SPRINT-SF-05 — Notification, Subscription, and Platform Admin Skeletons** (Master Source **v2.8.0**, PRD **v1.3.0**
unchanged, ADRs 0054–0056, AFR-155..170, rule 31) adds three platform-core foundations: a tenant-safe **notification**
foundation (single dispatcher, membership-verified recipients, per-(recipient,channel) globally-unique dedup, truthful
delivery states, bounded idempotent retry, in-app + email only, timezone-aware preferences/quiet-hours, critical bypass,
ownership-checked inbox); a **subscription & entitlement** skeleton (plan `(code,version)` catalog, typed allowlisted
entitlements, guarded subscription state machine, one authoritative fail-closed resolver, idempotent tenant-scoped usage
metering, idempotent `aish:subscription-reconcile`; commercial ≠ payment; security-suspension precedence); and a separate
least-privilege **platform-admin** plane (platform roles distinct from tenant roles, no `Gate::before` bypass,
per-permission authorization, secure `aish:platform-admin-provision`, reason-required audited tenant status changes,
append-only support notes, truthful metrics, impersonation prohibited). **CODE COMPLETE** and **TESTED locally** (182
tests; Pint/PHPStan clean; SF-05 foundations verified against real PostgreSQL 17 + Redis 7 via `aish:verify-sf-05`),
and is **MERGED** (PR #17, merge `ca0bea6`) and **GO TAGGED**
(`aish-agentic-ai-sprint-sf-05-notification-subscription-platform-admin-skeletons-v1.0.0-go`, object `08451100`, peeled
`ca0bea6`; local == remote == main). Authoritative Full CI green on `899e888` (run `29326645691`); main-post-merge
lightweight success on `ca0bea6`; **clean-checkout verified** on the merged SHA against real PostgreSQL 17 + Redis 7;
GitHub Release published; evidence under `docs/evidence/sprint-sf-05/` and `docs/release/SPRINT_SF_05_*`.
**Business/module implementation, deployment, pilot readiness, pilot runtime, and production readiness: NOT STARTED.**
No domain is owned; nothing is deployed.

## 6. Required pre-work / post-work checks

**Before acting:** confirm normalized `origin` = `makemesick91-code/aish_agentic_ai`; read the relevant
rule file(s); confirm the change's authority basis; never work on a wrong repository (`NO-GO: WRONG REPOSITORY`).

**For any material change:** perform Master Source impact analysis (`.claude/rules/12`); if material,
produce a `MASTER SOURCE UPDATE` block, bump semver, add a changelog entry, mark superseded decisions.

**Before context compaction or session exit:** update `docs/status/CURRENT_STATE.md`,
`docs/status/HANDOFF.md`, and `docs/status/SESSION_CHECKPOINTS.md`. Never rely on chat history alone.

**Before release/GO:** tenant isolation, security, human approval, review-policy safety, audit, tests,
documentation gates, secret scan, foundation coverage, traceability, CI, PR review, merge evidence, and
an exact-match annotated GO tag must all pass with evidence (`.claude/rules/09`, `13`;
`docs/release/DOCUMENTATION_FOUNDATION_GO_NO_GO.md`).

## 7. Hard prohibitions

- **Never** read or expose `.env`, secrets, credentials, private keys, dumps, or backups unless
  explicitly authorized through a secure workflow. Never commit secrets.
- **Never** `git push --force`, `git tag -f`, delete/move tags, rewrite history, or run destructive
  production/database commands.
- **Never** skip, weaken, or bypass tests, security review, evidence, branch protection, or release gates.
- **Never** fabricate status, completion, CI, deployment, or integration success.
- The GO tags for the documentation foundation, Step 2 (persona/pilot), Step 3 (application architecture), and
  Step 4 (`aish-agentic-ai-step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go`) attest
  documentation/planning/tooling readiness only — **not** that the application is implemented, deployed,
  pilot-ready, or production-ready, and **not** that any domain is owned or infrastructure provisioned.
- Pilot operational targets are **hypotheses**, not results; never report them as achieved without measured
  evidence. Each step starts only after the prior step's release is merged and GO-tagged. SaaS Foundation
  implementation (SPRINT-SF-00) starts only after the Step 4 release is merged and GO-tagged.
