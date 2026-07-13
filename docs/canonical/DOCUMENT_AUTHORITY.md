# Document Authority — Aish Agentic AI

Canonical rule: `.claude/rules/00-document-authority.md`. Canonical source: Master Source §1, §66.3.

## Authority order (highest first)

1. **Latest explicit product-owner decision.**
2. **Highest-version canonical Master Source** — `MASTER_SOURCE.md` (active **v2.5.0**).
3. **Newest approved PRD** — `PRD.md` (**v1.3.0**).
4. **Approved ADRs and decision-log entries** — `../decisions/adr/`, `../architecture/adr/`, `../decisions/DECISION_LOG.md`.
5. **Other repository documentation.**
6. **Generated or derived artifacts.**
7. **Knowledge-graph (Graphify) indexes and summaries** — derived, **never authoritative**.

When the PRD conflicts with the Master Source, the **Master Source wins** (item 2 > item 3). When a newer
owner decision conflicts with any document, the **owner decision wins** and MUST be recorded as a versioned
Master Source update (`.claude/rules/12`) — never applied silently.

## Canonical sources and preservation

| Document | Version | Working copy | Preserved original | SHA-256 |
|----------|---------|--------------|--------------------|---------|
| Master Source | 2.1.1 | `MASTER_SOURCE.md` | `source/MASTER SOURCE — Aish Agentic AI — v2.1.1.md` | `ea5e4b45…de246e` |
| PRD | 1.0.1 (from 1.0.0 baseline) | `PRD.md` | `source/Aish Agentic AI — Product Requirement Document — v1.0.1.md` | `c370071c…874288` |
| Master Source (historical) | 2.0.0 | — | `source/MASTER SOURCE — Aish Agentic AI — v2.0.0.md.txt` | `cad79234…efc21b` |

Full checksums: `../evidence/source-checksums/SHA256SUMS.txt`. Originals in `source/` are never overwritten
or deleted; superseded decisions are marked superseded, not removed (`.claude/rules/12`).

## Repository identity

Canonical repository: `https://github.com/makemesick91-code/aish_agentic_ai`
(normalized `makemesick91-code/aish_agentic_ai`). No other repository is canonical without a versioned ADR.
