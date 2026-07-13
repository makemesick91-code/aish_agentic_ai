# Architecture Decision Records (ADRs)

Foundation ADRs are maintained centrally under [`../../decisions/adr/`](../../decisions/adr/) to avoid
duplicate, competing ADR trees. Architecture-affecting decisions are cross-referenced here.

| ADR | Title | Architecture relevance |
|-----|-------|------------------------|
| [0001](../../decisions/adr/0001-canonical-documentation-hierarchy-and-authority.md) | Canonical documentation hierarchy & authority | Documentation architecture |
| [0002](../../decisions/adr/0002-claude-persistent-context-claude-md-and-modular-rules.md) | Claude persistent context (CLAUDE.md + modular rules) | Project-memory architecture |
| [0003](../../decisions/adr/0003-graphify-derived-knowledge-graph-not-source-of-truth.md) | Graphify as derived knowledge graph | Knowledge-retrieval architecture |
| [0004](../../decisions/adr/0004-minimal-mcp-allowlist-and-secrets-policy.md) | Minimal MCP allowlist & secrets policy | Tooling/integration architecture |
| [0005](../../decisions/adr/0005-project-skills-and-subagents-architecture.md) | Project skills & subagents architecture | Automation/review architecture |
| [0006](../../decisions/adr/0006-documentation-as-code-ci-and-evidence-gates.md) | Documentation-as-code CI & evidence gates | CI/validation architecture |
| [0007](../../decisions/adr/0007-immutable-documentation-foundation-go-tag-semantics.md) | Immutable documentation-foundation GO tag | Release architecture |

New application-architecture ADRs (e.g. repository topology — see `../../product/OPEN_DECISIONS.md` OD-2)
will be added under `../../decisions/adr/` and cross-linked here. Rule: `.claude/rules/08`, `12`.
