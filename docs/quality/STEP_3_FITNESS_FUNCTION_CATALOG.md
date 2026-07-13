# Step 3 Fitness Function Catalog — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §50 · PRD v1.2.0 §23 · **Rules:** `.claude/rules/09`, `20` ·
**ADR:** [0030](../decisions/adr/0030-testing-strategy-and-architecture-fitness-functions.md).

Catalog + evidence mapping for the 45 fitness functions defined in
[Architecture Fitness Functions](../architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md). "Step 3 evidence" is what a
documentation gate can verify **now**; "Impl. enforcement" is added with the application (`tests/Architecture`,
`tests/Security`, `tests/Integration`).

| Group | IDs | Count | Step 3 evidence | Impl. enforcement |
|-------|-----|-------|-----------------|-------------------|
| Modules | FF-MOD-01..05 | 5 | MODULE_BOUNDARIES + DEPENDENCY_MATRIX + `check-step3-coverage.sh` | `tests/Architecture` |
| Tenant isolation | FF-TEN-01..14 | 14 | TENANT_ISOLATION_CONTROL_MATRIX rows | `tests/Security` per surface |
| Data | FF-DATA-01..03 | 3 | DATA_OWNERSHIP_MATRIX | schema/migration tests |
| Reliability | FF-REL-01..06 | 6 | OUTBOX_IDEMPOTENCY_RETRY | integration/idempotency tests |
| Security/Privacy/AI/API | FF-SEC-01..05, FF-AI-01..05, FF-API-01..04 | 14 | threat model, AI docs, API standards, `secret-scan.sh` | security/AI/API tests |
| Documentation/governance | FF-DOC-01..03 | 3 | traceability + coverage + `check-step3-coverage.sh` | CI governance gates |

**Total: 45.** Each FF ID resolves to a specific control in the referenced doc and to an AFR in
[Application Foundation Rules](../architecture/APPLICATION_FOUNDATION_RULES.md).

## Which fitness functions run in Step 3
Only **documentation/coverage** checks run in Step 3 (they need no application code):
- `check-step3-coverage.sh` — module presence, isolation-surface coverage, AFR coverage, security/AI/reliability
  tokens, truthful-status language, no-orphan traceability.
- `check-adr.sh` — ADR sequence 0001..0032, required sections, valid status, no `TBD` on fundamental Step 3 ADRs.
- `secret-scan.sh` — FF-SEC-01.
- `scripts/graphify/build.sh` + `query-smoke.sh` — derived index build + drift + query resolution.

Code-level fitness functions (FF-TEN tests, FF-REL tests, etc.) execute when the application exists — **not** in
Step 3. No application test runs in Step 3.

## Assertion
All 45 fitness functions are catalogued with Step 3 evidence and implementation enforcement. **No critical gap.**
