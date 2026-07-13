# ADR 0020 — AI Service Extraction Criteria

- **Status:** Accepted (2026-07-13, Asia/Makassar) — criteria only; **no extraction in Step 3**
- **Owner:** AI Systems Architect
- **Rule:** `.claude/rules/08`, `20` (AFR-042) · **Canonical:** Master Source v2.3.0 §34

## Context
The MVP calls AI from Laravel (ADR 0019). A separate orchestrator (e.g. Python/FastAPI) may become justified, but
only with evidence — premature extraction adds cost and operational surface.

## Decision
Record explicit **extraction criteria**. A separate AI orchestrator service is split out **only** when there is
evidence of at least one: growing multi-agent complexity; increasing tool calling; multiple providers; distinct
AI scaling needs; tracing requiring a separate service; latency/concurrency needs; or a security-boundary need.
Extraction requires a **new ADR** capturing the triggering evidence. See
[AI Service Boundary](../../architecture/AI_SERVICE_BOUNDARY.md) §6.

## Alternatives
- **Extract now** — rejected: no evidence; violates reliability-before-complexity.
- **Never extract** — rejected: forecloses a legitimate future need.

## Consequences
Clear, evidence-based trigger prevents both premature and blocked extraction.

## Impacts
- **Security:** extraction later can add a stronger AI boundary if needed.
- **Privacy:** redaction/guardrails stay mandatory pre- and post-extraction.
- **Tenant isolation:** any extracted service must preserve tenant scoping.
- **Database:** unchanged now.
- **Operational:** avoids premature multi-service ops.
- **Cost:** avoids unjustified infra spend.

## Verification / fitness function
Governance check: extraction PR must cite an ADR referencing this one and the triggering evidence.

## Related
Requirement: Master Source §34. Application rule: AFR-042. ADRs: 0009, 0019.

## Evidence
`docs/architecture/AI_SERVICE_BOUNDARY.md` §6; tracked as OD-03 in [Open Decisions](../../architecture/ARCHITECTURE_OPEN_DECISIONS.md).

## Non-claims
No AI service is extracted, built, or deployed in Step 3.

## Rollback / supersession
Criteria may be tightened/loosened via a new ADR + Master Source update.
