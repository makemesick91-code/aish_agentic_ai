---
id: "15"
title: MCP, Skills, Subagents, Hooks, and Tool Safety
domain: tooling
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §66.7, §66.8, §66.9"
  - "GO Tag Prompt v1.0.1 (MCP / Skills / Subagents / Settings & Hooks)"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 15 — MCP, Skills, Subagents, Hooks, and Tool Safety

## Purpose
Keep tooling minimal, least-privilege, secret-free, and safe.

## Scope
`.mcp.json`, `.claude/settings.json`, hooks, project skills, and subagents.

## Rules
- MCP servers **MUST** follow minimal-sufficient access and an allowlist; unnecessary or redundant MCPs
  **MUST NOT** be added. `.mcp.json` **MUST NOT** contain secrets; credentials are env-var references.
- HTTP MCP services **MUST** bind to loopback by default or require authentication when exposed. Data scope,
  tool permissions, risk, and owner **MUST** be documented (`docs/tooling/MCP.md`, `MCP_SKILLS_MANIFEST.md`).
- Any MCP/tool that can mutate production, billing, credentials, data deletion, deployment, or public
  content **MUST** require explicit additional approval.
- Project skills (`master-source-update`, `documentation-gate`, `release-evidence`, `graphify-refresh`)
  **MUST** be repeatable workflows with **no unsafe automatic mutation**; a skill **MUST NOT** merge, publish,
  deploy, tag, or delete data on its own.
- Subagents (product-requirements, architecture, security-privacy, ai-governance, qa-traceability,
  release-governance) **MUST** have least-privilege tools and **MUST NOT** independently publish, merge,
  tag, or run destructive operations; they return concise findings only.
- Settings/hooks **MUST** deny or require confirmation for high-risk actions (reading `.env`/secrets/dumps,
  force-push, tag deletion/moving, destructive DB/production/rollback, public review publish, billing
  mutation, data deletion, unpinned installers, disabling tests/scanning/branch protection). Hooks **MUST**
  be validated with positive and negative tests and **MUST NOT** expose secret-bearing payloads.
- The knowledge graph (Graphify) is **derived**; it **MUST NOT** index secrets/PII and **MUST NOT** override canonical docs.

## Required checks
- `.mcp.json`, `.claude/settings.json`, skills, and subagents parse and validate; secret scan passes.

## Evidence
- `docs/tooling/MCP.md`, `MCP_SKILLS_MANIFEST.md`, `docs/tooling/GRAPHIFY.md`, `docs/tooling/CLAUDE_PROJECT_SETUP.md`.

## Related canonical sections
- Master Source §66.7 (Graphify), §66.8 (MCP), §66.9 (skills/subagents); GO Tag Prompt v1.0.1.

## Supersession
Superseded only by a higher-version Master Source update; least-privilege and no-secrets constraints are permanent.
