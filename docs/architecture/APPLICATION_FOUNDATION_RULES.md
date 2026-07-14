# Application Foundation Rules (AFR) — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0; PRD v1.2.0 · **Rules:** all `.claude/rules/*` + `20` · **ADRs:** 0009–0032.

Canonical, stable-ID application rules. Each **AFR** is enforceable (`MUST`/`MUST NOT`) and maps to an ADR, a
Claude rule, an AGENTS instruction area, a fitness function, and evidence. This is the machine-checkable spine:
**Canonical Decision → ADR → AFR → AGENTS → Claude rule → (Codex rule) → Fitness function → Evidence**. No
permanent decision is orphan (verified by [Traceability Matrix](../quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md)).

Legend: **Rule** = `.claude/rules/NN`; **FF** = fitness function id ([catalog](ARCHITECTURE_FITNESS_FUNCTIONS.md)).

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF |
|-----|-----------------------------|-----|------|----|
| AFR-001 | Work MUST follow the source-authority order and target the canonical repo `makemesick91-code/aish_agentic_ai` | — | 00 | FF-DOC-02 |
| AFR-002 | Product identity MUST be "Aish Agentic AI"; core stays generic (pilot MUST NOT narrow it) | — | 01,16 | FF-DOC-02 |
| AFR-003 | Architecture MUST be a Laravel 12 modular monolith; microservices are NOT default | 0009 | 08 | FF-MOD-01 |
| AFR-004 | Each module MUST own its data; a module MUST NOT write another module's tables | 0010 | 08 | FF-MOD-02 |
| AFR-005 | Cross-module work MUST use contract/service/event; no undocumented circular dependency; Shared Kernel minimal | 0010 | 08 | FF-MOD-03,04,05 |
| AFR-006 | Every tenant-owned record MUST carry `tenant_id`; tenant isolation on all surfaces | 0011 | 03 | FF-TEN-01 |
| AFR-007 | Branch-relevant records MUST carry `branch_id`; branch-scoped roles see only their branch | 0011 | 03 | FF-DATA-02, FF-TEN-11 |
| AFR-008 | Tenant/branch context MUST be propagated (jobs/events rehydrate); no ambient all-tenant default | 0012 | 03 | FF-TEN-03 |
| AFR-009 | Exactly one owning module per table; migrations create only own tables | 0014 | 07 | FF-DATA-01 |
| AFR-010 | Migrations MUST be expand/contract and reversible; destructive prod migration prohibited without runbook | 0014 | 07,13 | FF-DATA-02 |
| AFR-011 | DB queries MUST be tenant-scoped (scope + tenant-leading constraints) | 0011,0015 | 03 | FF-TEN-01 |
| AFR-012 | Cache keys MUST be tenant/branch-prefixed | 0015 | 03 | FF-TEN-02 |
| AFR-013 | Queue jobs MUST carry + rehydrate tenant context | 0012,0015 | 03 | FF-TEN-03 |
| AFR-014 | Storage paths MUST be tenant/branch-scoped | 0015 | 03 | FF-TEN-04 |
| AFR-015 | Search indexes/queries MUST be tenant-filtered | 0015 | 03 | FF-TEN-05 |
| AFR-016 | Exports MUST be tenant-scoped + audited | 0015,0029 | 03,07 | FF-TEN-06 |
| AFR-017 | Analytics/read-models MUST be tenant-scoped; reporting MUST NOT bypass isolation | 0015 | 03 | FF-TEN-07 |
| AFR-018 | AI retrieval MUST be tenant/branch-filtered, minimal context | 0019,0023 | 03,05 | FF-TEN-08 |
| AFR-019 | Audit views MUST be tenant-scoped; audit MUST be append-only, non-deletable | 0024 | 07 | FF-TEN-13 |
| AFR-020 | Notifications MUST be tenant-scoped and honor opt-out | 0015 | 03,17 | FF-TEN-10 |
| AFR-021 | Authentication MUST use Fortify/Sanctum, MFA-ready, AI-independent | 0013 | 04 | FF-TEN-11 |
| AFR-022 | Authorization MUST be least-privilege with tenant+branch scope; high-risk needs approval | 0013 | 03,05 | FF-SEC-03 |
| AFR-023 | Secrets/`.env`/tokens/keys/dumps MUST NOT be committed | 0025 | 04 | FF-SEC-01 |
| AFR-024 | Access tokens MUST be encrypted; refresh tokens MUST NOT be plaintext; rotation supported | 0022 | 04 | FF-SEC-02 |
| AFR-025 | OAuth state MUST be validated; tenants can disconnect + delete credentials | 0022 | 04,06 | FF-SEC-02 |
| AFR-026 | Data MUST be classified; PII minimized; retention configurable | 0029 | 07 | FF-DATA-03 |
| AFR-027 | Human approval MUST gate §33/PRD-§13 triggers; every public reply human-approved | 0028 | 05,06,18 | FF-SEC-03 |
| AFR-028 | Review gating/manipulation is PROHIBITED; equal Google Review access | 0021,0028 | 06,18 | FF-SEC-04 |
| AFR-029 | A kill switch MUST exist and MUST NOT break manual operation | 0016,0028 | 05 | FF-AI-05 |
| AFR-030 | Feature flags MUST be auditable and MUST NOT disable audit/approval/isolation | 0028 | 05 | FF-AI-03 |
| AFR-031 | External side effects MUST be idempotent (dedupe on event_id) | 0016 | 08 | FF-REL-02 |
| AFR-032 | External effects MUST use a transactional outbox | 0016 | 08 | FF-REL-01 |
| AFR-033 | Retry MUST be bounded with backoff | 0016 | 08 | FF-REL-03 |
| AFR-034 | Poison messages MUST dead-letter; replay is authorized + audited | 0016 | 08 | FF-REL-04 |
| AFR-035 | Retry MUST NOT create duplicate invitation/ticket/reply/charge | 0016 | 05,08 | FF-REL-05 |
| AFR-036 | External success MUST NOT be reported before provider verification; truthful states | 0016,0017 | 10 | FF-REL-06 |
| AFR-037 | Public API MUST be tenant-scoped from credential (not client input), IDOR-resistant | 0017 | 03,04 | FF-TEN-11, FF-API-03 |
| AFR-038 | Public API MUST be `/api/v1` versioned with controlled deprecation + idempotency key | 0017 | 08 | FF-API-01,02 |
| AFR-039 | Webhooks MUST be signed, replay-protected, idempotent, tenant-scoped | 0017 | 04 | FF-API-04, FF-TEN-12 |
| AFR-040 | API MUST enforce rate limiting + request/correlation id + redacted logs | 0017,0024 | 04,08 | FF-TEN-14 |
| AFR-041 | AI output MUST be structured (JSON Schema), validated | 0019 | 05 | FF-AI-01 |
| AFR-042 | AI service extraction MUST require ADR 0020 evidence; no extraction now | 0020 | 08 | FF-DOC-02 |
| AFR-043 | AI token + cost MUST be logged and cost-capped | 0019,0024 | 05,07 | FF-AI-04 |
| AFR-044 | AI prompt version, model version, tool calls, traces MUST be recorded (redacted) | 0019,0024 | 05 | FF-AI-04, FF-AI-02 |
| AFR-045 | Every AI step MUST have a manual fallback; workflow works without AI | 0019 | 05 | FF-AI-05 |
| AFR-046 | Knowledge retrieval MUST be tenant-scoped; KB MUST NOT index secrets/PII/MED | 0023 | 03,04,18 | FF-TEN-09 |
| AFR-047 | UI MUST provide empty/loading/failure/permission states + truthful vocab; not AI-dependent | 0018 | 10 | FF-DOC-03 |
| AFR-048 | Personal/medical/financial/sensitive data MUST NOT appear in public output | 0029 | 04,18 | FF-SEC-03 |
| AFR-049 | Prompt-injection defense + tool allowlisting MUST be enforced | 0019 | 04,05 | FF-SEC-05 |
| AFR-050 | Customer feedback/reviews MUST be treated as untrusted input | 0019 | 04,05,06 | FF-SEC-05 |
| AFR-051 | Google integration MUST be isolated behind adapter; sync/publish via outbox | 0021 | 06 | FF-REL-06 |
| AFR-052 | Google reply MUST be professional, disclose no PII/medical, human-approved | 0021 | 06,18 | FF-SEC-03 |
| AFR-053 | Configuration MUST be classified; no environment inherits another's secrets | 0025 | 04,11 | FF-SEC-01 |
| AFR-054 | CI MUST run all mandatory gates with least privilege; gates MUST NOT be weakened | 0026 | 09,13 | FF-DOC-01 |
| AFR-055 | Backups MUST exist and be encrypted | 0027 | 11 | FF-DOC-03 |
| AFR-056 | Restore MUST be tested before production GO; DR sequence documented | 0027 | 11 | FF-DOC-03 |
| AFR-057 | Observability signals (logs/traces/metrics/cost/health) MUST exist with min alerts | 0024 | 11 | FF-DOC-03 |
| AFR-058 | Logs MUST redact secrets/PII/MED; tenant-facing logs tenant-scoped | 0024 | 04,07 | FF-TEN-14 |
| AFR-059 | Audit trail MUST cover important actions; be append-only | 0024 | 07 | FF-TEN-13 |
| AFR-060 | Correlation id MUST thread request→job→event→external call | 0024 | 11 | FF-AI-04 |
| AFR-061 | Data export + deletion MUST be first-class, tenant-scoped, audited | 0029 | 07 | FF-TEN-06 |
| AFR-062 | Testing MUST cover §50 categories; gates evidence-based | 0030 | 09 | FF-DOC-01 |
| AFR-063 | Architecture fitness functions MUST guard boundaries/isolation/reliability | 0030 | 09 | FF-MOD-03 |
| AFR-064 | Dependencies MUST be minimal, pinned, verified; MCP least-privilege, secret-free | 0031 | 04,15 | FF-SEC-01 |
| AFR-065 | Material decisions MUST update the living Master Source (semver + changelog) | — | 12 | FF-DOC-02 |
| AFR-066 | Completion MUST be evidence-based; no fake completion/CI/deploy | — | 09,19 | FF-DOC-03 |
| AFR-067 | GO tags MUST be immutable; no force-push/tag move/delete/history rewrite | — | 13 | FF-DOC-03 |
| AFR-068 | Status MUST be truthful; MUST NOT claim application implemented/deployed/pilot-ready | — | 10,19 | FF-DOC-03 |
| AFR-069 | AGENTS.md, Claude rules, and Codex rules MUST stay in sync (no two sources of truth) | — | 15 | FF-DOC-02 |
| AFR-070 | MCP/tooling MUST be least-privilege, allowlisted, secret-free, and documented | 0031 | 15 | FF-SEC-01 |
| AFR-071 | Deployment topology MUST follow the planned baseline + evidence-gated scale path | 0032 | 11 | FF-DOC-03 |
| AFR-072 | CI workflow permissions MUST be least-privilege; existing gates MUST NOT be removed | 0026 | 13 | FF-DOC-01 |

