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

**72 AFRs.** AGENTS-instruction coverage is asserted in [Rule Coverage](../quality/STEP_3_ARCHITECTURE_RULE_COVERAGE.md);
evidence pointers in the [Traceability Matrix](../quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md). No orphan
permanent decision.
