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

**133 AFRs through Step 5 (72 Step 3 + 32 Step 4 + 22 CICD-CTRL-1 + 7 Step 5).** Step 5 AGENTS-instruction and rule
coverage is asserted in the [Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md); every AFR
maps to an ADR, Claude rule 29, a runtime fitness check, and actual runtime/CI evidence. No orphan permanent decision.

## Step 6 — SaaS Core Foundation (AFR-134..154)

SaaS core foundation rules (consolidated SPRINT-SF-01..SF-04 / EPIC-SF-04..09, ADR 0051). Each maps to an ADR
(0011, 0012, 0013, 0015, 0029, 0051, 0052, 0053), Claude rule 30, and a SaaS-core fitness check (SC-*) verified by
`tests/Feature/{Auth,Tenancy}/*`, the cross-tenant security test matrix, the `backend-runtime-ci` job, and the
consolidated Step 6 GO gate. Coverage is mapped in the
[Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md). The SaaS core is platform-core
infrastructure in top-level `app/` namespaces, not `app/Modules/` (ADR 0052); business modules remain NOT STARTED.

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF/Gate |
|-----|-----------------------------|-----|------|---------|
| AFR-134 | Secure authentication foundation MUST use Fortify (web session); Sanctum is installed and foundation-ready but MUST NOT be relied on as wired this step; authentication MUST NOT enumerate users and MUST NOT log credentials/tokens | 0013,0053 | 30,04 | SC-01 |
| AFR-135 | Public/self-service registration MUST be disabled; users MUST arrive only via secure provisioning + invitation | 0053 | 30 | SC-02 |
| AFR-136 | User identity MUST be global; users MUST NOT carry a `tenant_id` or tenant role; tenant access MUST be only via an active membership | 0053,0052 | 30,03 | SC-03 |
| AFR-137 | Tenant lifecycle MUST use explicit states (active/suspended/deletion_pending); a tenant MUST NOT be hard-deleted | 0053,0029 | 30,07 | SC-04 |
| AFR-138 | Tenant settings MUST be typed and tenant-scoped (`tenant_settings`); a setting MUST NOT leak across tenants | 0011,0053 | 30 | SC-05 |
| AFR-139 | A branch MUST belong to exactly one tenant with an active/inactive lifecycle; an inactive branch MUST NOT be selectable | 0011,0012 | 30,03 | SC-06 |
| AFR-140 | Membership MUST be an explicit `tenant_memberships` pivot with states invited/active/suspended/revoked, an `all_branches` flag, and `branch_access_grants`; the last active owner MUST NOT be removed, revoked, suspended, or demoted | 0053 | 30 | SC-07 |
| AFR-141 | Invitations MUST use a one-time hashed token (never plaintext-stored/logged), an expiry, tenant/branch+role scope, and race-safe single-use acceptance; public route keys MUST use ULIDs (anti-IDOR) | 0053,0013 | 30,04 | SC-08 |
| AFR-142 | Tenant context MUST be immutable, request/job-scoped, and fail-closed — no silent fallback to first/any tenant, cleared between requests and jobs; a switch MUST require an active membership | 0012,0053 | 30,03 | SC-09 |
| AFR-143 | A selectable branch MUST belong to the current tenant; a branch-restricted user MUST NOT access another branch's data | 0012,0015 | 30,03 | SC-10 |
| AFR-144 | RBAC MUST be tenant-scoped via Spatie `laravel-permission` (`teams = true` on `tenant_id`); platform roles MUST be separate from tenant roles | 0013 | 30 | SC-11 |
| AFR-145 | Authorization MUST be defense-in-depth (policies + service/action layer); UI hiding MUST NOT be treated as sufficient; a user MUST NOT self-escalate privileges | 0013 | 30 | SC-12 |
| AFR-146 | Every tenant-owned record MUST carry `tenant_id` (+ `branch_id` where relevant) with tenant-leading keys; relationships MUST NOT cross tenant; a cross-tenant read/write (IDOR) is a release blocker | 0011,0012 | 30,03 | SC-13 |
| AFR-147 | Cache keys MUST be tenant-namespaced and MUST NOT collide; a broad Redis flush MUST NOT be used as application behavior | 0015 | 30 | SC-14 |
| AFR-148 | Queued jobs handling tenant data MUST carry a validated tenant context envelope, cleared after each job; a retry MUST NOT switch/drop the tenant or create duplicate side effects | 0012,0015 | 30,03 | SC-15 |
| AFR-149 | Tenant files MUST use a tenant/branch path prefix (`tenants/{id}/branches/{id}/...`), MUST prevent path traversal, and MUST default to private | 0015 | 30,04 | SC-16 |
| AFR-150 | Logging MUST carry structured tenant context and MUST NOT contain PII/secrets/tokens or leak another tenant's context across worker/request | 0015,0029 | 30,04 | SC-17 |
| AFR-151 | Audit MUST be append-only (security/admin actions recorded, sanitized metadata, no `updated_at`, update/delete blocked at model layer) and MUST NOT be deletable | 0029,0053 | 30,07 | SC-18 |
| AFR-152 | A suspended tenant/user or suspended/revoked membership MUST fail closed on every surface; a revoked permission MUST NOT survive as authorization via unsafe stale cache | 0053,0013 | 30,04 | SC-19 |
| AFR-153 | The cross-tenant security test matrix (read/write/list/export/cache/queue/storage/log across tenants) MUST pass; a cross-tenant breach is a release blocker | 0011,0051 | 30,03 | SC-20 |
| AFR-154 | A clean-checkout SaaS-core verification on the exact merged SHA MUST pass before the consolidated Step 6 GO tag | 0051,0052 | 30,13,29 | SC-21 |

