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

Application-architecture ADRs **0009–0032** (Step 3) and **0033–0041** (Step 4 — domain/DNS, environment topology,
environment data policy, local development, configuration/secrets, dependency baseline, SaaS Foundation sequence,
deployment-target class, brand token governance) live under [`../../decisions/adr/`](../../decisions/adr/) and are
mapped in the [Step 4 Traceability Matrix](../../quality/STEP_4_REQUIREMENTS_TRACEABILITY_MATRIX.md). Rule: `.claude/rules/08`, `12`, `20`.

CI/runtime and SaaS-core ADRs also live under [`../../decisions/adr/`](../../decisions/adr/): **0042–0046**
(CICD-CTRL-1 — safe CI runtime control; rule 28), **0047–0050** (Step 5 — runtime & repository bootstrap; rule 29),
and **0051–0053** (Step 6 — SaaS Core Foundation; rule 30):

| ADR | Title | Architecture relevance |
|-----|-------|------------------------|
| [0051](../../decisions/adr/0051-step-6-consolidated-saas-core-foundation.md) | Step 6 consolidated SaaS Core Foundation | Release packaging of coupled SF-01..SF-04 sprints |
| [0052](../../decisions/adr/0052-saas-core-platform-placement.md) | SaaS core platform placement (top-level `app/`, not `app/Modules/`) | Cross-cutting substrate placement |
| [0053](../../decisions/adr/0053-tenant-membership-and-context-model.md) | Tenant membership & context model | Tenancy, identity, RBAC, isolation |
