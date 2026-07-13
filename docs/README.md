# Documentation — Aish Agentic AI

This tree is the canonical, version-controlled product knowledge for Aish Agentic AI. It follows the
authority order in `../CLAUDE.md` §2. Derived documents **summarize and link** to the canonical Master
Source and PRD — they do not duplicate them.

## Map

| Area | Path | Contents |
|------|------|----------|
| Canonical | `canonical/` | Normalized `MASTER_SOURCE.md` (v2.1.1) + `PRD.md` (v1.0.1), `DOCUMENT_AUTHORITY.md`, and preserved `source/` originals. |
| Product | `product/` | Vision, MVP scope, roadmap, personas, open decisions. |
| Architecture | `architecture/` | System context, domain map, event catalog, ADRs. |
| Security | `security/` | Security foundation, tenant isolation, privacy/PII, prompt-injection defense, threat model. |
| AI | `ai/` | Agentic architecture, human-approval matrix, AI evaluation baseline, cost & tracing. |
| Integrations | `integrations/google/` | Google Review policy, OAuth/token security, integration readiness. |
| Quality | `quality/` | Requirements traceability, foundation coverage, test strategy, release gates. |
| Operations | `operations/` | Observability, backup/restore, incident & rollback baselines. |
| Tooling | `tooling/` | Claude project setup, MCP, MCP/skills manifest, Graphify, Limit Saver. |
| Decisions | `decisions/` | Decision log, version matrix, ADRs. |
| Status | `status/` | Current state, handoff, session checkpoints. |
| Release | `release/` | Documentation-foundation GO/NO-GO, release manifest, tag verification. |
| Evidence | `evidence/` | Source checksums, validation, Graphify, CI, and git-release evidence. |

## Traceability

Every permanent decision maps through `quality/FOUNDATION_COVERAGE_MATRIX.md`
(canonical section → rule → derived doc → validation/evidence → status) and
`quality/REQUIREMENTS_TRACEABILITY_MATRIX.md` (PRD requirement → rule/doc → validation).

## Status

Documentation & Claude Rules Foundation. **Application implementation: NOT STARTED.**