**154 AFRs total (72 Step 3 + 32 Step 4 + 22 CICD-CTRL-1 + 7 Step 5 + 21 Step 6).** Step 6 AGENTS-instruction and
rule coverage is asserted in the [Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md); every
Step 6 AFR maps to an ADR (0011, 0012, 0013, 0015, 0029, 0051–0053), Claude rule 30, a SaaS-core fitness check
(SC-*), and forthcoming test/CI evidence under `docs/evidence/step-6/`. No orphan permanent decision. Step 6
consolidates the coupled core sprints per ADR 0051; SPRINT-SF-05..SF-08 remain independently gated. Business/module
implementation, deployment, pilot, and production remain **NOT STARTED**.

## SPRINT-SF-05 — Notification, Subscription, and Platform Admin (AFR-155..170)

SPRINT-SF-05 platform-core rules (notification delivery, subscription/entitlement, platform operator plane). Each
maps to an ADR (0054, 0055, 0056; with supporting 0011–0016, 0029, 0051–0053), Claude rule 31, and a SaaS-core
fitness check (SC-*) verified by `tests/Feature/{Notifications,Subscriptions,Platform,Security,Audit,Console}/*`,
`tests/Architecture/Sf05BoundariesTest.php`, the cross-tenant security test matrix, the `backend-runtime-ci` job,
and the SPRINT-SF-05 GO gate. These are platform-core capabilities in top-level `app/` namespaces, not
`app/Modules/` (ADR 0052); business modules remain NOT STARTED.