**72 AFRs (Step 3).** AGENTS-instruction coverage is asserted in [Rule Coverage](../quality/STEP_3_ARCHITECTURE_RULE_COVERAGE.md);
evidence pointers in the [Traceability Matrix](../quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md). No orphan
permanent decision.

## Step 4 — Domain, Branding, Environment, and SaaS Foundation Planning (AFR-073..104)

**Status:** PLANNING BASELINE (Step 4) — **NOT IMPLEMENTED**. **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0 · **ADRs:** 0033–0041 · **Claude rules:** 21–27.
`V4-*` are Step 4 documentation gates defined in the [Step 4 Validation Catalog](../quality/STEP_4_VALIDATION_CATALOG.md)
and enforced by `scripts/docs/check-step4-coverage.sh` / `check-brand-tokens.sh`. FF-* reuse the Step 3 catalog.

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF/Gate |
|-----|-----------------------------|-----|------|---------|
| AFR-073 | Official product name MUST remain "Aish Agentic AI"; it MUST NOT change without an explicit product-owner decision | 0033 | 21,01 | FF-DOC-02 |
| AFR-074 | Domains MUST be owned by the organization account (Aish Tech Solution); MUST NOT be a personal developer account | 0033 | 21 | FF-SEC-01 |
| AFR-075 | Registrar account MUST enforce MFA; domains MUST have transfer lock, a DNSSEC target, and renewal monitoring | 0033 | 21 | V4-DOM-01 |
| AFR-076 | Domain availability MUST be point-in-time verified with evidence; ownership MUST NOT be claimed without registration evidence | 0033 | 21,19 | V4-DOM-02 |
| AFR-077 | Subdomain naming MUST be canonical; non-production domains MUST NOT be confused with production | 0033 | 21,23 | V4-DOM-03 |
| AFR-078 | A preferred primary domain plus at least two fallbacks MUST be recorded before registration | 0033 | 21 | V4-DOM-01 |
| AFR-079 | OAuth redirect URIs MUST be exact-match and per-environment; wildcard redirect URIs MUST NOT be used | 0033 | 21,04 | FF-SEC-02 |
| AFR-080 | The email domain MUST enforce SPF, DKIM, and DMARC; no-reply limitations MUST be documented | 0033 | 21,04 | V4-DOM-04 |
| AFR-081 | The official product descriptor MUST be used; positioning MUST NOT be reduced to a survey/review/chatbot tool | 0041 | 22,01 | FF-DOC-02 |
| AFR-082 | Brand hierarchy MUST be the branded house Aish Tech Solution → Aish Agentic AI | 0041 | 22 | V4-BRAND-01 |
| AFR-083 | A working tagline MUST carry status "APPROVED WORKING BASELINE"; it MUST NOT be presented as a final trademark | 0041 | 22 | V4-BRAND-01 |
| AFR-084 | Brand tokens MUST be versioned and labelled planning; they MUST NOT be claimed implemented in any UI | 0041 | 22,10 | V4-BRAND-02 |
| AFR-085 | Visual token contrast MUST target WCAG 2.2 AA and MUST be verified at design review before UI implementation | 0041 | 22,10 | V4-BRAND-02 |
| AFR-086 | No final logo/brand MUST be claimed without owner approval; no misleading AI-autonomy or guaranteed-rating claim | 0041 | 22,06 | FF-DOC-03 |
| AFR-087 | Six environments MUST be defined (local, test, CI, staging, pilot, production) each with documented isolation | 0034 | 23 | V4-ENV-01 |
| AFR-088 | Raw production data MUST NOT be copied to local/test/CI/staging; synthetic data is the default | 0035 | 23,07 | V4-ENV-02 |
| AFR-089 | Each environment MUST isolate database, redis, queue, and storage; resource names MUST NOT collide across environments | 0034 | 23,03 | FF-TEN-02 |
| AFR-090 | Each environment MUST use its own secrets; no environment inherits another environment's secrets | 0037 | 24,04 | FF-SEC-01 |
| AFR-091 | Configuration MUST be classified (public/internal/secret/credential/...); secrets/credentials MUST NOT be committed | 0037 | 24,04 | FF-SEC-01 |
| AFR-092 | Promotion MUST follow local→test/CI→staging→pilot→production; there MUST NOT be a direct unreviewed pilot/production deploy | 0034 | 23,13 | V4-ENV-03 |
| AFR-093 | Step 4 CI MUST NOT add fake Laravel runtime gates; only documentation/planning CI runs until the app exists | 0026,0034 | 23,09 | FF-DOC-01 |
| AFR-094 | Local development MUST define a recommended baseline plus a fallback; no runtime bootstrap occurs in Step 4 | 0036 | 23 | V4-ENV-01 |
| AFR-095 | Dependencies MUST be researched against official sources; version-sensitive facts MUST NOT rely on memory alone | 0038 | 25 | V4-DEP-01 |
| AFR-096 | No package MUST be installed and no lock file generated in Step 4; a framework-major change requires an ADR | 0038 | 25,08 | V4-DEP-01 |
| AFR-097 | Supply chain MUST enforce official registry, typosquat prevention, lock-file review, vulnerability scan, and SBOM | 0038 | 25,04 | V4-DEP-02 |
| AFR-098 | Dependency approval MUST use the controlled vocabulary; upgrades follow the pinning + emergency-patch policy | 0038 | 25 | V4-DEP-02 |
| AFR-099 | The SaaS Foundation MUST follow the fixed implementation sequence; tenant context precedes business features | 0039 | 26,08 | V4-SF-01 |
| AFR-100 | RBAC MUST precede a privileged console; audit precedes sensitive mutation; isolation precedes external async/upload | 0039 | 26,03 | V4-SF-01 |
| AFR-101 | Observability and tested restore MUST precede pilot; runtime evidence MUST precede any deployment claim | 0039,0040 | 26,11,19 | V4-SF-02 |
| AFR-102 | The pilot/production deployment-target MUST be a dedicated isolated class; it MUST NOT share DB/redis/pool/secrets with DaengtisiaMS or Aish POS by default | 0040 | 26,11 | V4-SF-02 |
| AFR-103 | Planning MUST NOT be reported as implementation: domain candidate ≠ ownership, plan ≠ deployment, dependency approval ≠ installation | — | 27,19 | FF-DOC-03 |
| AFR-104 | The Step 4 GO tag MUST attest planning/documentation readiness only — not application implementation, deployment, pilot, or production readiness | — | 27,13,19 | FF-DOC-03 |

