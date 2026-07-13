# ADR 0031 — Dependency and Supply-Chain Governance

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Security / DevOps Architect
- **Rule:** `.claude/rules/04`, `15`, `20` (AFR-064) · **Canonical:** Master Source v2.3.0 §43, §66.8

## Context
Supply-chain compromise is a named threat. Dependencies (Composer/npm, MCP, tooling, Codex/Graphify) must be
minimal, pinned, verified, and secret-free.

## Decision
Dependencies are **minimal and least-privilege**; versions are **pinned at implementation** after verifying
official source/provenance/license; installers are not unpinned; no dependency is added merely to look complete.
MCP servers follow an allowlist with no committed secrets; unverified similarly-named packages are not installed.
Branded Graphify and external Limit Saver are adopted only after provenance verification (OD-05, OD-06). See
[MCP Governance](../../tooling/MCP_GOVERNANCE.md) and [Dependency governance in the Threat Model](../../security/STEP_3_THREAT_MODEL.md).

## Alternatives
- **Latest/unpinned dependencies** — rejected: reproducibility + supply-chain risk.
- **Broad MCP/tooling set** — rejected: least privilege.

## Consequences
Reduced supply-chain surface; requires provenance/license checks and pinning discipline.

## Impacts
- **Security:** the core subject — verified, pinned, minimal dependencies.
- **Privacy:** no secret/PII sent to third-party tooling.
- **Tenant isolation:** tooling cannot access tenant data plane.
- **Database:** none.
- **Operational:** reproducible builds.
- **Cost:** avoids unnecessary tooling.

## Verification / fitness function
FF-SEC-01 (secret scan). Governance: dependency additions require review + provenance record.

## Related
Requirement: Master Source §43, §66.8. Application rule: AFR-064. ADRs: 0025, 0026.

## Evidence
`docs/tooling/MCP_GOVERNANCE.md`, `docs/tooling/MCP_MANIFEST.md`, `docs/evidence/step-3/inventory/tooling-inventory.txt`.

## Non-claims
No application dependency is installed or pinned in Step 3; branded Graphify/Limit Saver are not claimed as used.

## Rollback / supersession
Least-privilege + no-secret governance is permanent; superseded only by a security ADR + Master Source update.