| AFR | Statement (MUST / MUST NOT) | ADR | Rule | FF/Gate |
|-----|-----------------------------|-----|------|---------|
| AFR-155 | Tenant notifications MUST be tenant-scoped; the recipient's active membership MUST be verified before dispatch and a tenant MUST NOT notify another tenant's members; only in-app and email channels exist (WhatsApp/SMS/Slack/Teams/push/webhook OUT) | 0054,0052 | 31,03 | SC-22 |
| AFR-156 | Notifications MUST be enqueued only through the single dispatcher and produce exactly one delivery per recipient per channel (globally-unique dedup key); retries MUST be bounded and idempotent and MUST NOT duplicate a logical delivery or side effect | 0054,0016 | 31 | SC-23 |
| AFR-157 | Delivery state MUST be truthful (`pending/queued/sending/sent/failed/cancelled/suppressed`): `queued` is NOT `sent`, `sent` means transport-accepted not proven receipt; a failed delivery MUST carry an explicit state and a sanitized failure code | 0054 | 31,10 | SC-24 |
| AFR-158 | Preferences and quiet hours MUST be timezone-aware; critical security notifications MUST NOT be silenced by a preference; suppressed deliveries MUST be recorded truthfully; content MUST NOT contain tokens/secrets/PII/medical | 0054 | 31,04 | SC-25 |
| AFR-159 | The in-app inbox MUST be scoped to the current tenant and acting recipient; mark-as-read MUST re-verify ownership (no recipient-swap / delivery IDOR) | 0054,0012 | 31,03 | SC-26 |
| AFR-160 | Plans MUST be a versioned `(code, version)` catalog with `draft/active/retired`; a plan version MUST NOT silently change historical meaning; a retired plan MUST NOT be newly assigned but existing references MUST stay valid | 0055 | 31 | SC-27 |
| AFR-161 | Entitlement keys MUST be typed and allowlisted; unknown/missing/expired MUST fail closed; all entitlement decisions MUST use the single authoritative resolver (no duplicated plan logic) | 0055 | 31,04 | SC-28 |
| AFR-162 | Tenant subscription state (`trialing/active/grace/suspended/cancelled/expired`) MUST use a guarded state machine; an invalid transition MUST be rejected | 0055 | 31 | SC-29 |
| AFR-163 | Usage records MUST be tenant-scoped and idempotent (a repeated increment MUST NOT double-count); a negative quantity MUST be refused outside an explicit correction; period boundaries MUST be timezone-aware | 0055,0015 | 31,03 | SC-30 |
| AFR-164 | Reconciliation MUST be idempotent and safe to rerun, emitting a transition/notification at most once; commercial state and payment state are NOT equivalent — no paid/collected state MUST be claimed without provider evidence (payment/invoicing/tax/dunning OUT) | 0055 | 31 | SC-31 |
| AFR-165 | A commercial restriction MUST be distinct from a security suspension; security suspension ALWAYS takes precedence and a commercial state MUST NOT override a tenant/user/membership security state | 0055,0053 | 31,04 | SC-32 |
| AFR-166 | Platform roles and tenant roles MUST be separate (a platform role grants no tenant-data access and a tenant role grants no platform access); there MUST NOT be a `Gate::before` bypass or universal hidden tenant bypass | 0056,0013 | 31,03 | SC-33 |
| AFR-167 | Every platform mutation MUST be authorized by a specific platform permission (least privilege) and MUST be audited; self-escalation MUST be prohibited; only a Super Admin MAY grant Super Admin; the last Platform Super Admin MUST NOT be removed, revoked, or demoted | 0056 | 31 | SC-34 |
| AFR-168 | Platform operator provisioning MUST be secure (reset/invitation onboarding, no fixed/plaintext password, no logged password, duplicate-safe); tenant status changes (suspend/reactivate/mark-deletion-pending) MUST require a reason (except reactivation), be audited, notify owners, and MUST NOT hard-delete; metrics MUST be truthful; impersonation MUST be prohibited without a dedicated ADR | 0056 | 31,04 | SC-35 |
| AFR-169 | Subscription events and platform support notes MUST be append-only (no `updated_at`; update/delete blocked at the model layer); audit metadata MUST be sanitized (no secrets/tokens/passwords/message bodies/medical) and MUST distinguish platform from tenant context | 0054,0055,0056,0029 | 31,07 | SC-36 |
| AFR-170 | A clean-checkout SPRINT-SF-05 verification (`scripts/runtime/verify-sf-05.sh`) on the exact merged SHA MUST pass before the SPRINT-SF-05 GO tag | 0051,0054 | 31,13,29 | SC-37 |
| AFR-171 | Every survey MUST be tenant-owned; a branch-owned survey MUST NOT be used by another branch; platform roles MUST NOT imply survey access; a published survey MUST NOT be hard-deleted | 0057,0052 | 32,03,30 | SV-01 |
| AFR-172 | Published survey versions MUST be immutable (question text/order/type, options, required, scoring, locale, mode); editing published content MUST create a new draft version and MUST NOT mutate the published one | 0057 | 32 | SV-02 |
| AFR-173 | Publishing MUST be transactional and race-safe (no two current versions) and validated (≥1 question, unique order/key, choice ≥2 options, valid CSAT/NPS/CES scales); it MUST be idempotent | 0057 | 32 | SV-03 |
| AFR-174 | Historical responses MUST always resolve to the exact answered survey version; a completed response MUST be immutable except an authorized reasoned audited invalidation; answers are write-once | 0057 | 32,07 | SV-04 |
| AFR-175 | Question type and answer type MUST match; options MUST belong to their question; an answer MUST NOT reference a question/option from another version (enforced in DB where practical) | 0057 | 32 | SV-05 |
| AFR-176 | Public survey routes MUST use opaque validated access and MUST NOT expose draft/preview content; preview MUST require authoring authorization | 0058 | 32,03 | SV-06 |
| AFR-177 | Invitation secrets MUST be strong tokens stored only as a one-way hash (SHA-256+), constant-time compared, one-time, expiring, and revocable; the plaintext MUST NOT be persisted or appear in logs, audit, session, delivery records, or error output | 0058,0004 | 32,04 | SV-07 |
| AFR-178 | Public resolution failures MUST be generic (no enumeration oracle); the public plane MUST NOT gain tenant application access, RBAC, or platform access; the cross-tenant scope bypass MUST be confined to the single reviewed public gateway | 0058 | 32,03,30 | SV-08 |
| AFR-179 | Public submission MUST be server-validated, rate-limited per token and per IP, payload-bounded, transactional, and idempotent; a unique invitation MUST complete at most once (concurrent duplicates yield one completion) | 0058 | 32,04 | SV-09 |
| AFR-180 | A QR code MUST contain only the protected public URL (no customer data, tenant secret, unprotected id, or health info), MUST be deterministic, and MUST require no external service | 0058 | 32,04 | SV-10 |
| AFR-181 | CSAT/NPS/CES MUST be deterministic, versioned, and computed only through the single MetricCalculator over stored raw answers — never from UI labels and never re-implemented in a controller/view/query | 0059 | 32,10 | SV-11 |
| AFR-182 | Raw counts MUST be retained; rounding MUST be explicit (2 decimals at the boundary); an empty population MUST return a truthful null, not a fabricated zero | 0059 | 32,10,27 | SV-12 |
| AFR-183 | Survey metrics MUST be tenant/branch/version scoped with no cross-tenant aggregation and no answer content in summaries | 0059,0057 | 32,03 | SV-13 |
| AFR-184 | Consent MUST use explicit text, store an accepted/rejected boolean with the consent-text version, MUST NOT default to accepted or pre-check; survey completion MUST NOT be marketing consent; anonymous responses MUST NOT silently create a customer identity | 0057,0058 | 32,04,18 | SV-14 |
| AFR-185 | Survey entitlement decisions MUST use the single authoritative resolver via one guard; unknown/ungranted keys fail closed; usage meters (`survey_invitations.created`, `survey_responses.completed`) MUST be tenant-scoped and idempotent (no metering on preview/failed submission; no double-count on retry) | 0055,0058 | 32,31 | SV-15 |
| AFR-186 | Survey invitation mail MUST go through a reviewed mail adapter; internal survey notifications MUST use the approved SF-05 dispatcher; a retry MUST NOT create a duplicate logical invitation; a survey score MUST NEVER gate Google Review access (anti-gating preserved) | 0054,0058 | 32,06,18,31 | SV-16 |
| AFR-187 | A clean-checkout Step 7 verification (`scripts/runtime/verify-step-7.sh` / `php artisan aish:verify-step-7`) on the exact merged SHA MUST pass before the Step 7 GO tag | 0057,0058,0059 | 32,13,29 | SV-17 |
| AFR-188 | A feedback item MUST be created from a source event exactly once; projection MUST be idempotent via a DB unique constraint `(tenant_id, source_type, source_id)` so a replay/retry/duplicate event resolves to the same item with no duplicate side effect; projection MUST run off the source write path via an after-commit event + queued listener | 0060,0016 | 33,03 | FB-01 |
| AFR-189 | Projection reconciliation (`aish:feedback-reconcile`) MUST be idempotent and safe to rerun, back-filling missing items without creating a duplicate, and MUST NOT be a second uncontrolled write path | 0060 | 33 | FB-02 |
| AFR-190 | Every feedback-owned record MUST carry `tenant_id` (and `branch_id` where the source is branch-scoped); a branch-restricted user MUST see only their branch's feedback; platform roles MUST NOT imply feedback access; cross-tenant/cross-branch feedback access is a release blocker | 0060,0012,0011 | 33,03,30 | FB-03 |
| AFR-191 | Lifecycle state MUST be explicit (`new → triaged → assigned → in_progress → resolved → closed → archived`) and changed only through a guarded transition (invalid transitions rejected); `resolved`/`closed` are operational feedback states only and MUST NOT be presented as a customer-recovery/refund/compensation outcome | 0060 | 33 | FB-04 |
| AFR-192 | Assignment MUST target only a member with an active tenant membership whose branch scope includes the item's branch; a branch-restricted item MUST NOT be assigned outside its branch; assignment changes MUST append to the timeline/history | 0061,0013 | 33,03 | FB-05 |
| AFR-193 | A member whose tenant membership is revoked or suspended MUST fail closed — MUST NOT act on or be newly assigned a feedback item (effective access re-validated, not cached stale) | 0061,0053 | 33,03,30 | FB-06 |
| AFR-194 | Manual tags MUST be tenant-owned; a tag from one tenant MUST NOT be applied to another tenant's item | 0061,0012 | 33,03 | FB-07 |
| AFR-195 | Internal notes MUST be append-only (no edit/delete at the model layer), tenant/branch-scoped; note free text is untrusted (escaped on output, never logged, not AI-fed in Step 8) | 0061,0029 | 33,07 | FB-08 |
| AFR-196 | The feedback timeline (`FeedbackEvent`) MUST be append-only (no `updated_at`; update/delete blocked at the model layer) and MUST NOT be deletable; it records every lifecycle/assignment/tag/note/attachment change with actor + tenant | 0061,0029 | 33,07 | FB-09 |
| AFR-197 | Feedback attachments MUST be stored on a private tenant-prefixed disk (`tenants/{id}/feedback/{item_id}/...`); there MUST NOT be a public disk or public listing; user-supplied names MUST NOT be used as a path segment (path traversal prevented); removal MUST be a recorded remove-state, not a silent hard delete | 0062,0004 | 33,04 | FB-10 |
| AFR-198 | Attachment MIME MUST be validated by content inspection against an allowlist (not by extension or the client `Content-Type`); a disallowed type MUST be refused | 0062,0004 | 33,04 | FB-11 |
| AFR-199 | Metadata search MAY be available to any feedback list-viewer; content search over free text MUST be gated by the `feedback.view-content` permission and MUST be excluded from the query (never returned, never a match source) for users without it | 0061,0013 | 33,03,04 | FB-12 |
| AFR-200 | Search indexing MUST stay inside the tenant boundary (native PostgreSQL FTS `tsvector`/GIN with a portable `LIKE` fallback); it MUST NOT introduce an unscoped external index that could leak across tenants | 0061 | 33,03 | FB-13 |
| AFR-201 | Bulk operations MUST be bounded (a hard item cap), MUST re-authorize the specific per-action permission for every item, MUST stay within tenant/branch scope, and MUST record each change on the timeline | 0061 | 33,03 | FB-14 |
| AFR-202 | Export MUST be a queued job writing to a private, expiring location; it MUST be entitlement-gated via the single authoritative resolver and metered as tenant-scoped idempotent usage (a retry MUST NOT double-count) | 0062,0055 | 33,04,31 | FB-15 |
| AFR-203 | Every exported cell beginning with `=`, `+`, `-`, `@`, tab, or CR MUST be neutralized against CSV formula-injection; delimiters/quotes MUST be escaped; export fields MUST be minimized (no secret/token/unrelated-tenant data) | 0062,0004 | 33,04 | FB-16 |
| AFR-204 | The export download MUST re-authorize the requesting user (ownership of the export record) and re-check tenant/branch/content scope; another user MUST NOT fetch someone else's export and a download link MUST NOT be public | 0062,0004 | 33,03,04 | FB-17 |
| AFR-205 | Base feedback access MUST be entitlement-gated (`EnsureFeedbackEnabled`); an unknown/ungranted key MUST fail closed; a commercial state MUST NOT override a security suspension | 0055,0060 | 33,31 | FB-18 |
| AFR-206 | Feedback usage meters MUST be tenant-scoped and idempotent; a failed or duplicate operation MUST NOT double-count | 0055,0015 | 33,07,31 | FB-19 |
| AFR-207 | Internal feedback notifications MUST use the approved SF-05 dispatcher (membership-verified recipients, truthful delivery states); a retry MUST NOT create a duplicate logical notification; a feedback state/score MUST NEVER gate Google Review access (anti-gating preserved) | 0054,0060 | 33,06,18,31 | FB-20 |
| AFR-208 | Feedback audit metadata MUST be sanitized and MUST NOT contain tokens, secrets, passwords, or free-text answer/customer/medical content; audit MUST be append-only and carry actor + tenant | 0060,0029 | 33,07 | FB-21 |
| AFR-209 | An independent security review (reviewer ≠ implementer) of the Step 8 feedback surface MUST be completed with no unresolved CRITICAL/HIGH/MEDIUM before the Step 8 GO tag | 0060,0061,0062 | 33,04 | FB-22 |
| AFR-210 | A clean-checkout Step 8 verification (`scripts/runtime/verify-step-8.sh` / `php artisan aish:verify-step-8`) on the exact merged SHA MUST pass before the Step 8 GO tag | 0060,0061,0062,0013,0029 | 33,13,29 | FB-23 |
| AFR-211 | The Experience OS domain map MUST lock a single source of truth per capability; exactly one domain owns each aggregate's write path and no domain writes another domain's tables (cross-domain work uses a contract, service, or typed versioned domain event) | 0063,0010 | 34,03,20 | EOS-01 |
| AFR-212 | Customer identity is owned ONLY by Customer Profile & Identity Resolution; Survey/Feedback/Recovery/Review/Conversation MUST reference a customer id and MUST NOT create, merge, or mutate customer identity; feedback item lifecycle is owned only by Feedback Operations | 0063,0064,0060 | 34,03,33 | EOS-02 |
| AFR-213 | The Step 8 immutable Feedback Timeline MUST be preserved as authoritative for feedback-item history; the wider Experience Event Ledger MUST be additive and MUST NOT replace or destructively migrate it | 0063,0065 | 34,33 | EOS-03 |
| AFR-214 | Every cross-boundary interaction MUST carry tenant/branch context and MUST cross only references and minimized non-sensitive attributes — never raw free text, MED data, secrets, or tokens | 0063,0011 | 34,03,04,18 | EOS-04 |
| AFR-215 | The `Customer` aggregate MUST be tenant-scoped (ULID public id; branch = provenance only); cross-tenant identity linking is PROHIBITED | 0064,0012 | 34,03 | EOS-05 |
| AFR-216 | Deterministic identity links (exact normalized verified email/phone) MAY be auto-applied; probabilistic matches MUST be suggestions that are NEVER auto-applied and require human approval; provenance + confidence MUST be recorded | 0064 | 34,03 | EOS-06 |
| AFR-217 | Customer merge/split MUST be human-approved, reversible, and immutably audited (`customer_merge_events` + append-only audit); NO silent destructive merge is permitted | 0064,0029 | 34,07 | EOS-07 |
| AFR-218 | Anonymous responses MUST NOT silently create a customer identity; an IP is not an identity; consent history MUST be versioned and append-only; survey completion is NOT marketing consent | 0064,0058 | 34,32,18 | EOS-08 |
| AFR-219 | Customer erasure MUST tombstone + purge PII while retaining minimized non-PII counts; retention MUST be configurable and legal hold MUST override purge; Step 8 backfill MUST be additive/idempotent/resumable/non-destructive | 0064,0068 | 34,07 | EOS-09 |
| AFR-220 | Experience Event Ledger events MUST carry ULID event identity, mandatory tenant/branch context, subject + sanitized actor, source domain, and a typed versioned event type with distinct occurred_at vs recorded_at | 0065 | 34,03,07 | EOS-10 |
| AFR-221 | Ledger delivery MUST be idempotent (per-producer idempotency key); ordering MUST be per-subject monotonic on recorded_at with NO global total order; consumers MUST tolerate out-of-order occurred_at | 0065,0016 | 34,03 | EOS-11 |
| AFR-222 | Ledger payloads MUST be minimized with PII/MED classification and redaction (no raw free-text answer content, no medical data, no secrets/tokens); events MUST be append-only (no update/delete at the model layer) | 0065,0060 | 34,04,07,18 | EOS-12 |
| AFR-223 | Ledger projections MUST be idempotent and rebuildable; a DLQ + idempotent reconcile MUST exist; backfilled rows MUST be marked distinctly | 0065,0068 | 34,03 | EOS-13 |
| AFR-224 | Ledger schema evolution MUST be additive and versioned (forward/backward tolerant, no destructive rewrite); queries/indexes MUST be tenant-scoped with no cross-tenant aggregation | 0065 | 34,03 | EOS-14 |
| AFR-225 | Channel connections + credentials MUST be tenant-owned and encrypted at rest (refresh tokens never plaintext; rotation + revocation); no environment inherits another's secrets; conversations MUST use a provider-independent model | 0066,0024 | 34,04,24 | EOS-15 |
| AFR-226 | Provider webhooks MUST be signature-verified per tenant with replay protection; inbound MUST dedupe by (tenant_id, provider, provider_message_id) and outbound MUST carry an idempotency key | 0066 | 34,04 | EOS-16 |
| AFR-227 | Channel delivery states MUST be truthful (queued/sent/delivered/read/failed/moderation/unknown); provider acknowledgement is NOT proven end-user receipt; a mock MUST NOT be claimed as integration success | 0066,0054 | 34,10,18 | EOS-17 |
| AFR-228 | Channel adapters MUST use bounded retry + DLQ + reconciliation, per-provider/per-tenant rate limits, private content-MIME-validated attachments, per-message cost metering, and tenant/branch routing (never cross-tenant) | 0066,0062 | 34,03,04 | EOS-18 |
| AFR-229 | A per-provider circuit breaker MUST fall back to manual; a single provider's failure MUST NOT break Feedback Operations or Customer Recovery; channel routing MUST NEVER gate Google Review access (anti-gating preserved) | 0066 | 34,06,17,18 | EOS-19 |
| AFR-230 | AI tools MUST be an explicit allowlisted registry; each tool MUST be gated by a specific permission and carry validated tenant/branch context; customer content MUST NOT steer tool calls; structured output MUST be enforced | 0067,0019 | 34,04,05 | EOS-20 |
| AFR-231 | High-risk actions (Master Source §33 / PRD §13 triggers) MUST require human approval regardless of confidence; forbidden actions (public reply, refund/compensation, data deletion, legal statements, admission of fault) MUST NEVER be auto-executed | 0067 | 34,05,06,18 | EOS-21 |
| AFR-232 | AI runs MUST enforce per-tenant/agent/run cost ceilings + token/outcome metering, bounded timeout/retry with duplicate-action prevention (idempotency keys), and correlation/trace projected to the ledger | 0067,0016 | 34,05,07 | EOS-22 |
| AFR-233 | AI MUST apply input/output guardrails + prompt-injection defense + PII/MED redaction (MED never sent to a provider or into public output) and a global/per-tenant/per-agent kill switch; manual workflows MUST remain operable when AI is unavailable | 0067,0028 | 34,04,05,18 | EOS-23 |
| AFR-234 | AI capabilities MUST pass an adversarial evaluation dataset + quality gates (no PII leakage, valid structured output, active approval, cost limit, kill switch, idempotent retry) before release; autonomy MUST follow manual → semi-automated → approved automation → limited autonomy | 0067 | 34,05,09 | EOS-24 |
| AFR-235 | Migrations MUST be additive-only by default with no migration-history reset; existing Step 8 records MUST remain valid and unaltered; there MUST be no destructive production backfill | 0068,0048 | 34,23,29 | EOS-25 |
| AFR-236 | Backfills MUST be idempotent, queued, chunked, and resumable with progress + failure visibility + DLQ; an idempotent `aish:*-reconcile` MUST exist as a controlled second read path (not an uncontrolled write path) | 0068,0060 | 34,03,26 | EOS-26 |
| AFR-237 | New capabilities MUST be per-tenant feature-flagged (default off) and enabled only after backfill + verification; deployment ordering MUST be migrate → tolerant deploy → backfill → verify → flag with mixed-version tolerance | 0068 | 34,26 | EOS-27 |
| AFR-238 | A tested restore MUST precede any pilot/production backfill and a backup MUST precede a large backfill; every backfill MUST be tenant-scoped with a post-backfill isolation check and a stated performance budget | 0068,0049 | 34,11,26 | EOS-28 |

