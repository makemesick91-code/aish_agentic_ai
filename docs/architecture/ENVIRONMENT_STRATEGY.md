# Environment Strategy — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §43 · **Rules:** `.claude/rules/04`, `11`, `20` ·
**ADR:** [0025](../decisions/adr/0025-environment-and-secret-management.md), [0026](../decisions/adr/0026-ci-cd-architecture.md).

## 1. Environments
`local` · `test` · `CI` · `staging` · `pilot` · `production`. None are provisioned or deployed in Step 3; this
is the planned matrix implementation and operations will realize.

## 2. Configuration classification (ADR 0025)
| Class | Examples | Handling |
|-------|----------|----------|
| Required | app key, DB DSN, Redis URL, storage bucket | must be set per environment; validated on boot |
| Optional | feature toggles, tuning | safe defaults |
| Secret | DB password, OAuth client secret, provider API keys, webhook signing keys | secret manager / env only; **never committed** |
| Environment-specific | hostnames, base URLs | per-environment config |
| Tenant-configurable | invitation timing, retention windows | stored per tenant, not in env |
| Feature-flag | rollout gates, kill switches | flag store; auditable |

## 3. Secret prohibition (hard)
Never commit: `.env`, credentials, access/refresh tokens, private keys, database dumps, production hostname
credentials. Enforced by `scripts/docs/secret-scan.sh`, the deny-lists in `.claude/settings.json` /
`.codex/rules/`, and GitHub push protection (`.claude/rules/04`). OAuth tokens are encrypted at rest (ADR 0022).

## 4. Promotion path
`local → test/CI → staging → pilot → production`. Each promotion is gated by CI and the applicable release
gates (`.claude/rules/09`, `13`). No environment inherits another's secrets.

## 5. Architecture decision vs runtime evidence
This document is an **architecture decision**. There is **no runtime evidence** in Step 3: no environment is
built, no secret is provisioned, nothing is deployed. See [Deployment Topology](DEPLOYMENT_TOPOLOGY.md).
