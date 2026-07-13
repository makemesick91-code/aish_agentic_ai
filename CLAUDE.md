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
2. Highest-version canonical **Master Source** — `docs/canonical/MASTER_SOURCE.md` (active **v2.1.1**).
3. Newest approved **PRD** — `docs/canonical/PRD.md`.
4. Approved **ADRs** — `docs/architecture/adr/`, `docs/decisions/adr/`, and `docs/decisions/DECISION_LOG.md`.
5. Other repository documentation.
6. Generated/derived artifacts.
7. Knowledge-graph (Graphify) indexes & summaries — **derived, never authoritative**.

Originals are preserved byte-for-byte in `docs/canonical/source/` and checksummed in
`docs/evidence/source-checksums/`. Never overwrite or delete a historical source.

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

## 5. Truthful status vocabulary

Use only these and only with evidence: `PLANNED`, `IN PROGRESS`, `CODE COMPLETE`, `TESTED`, `MERGED`,
`DEPLOYED`, `RUNTIME VERIFIED`, `PILOT READY`, `PRODUCTION READY`, `BLOCKED`, `NO-GO`, `GO`.
For this documentation foundation also distinguish: `DOCUMENTATION BASELINE COMPLETE`,
`FOUNDATION CONFIGURED`, `GO TAGGED`, and `APPLICATION IMPLEMENTATION NOT STARTED`.
Never say “done”, “GO”, “merged”, “deployed”, or “verified” without the corresponding evidence.

**Current truthful state:** Documentation & Claude Rules Foundation in progress.
**Application implementation: NOT STARTED.**

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
- This foundation's GO tag (`aish-agentic-ai-docs-foundation-v1.0.0-go`) attests documentation/tooling
  readiness only — **not** that the application is implemented, deployed, pilot-ready, or production-ready.