**238 AFRs total (72 Step 3 + 32 Step 4 + 22 CICD-CTRL-1 + 7 Step 5 + 21 Step 6 + 16 SPRINT-SF-05 + 17 Step 7 + 23 Step 8 + 28 Step 9).**

Step 9 (Competitive Gap Audit & Architecture Re-baseline) AFR-211..AFR-238 each map to an ADR (0063–0068), Claude
rule 34, an Experience-OS fitness check (EOS-01..EOS-28), and design/evidence artifacts under
`docs/architecture/experience-os/`, `docs/product/`, `docs/security/STEP_9_THREAT_MODEL.md`, and `docs/evidence/step-9/`.
No orphan permanent decision. Step 9 is architecture/governance LOCK only: it locks domain boundaries, the Customer 360
identity model, the Experience Event Ledger (preserving the Step 8 timeline), the channel-adapter and AI-tool-permission
contracts, and the additive migration strategy. Customer 360, recovery, Google Review, AI, omnichannel, knowledge base,
analytics, public API, payment, deployment, pilot, and production remain **NOT STARTED**.
Step 7 (Survey & CSAT Foundation) AFR-171..AFR-187 each map to an ADR (0057–0059 with supporting 0011–0016, 0029,
0051–0053), Claude rule 32, a survey fitness check (SV-01..SV-17), and test/CI evidence under `docs/evidence/step-7/`.
No orphan permanent decision. Step 7 delivers the first customer-experience capability on the SaaS core + SF-05
substrate; feedback/AI/recovery/Google/billing modules and deployment/pilot/production remain **NOT STARTED**.

