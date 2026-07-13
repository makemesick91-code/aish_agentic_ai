# Architecture Fitness Functions — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §50 · PRD v1.2.0 §23 · **Rules:** `.claude/rules/09`, `20` ·
**ADR:** [0030](../decisions/adr/0030-testing-strategy-and-architecture-fitness-functions.md).

Fitness functions are automated, repeatable checks that guard the architecture over time. Each has a **Step 3
check** (documentation/gate that can run **now**, since there is no application code) and an **Implementation
enforcement** (`tests/Architecture` or security/integration tests, run when code exists). Nothing here executes
against application code in Step 3. See catalog + evidence mapping in
[STEP_3_FITNESS_FUNCTION_CATALOG](../quality/STEP_3_FITNESS_FUNCTION_CATALOG.md).

## Modules
| ID | Invariant | Step 3 check | Implementation enforcement |
|----|-----------|--------------|-----------------------------|
| FF-MOD-01 | All 17 modules defined with a contract | `check-step3-coverage.sh` module presence | app boot: every module provider registered |
| FF-MOD-02 | A module writes only its own tables | data-ownership matrix (one writer/table) | `tests/Architecture` table-owner assertion |
| FF-MOD-03 | No undocumented circular dependency | dependency matrix reviewed | `tests/Architecture` dependency graph acyclic (minus documented hubs) |
| FF-MOD-04 | Shared Kernel depends on no module | dependency matrix | `tests/Architecture` `app/Shared` has no module import |
| FF-MOD-05 | Cross-module calls match allowed matrix | dependency matrix | `tests/Architecture` boundary rules |

## Tenant / branch isolation (one per surface)
| ID | Surface | Step 3 check | Implementation enforcement |
|----|---------|--------------|-----------------------------|
| FF-TEN-01 | DB queries | control matrix row | global+repository scope tests |
| FF-TEN-02 | Cache keys | control matrix row | cache-key prefix test |
| FF-TEN-03 | Queue jobs | control matrix row | job carries+rehydrates context test |
| FF-TEN-04 | File storage paths | control matrix row | storage-path scoping test |
| FF-TEN-05 | Search indexes | control matrix row | search filter test |
| FF-TEN-06 | Exports | control matrix row | export scoping test |
| FF-TEN-07 | Analytics/read-models | control matrix row | projection scoping test |
| FF-TEN-08 | AI retrieval | control matrix row | retrieval tenant-filter test |
| FF-TEN-09 | Knowledge retrieval | control matrix row | KB tenant-filter test |
| FF-TEN-10 | Notifications | control matrix row | notification scoping test |
| FF-TEN-11 | Public API | control matrix row | cross-tenant/IDOR test |
| FF-TEN-12 | Webhooks | control matrix row | webhook tenant-scope test |
| FF-TEN-13 | Audit views | control matrix row | audit tenant-scope test |
| FF-TEN-14 | Tenant-facing logs | control matrix row | log redaction/scope test |

## Data
| ID | Invariant | Step 3 check | Implementation enforcement |
|----|-----------|--------------|-----------------------------|
| FF-DATA-01 | Exactly one owner per table | data-ownership matrix | migration-owner test |
| FF-DATA-02 | `tenant_id` on tenant tables, `branch_id` on branch tables | ownership matrix columns | schema assertion test |
| FF-DATA-03 | No `MED` field modelled/stored | ownership matrix + boundary doc | schema deny-list test |

## Reliability
| ID | Invariant | Step 3 check | Implementation enforcement |
|----|-----------|--------------|-----------------------------|
| FF-REL-01 | Transactional outbox for external effects | outbox doc | outbox integration test |
| FF-REL-02 | Consumer idempotency (dedupe on event_id) | outbox doc | duplicate-delivery test |
| FF-REL-03 | Bounded retry + backoff | outbox doc | retry policy test |
| FF-REL-04 | Dead-letter + replay | outbox doc | DLQ + replay test |
| FF-REL-05 | No duplicate external action on retry | outbox doc | idempotent-effect test |
| FF-REL-06 | No success before provider verification | API/outbox doc | provider-verify test |

## Security / privacy / AI / API
| ID | Invariant | Step 3 check | Implementation enforcement |
|----|-----------|--------------|-----------------------------|
| FF-SEC-01 | No secret committed | `secret-scan.sh` | CI secret scan + push protection |
| FF-SEC-02 | OAuth tokens encrypted, refresh never plaintext | secrets doc | credential-encryption test |
| FF-SEC-03 | Public reply requires recorded human approval | approval rules doc | approval-gate test |
| FF-SEC-04 | No review gating/manipulation | reply-safety doc | anti-gating test |
| FF-SEC-05 | Customer content never steers tool calls | threat model | prompt-injection test |
| FF-AI-01 | Structured AI output (JSON Schema) | AI boundary doc | schema-validation test |
| FF-AI-02 | Redaction before AI input | AI control plane | redaction test |
| FF-AI-03 | Guardrail + human approval upstream of public action | guardrail doc | guardrail test |
| FF-AI-04 | Prompt+model version, token, cost logged | AI observability | trace/cost test |
| FF-AI-05 | Manual fallback + kill switch | AI boundary doc | fallback/kill-switch test |
| FF-API-01 | `/api/v1` versioned, deprecation controlled | API standards | route/version test |
| FF-API-02 | Idempotency-Key on unsafe ops | API standards | idempotency test |
| FF-API-03 | Tenant scope from credential, not client input | API standards | scope-spoofing test |
| FF-API-04 | Webhook signed + replay-protected | API standards | signature/replay test |

## Documentation / governance
| ID | Invariant | Step 3 check | Implementation enforcement |
|----|-----------|--------------|-----------------------------|
| FF-DOC-01 | No orphan critical requirement | `check-step3-coverage.sh` traceability | CI traceability gate |
| FF-DOC-02 | Every permanent decision maps ADR↔AFR↔AGENTS↔rule↔FF↔evidence | traceability matrix | CI mapping gate |
| FF-DOC-03 | No false implementation/deployment claim | `check-step3-coverage.sh` language check | CI truthful-status gate |

Total: **45** fitness functions (FF-MOD 5 + FF-TEN 14 + FF-DATA 3 + FF-REL 6 + FF-SEC 5 + FF-AI 5 + FF-API 4 +
FF-DOC 3). Step 3 gates assert their **documentation/coverage**; code-level enforcement is
added with the application (ADR 0030).
