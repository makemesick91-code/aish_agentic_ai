# ADR 0023 — Knowledge Base and Tenant-Scoped Retrieval

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** AI Systems Architect
- **Rule:** `.claude/rules/03`, `04`, `18`, `20` (AFR-018, AFR-046) · **Canonical:** Master Source v2.3.0 §42; PRD v1.2.0 §12

## Context
RAG/knowledge retrieval feeds AI. It must never cross tenants, never leak secrets/PII/medical data, and send
only the minimum relevant context.

## Decision
The Knowledge module owns a **tenant/branch-scoped** index. Retrieval is filtered by tenant+branch and returns
**minimum relevant** context (Master Source §42). The KB **MUST NOT** index secrets, PII, or `MED` data. Cross-
tenant retrieval is impossible by construction. See [AI Service Boundary](../../architecture/AI_SERVICE_BOUNDARY.md) §4.

## Alternatives
- **Global shared index** — rejected: cross-tenant leakage.
- **Send full documents to AI** — rejected: over-exposure; higher cost/leak risk.

## Consequences
Safe, scoped retrieval; requires per-tenant indexing and a content filter.

## Impacts
- **Security:** no secret/PII in the index; injection-resistant sourcing.
- **Privacy:** medical data excluded; minimal context.
- **Tenant isolation:** FF-TEN-08/09 (AI/knowledge retrieval).
- **Database:** knowledge_docs/chunks/retrieval_index (tenant-scoped).
- **Operational:** index build health; failure states.
- **Cost:** minimal-context retrieval reduces token cost.

## Verification / fitness function
FF-TEN-08, FF-TEN-09, FF-DATA-03. Implementation: retrieval tenant-filter + deny-list tests.

## Related
Requirement: Master Source §42; PRD §12. Application rule: AFR-018, AFR-046. ADRs: 0015, 0019.

## Evidence
`docs/architecture/AI_SERVICE_BOUNDARY.md`, `docs/architecture/MODULE_BOUNDARIES.md` (Knowledge).

## Non-claims
No knowledge base, index, or retrieval runs in Step 3.

## Rollback / supersession
Tenant-scoped, minimal, no-secret retrieval is permanent; superseded only by an AI/security ADR + Master Source update.