Step 8 (Feedback Operations Foundation) AFR-188..AFR-210 each map to an ADR (0060–0062 with supporting 0011–0016,
0029, 0051–0057), Claude rule 33, a feedback fitness check (FB-01..FB-23), and test/CI evidence under
`docs/evidence/step-8/`. No orphan permanent decision. Step 8 turns completed survey responses into an operable,
tenant/branch-safe Feedback Inbox (idempotent projection, explicit lifecycle, scope-validated assignment, tenant-
isolated tags, append-only notes/timeline, private content-MIME-validated attachments, permission-aware search,
bounded bulk operations, and queued requester-scoped CSV export) with entitlement/usage, SF-05 notification, and
sanitized audit; AI/recovery/SLA/Google/agent/RAG, deployment, pilot, and production remain **NOT STARTED**.

SPRINT-SF-05
AGENTS-instruction and rule coverage is asserted in the
[Foundation Coverage Matrix](../governance/foundation-coverage-matrix.md); every SPRINT-SF-05 AFR maps to an ADR
(0054–0056 with supporting 0011–0016, 0029, 0051–0053), Claude rule 31, a SaaS-core fitness check (SC-22..SC-37),
and forthcoming test/CI evidence under `docs/evidence/sprint-sf-05/`. No orphan permanent decision. SPRINT-SF-05
delivers three platform-core skeletons on the Step 6 SaaS core; SPRINT-SF-06..SF-08 remain independently gated.
Payment, AI, and Google integrations, business/module implementation, deployment, pilot, and production remain
**NOT STARTED**.
