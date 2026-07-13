# ADR 0003 — Graphify as Derived Knowledge Graph, Not Source of Truth

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/15` · **Canonical:** Master Source §66.3, §66.7

## Context
The owner requested Graphify for cross-session retrieval. No Graphify skill/CLI/plugin/MCP is installed and
no verified/pinned source exists to install from; installing unknown packages is prohibited.

## Decision
Treat Graphify strictly as a **derived** index that never overrides canonical documents. Since the branded
product is unavailable, realize the required role with a deterministic, reproducible documentation index
(`graphify.yaml`, `scripts/graphify/`) and record the branded product as `BLOCKED-OPTIONAL`. Do not claim
the branded product ran.

## Alternatives considered
- Install an unverified Graphify build — rejected: violates the no-unknown-installer policy (`.claude/rules/15`).
- Skip knowledge-graph entirely — rejected: owner explicitly requested the capability; a deterministic index satisfies the role.
- Block the whole release on Graphify — rejected: all mandatory gates pass; only the branded tool is optional/absent.

## Consequences
Query-smoke works deterministically today; a real Graphify MCP can replace the stub later by flipping the
documented status. The graph is never authoritative.

## Security impact
Index excludes `.env`/secrets/PII/dumps; no secret is indexed or committed.

## Migration impact
Installing real Graphify later requires recording source/version/license/checksum and re-running smoke tests.

## Supersession
Superseded when a trusted Graphify integration is installed and documented, via a Master Source update.
