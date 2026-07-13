# Step 3 Architecture Traceability Matrix — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0; PRD v1.2.0 · **Rules:** `.claude/rules/09`, `12`, `20`.

Computed spine **Canonical requirement → Architecture doc → ADR → AFR → AGENTS/Claude rule → Fitness function →
Evidence**. Every fundamental Step 3 decision is mapped; there are no critical orphans.

| Requirement (canonical) | Architecture doc | ADR | AFR | Rule | FF | Evidence |
|-------------------------|------------------|-----|-----|------|----|----------|
| Modular monolith (MS §34) | APPLICATION_ARCHITECTURE_BASELINE | 0009 | AFR-003 | 08,20 | FF-MOD-01 | evidence/step-3/architecture |
| Repo layout + boundaries (MS §34,§36) | REPOSITORY_LAYOUT, MODULE_BOUNDARIES | 0010 | AFR-004,005 | 08,20 | FF-MOD-02..05 | MODULE_DEPENDENCY_MATRIX |
| Shared-schema tenancy (MS §17,§36) | TENANCY_ARCHITECTURE | 0011 | AFR-006,007 | 03,20 | FF-TEN-01 | TENANT_ISOLATION_CONTROL_MATRIX |
| Context propagation (MS §17,§35) | TENANCY_ARCHITECTURE | 0012 | AFR-008 | 03,20 | FF-TEN-03 | TENANT_ISOLATION_CONTROL_MATRIX |
| AuthN/AuthZ (MS §34,§43) | IDENTITY_AND_ACCESS_ARCHITECTURE | 0013 | AFR-021,022 | 04,16,20 | FF-TEN-11 | IDENTITY_AND_ACCESS_ARCHITECTURE |
| DB ownership/migration (MS §36,§37) | DATABASE_ARCHITECTURE, DATA_OWNERSHIP_MATRIX | 0014 | AFR-009,010 | 07,20 | FF-DATA-01 | DATA_OWNERSHIP_MATRIX |
| Surface isolation (MS §17,§37,§42) | TENANCY_ARCHITECTURE | 0015 | AFR-011..020 | 03,07,20 | FF-TEN-01..14 | TENANT_ISOLATION_CONTROL_MATRIX |
| Events/outbox/idempotency (MS §35,§39) | EVENT_DRIVEN_ARCHITECTURE, OUTBOX_IDEMPOTENCY_RETRY | 0016 | AFR-031..036 | 08,10,20 | FF-REL-01..06 | OUTBOX_IDEMPOTENCY_RETRY |
| API/webhook (MS §39) | API_AND_WEBHOOK_STANDARDS | 0017 | AFR-037..040 | 03,04,08,20 | FF-API-01..04 | API_AND_WEBHOOK_STANDARDS |
| Frontend (MS §52,§53) | FRONTEND_ARCHITECTURE | 0018 | AFR-047 | 10,20 | FF-DOC-03 | FRONTEND_ARCHITECTURE |
| AI provider abstraction (MS §23-§34,§44) | AI_SERVICE_BOUNDARY, AI_RUNTIME_CONTROL_PLANE | 0019 | AFR-041,043,044,045,049,050 | 04,05,18,20 | FF-AI-01..05 | AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE |
| AI extraction criteria (MS §34) | AI_SERVICE_BOUNDARY | 0020 | AFR-042 | 08,20 | FF-MOD-01 | ARCHITECTURE_OPEN_DECISIONS (OD-03) |
| Google boundary (MS §16,§38) | GOOGLE_BUSINESS_PROFILE_ARCHITECTURE | 0021 | AFR-051,052,028 | 06,18,20 | FF-SEC-04 | GOOGLE_REVIEW_POLICY |
| OAuth/credential encryption (MS §43) | SECRETS_AND_CREDENTIALS_ARCHITECTURE | 0022 | AFR-024,025 | 04,20 | FF-SEC-02 | secret-scan.log |
| Knowledge/RAG (MS §42) | AI_SERVICE_BOUNDARY | 0023 | AFR-018,046 | 03,04,18,20 | FF-TEN-08,09 | MODULE_BOUNDARIES |
| Observability/audit (MS §51) | OBSERVABILITY_ARCHITECTURE | 0024 | AFR-057..060 | 07,11,20 | FF-TEN-14 | OBSERVABILITY_AND_ALERTING_BASELINE |
| Env/secret mgmt (MS §43) | ENVIRONMENT_STRATEGY | 0025 | AFR-023,053 | 04,11,20 | FF-SEC-01 | secret-scan.log |
| CI/CD (MS §54,§66.10) | ENVIRONMENT_STRATEGY | 0026 | AFR-054,072 | 09,13,20 | FF-DOC-01 | evidence/step-3/ci |
| Backup/restore/DR (MS §51,§54) | BACKUP_RESTORE_ROLLBACK | 0027 | AFR-055,056 | 11,20 | FF-DOC-03 | BACKUP_RESTORE_DR_BASELINE |
| Flags/approval/kill switch (MS §16,§33) | AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE | 0028 | AFR-027..030 | 05,06,18,20 | FF-SEC-03 | HUMAN_APPROVAL_MATRIX |
| Data classification/retention (MS §36,§37) | DATA_CLASSIFICATION_AND_HANDLING | 0029 | AFR-026,048,061 | 07,18,20 | FF-DATA-03 | DATA_CLASSIFICATION_AND_HANDLING |
| Testing/fitness (MS §50,§59) | ARCHITECTURE_FITNESS_FUNCTIONS | 0030 | AFR-062,063 | 09,20 | FF-DOC-01 | STEP_3_FITNESS_FUNCTION_CATALOG |
| Dependency/supply-chain (MS §43,§66.8) | MCP_GOVERNANCE | 0031 | AFR-064,070 | 04,15,20 | FF-SEC-01 | tooling-inventory.txt |
| Deployment topology (MS §34,§43,§51) | DEPLOYMENT_TOPOLOGY | 0032 | AFR-054,071 | 11,20 | FF-DOC-03 | ENVIRONMENT_AND_DEPLOYMENT_BASELINE |
| Source authority/identity (MS §1,§66) | (governance) | — | AFR-001,002 | 00,01,16 | FF-DOC-02 | DOCUMENT_AUTHORITY |
| Living source/versioning (MS §3-§6) | (governance) | — | AFR-065 | 12 | FF-DOC-02 | VERSION_MATRIX |
| Evidence/truthful status (MS §59,§66.11) | (governance) | — | AFR-066,067,068 | 09,10,13,19 | FF-DOC-03 | STEP_3_ARCHITECTURE_GO_NO_GO |
| Codex/Claude/AGENTS sync (MS §66.9) | AGENTS.md chain | — | AFR-069 | 15 | FF-DOC-02 | evidence/step-3/codex |

## Assertion
- Every AFR-001..AFR-072 appears above with an ADR (or governance origin), rule, FF, and evidence.
- Every ADR 0009..0032 is mapped to ≥1 AFR and ≥1 FF.
- **Orphan critical requirements: none.**