**104 AFRs total (72 Step 3 + 32 Step 4).** Step 4 AGENTS-instruction and rule coverage is asserted in
[Step 4 Rule Coverage](../quality/STEP_4_RULE_COVERAGE.md); evidence pointers in the
[Step 4 Traceability Matrix](../quality/STEP_4_REQUIREMENTS_TRACEABILITY_MATRIX.md). No orphan permanent decision.

## CICD-CTRL-1 — Safe CI Runtime Control (AFR-105..126)

**Status:** CI/RELEASE GOVERNANCE — CONFIGURED and evidenced. **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.5.0 §69; PRD v1.3.0 (unchanged) · **ADRs:** 0042–0046 · **Claude rule:** 28.
`CI-*` are CICD-CTRL-1 validators (`scripts/ci/*`) enforced by `pr-ci.yml`; the [CICD-CTRL-1 Validation
Catalog](../quality/CICD_CTRL_1_VALIDATION_CATALOG.md) maps each AFR to a validator and to GitHub run evidence.

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF/Gate |
|-----|-----------------------------|-----|------|---------|
| AFR-105 | A CI PASS MUST be valid only for the exact tested commit SHA; a result MUST NOT be reused after the head changes | 0042 | 28 | CI-SHA-01 |
| AFR-106 | Validation MUST be runnable locally; CI MUST NOT be the substitute for local validation | 0042 | 28 | CI-LOCAL-01 |
| AFR-107 | Feature PRs MUST open as drafts; a draft PR MUST run fast CI only | 0042 | 28 | CI-DRAFT-01 |
| AFR-108 | After review, the PR MUST be marked ready and one full release CI MUST be targeted at the final head | 0042 | 28 | CI-FULL-01 |
| AFR-109 | Any commit after a full CI MUST invalidate the old result and MUST require a new full CI | 0042 | 28 | CI-SHA-01 |
| AFR-110 | A feature branch MUST NOT run full CI separately for `push` and `pull_request` on the same SHA | 0042 | 28 | CI-TOPO-01 |
| AFR-111 | Stale runs for the same PR MUST be cancelled when a new head arrives (concurrency cancel-in-progress) | 0042 | 28 | CI-TOPO-02 |
| AFR-112 | There MUST be exactly one stable required gate (`pr-ci / Required Gate`) that always reports a conclusion | 0043 | 28 | CI-GATE-01 |
| AFR-113 | A mandatory workflow MUST NOT be skipped via a top-level path filter; change classification MUST be internal | 0043 | 28 | CI-TOPO-03 |
| AFR-114 | Unknown / mixed / unclassified changes MUST run the full safe suite (fail closed) | 0043 | 28 | CI-CLASS-01 |
| AFR-115 | Push to main MUST run lightweight integrity verification only, not the full release suite | 0044 | 28 | CI-POST-01 |
| AFR-116 | Tag creation MUST run exact-match/integrity verification only and MUST NOT run full CI | 0044 | 28 | CI-TAG-01 |
| AFR-117 | Post-tag evidence MUST NOT trigger full CI; it defaults to a GitHub Release artifact | 0044 | 28 | CI-TAG-02 |
| AFR-118 | Commit-message skip directives MUST NOT bypass mandatory release checks | 0043 | 28 | CI-SEC-01 |
| AFR-119 | Secret scan, workflow-security, tenant-isolation, and release-integrity gates MUST NOT be removed for speed | 0043 | 28,04 | CI-SEC-02 |
| AFR-120 | Third-party and official actions MUST be pinned to an immutable 40-hex commit SHA | 0045 | 28,25 | CI-SEC-03 |
| AFR-121 | Default `GITHUB_TOKEN` permission MUST be read-only; write MUST be granted only where required | 0045 | 28 | CI-SEC-04 |
| AFR-122 | `pull_request_target` MUST NOT execute untrusted PR head code with a privileged token | 0045 | 28,04 | CI-SEC-05 |
| AFR-123 | `main` SHOULD enforce a ruleset requiring the stable required gate and blocking force-push/deletion; admin bypass MUST NOT be used | 0046 | 28,13 | CI-RULE-01 |
| AFR-124 | A run-budget MUST NOT turn a failure into a success; failures MUST NOT be hidden or falsely marked flaky | 0042 | 28,09 | CI-BUD-01 |
| AFR-125 | Runtime suites (backend/frontend/database) MUST be routed but recorded NOT-YET-AVAILABLE until the application exists; no fake Laravel runtime gate | 0043 | 28,23 | CI-CLASS-02 |
| AFR-126 | CI-efficiency claims MUST be backed by actual GitHub run evidence; reruns MUST be reported truthfully (no false "one run forever" claim) | 0042 | 28,27 | CI-EVID-01 |

**126 AFRs total (72 Step 3 + 32 Step 4 + 22 CICD-CTRL-1).** CICD-CTRL-1 AGENTS-instruction and rule coverage is
asserted in the [CICD-CTRL-1 Traceability Matrix](../quality/CICD_CTRL_1_TRACEABILITY_MATRIX.md); every AFR maps to
an ADR, Claude rule 28, an AGENTS instruction, a validator, and actual GitHub run evidence. No orphan permanent decision.

## Step 5 — Runtime & Repository Bootstrap (AFR-127..133)

Runtime foundation rules. Each maps to an ADR (0047–0050), Claude rule 29, and a runtime fitness check (RT-*)
verified by `scripts/runtime/verify-runtime.sh`, the `backend-runtime-ci` job, and the test suite. Coverage is
mapped in the [Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md).

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF/Gate |
|-----|-----------------------------|-----|------|---------|
| AFR-127 | The runtime baseline (Laravel 12 / PHP 8.4 min ^8.3 / PostgreSQL 17 / Redis 7 / Node 22) MUST be pinned and identical across local, CI, and documentation | 0047 | 29,25 | RT-01 |
| AFR-128 | A clean checkout MUST bootstrap reproducibly; bootstrap MUST be idempotent, fail-fast, MUST NOT run as root, MUST NOT overwrite an existing `.env`, MUST NOT drop the database without an explicit flag, and MUST NOT print secrets | 0048 | 29,24 | RT-02 |
| AFR-129 | Health probes MUST be truthful: `/live` depends on no external dependency; `/ready` returns 503 when a mandatory dependency is down and MUST NOT leak credentials, connection strings, stack traces, queries, or internal paths | 0049 | 29,10,11 | RT-03 |
| AFR-130 | The backend runtime CI gate MUST run against real PostgreSQL + Redis, MUST be required on every ready PR, and MUST NOT be a fake/mock Laravel gate | 0050 | 29,28 | RT-04 |
| AFR-131 | Connectivity MUST be proven not assumed (`select 1`, cache round-trip, queue dispatch+processing); `APP_DEBUG` MUST NOT be true in production | 0047,0049 | 29,04 | RT-05 |
| AFR-132 | The queue/scheduler foundation MUST stay foundation-only (no business/agent jobs, no fabricated scheduled tasks); retry MUST NOT create duplicate side effects and a failed-job path MUST exist | 0048 | 29,05,02 | RT-06 |
| AFR-133 | Runtime evidence MUST precede any runtime/deployment claim; a clean-checkout verification on the exact merged SHA MUST pass before a Step 5 GO tag | 0050 | 29,13,27 | RT-07 |

**133 AFRs total (72 Step 3 + 32 Step 4 + 22 CICD-CTRL-1 + 7 Step 5).** Step 5 AGENTS-instruction and rule coverage
is asserted in the [Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md); every AFR maps to an
ADR, Claude rule 29, a runtime fitness check, and actual runtime/CI evidence. No orphan permanent decision.
