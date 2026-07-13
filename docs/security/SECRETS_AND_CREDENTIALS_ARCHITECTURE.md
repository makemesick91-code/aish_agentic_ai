# Secrets and Credentials Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §43 · PRD v1.2.0 §15.1, §15.2 · **Rules:** `.claude/rules/04`, `15`, `20` ·
**ADR:** [0022](../decisions/adr/0022-oauth-credential-encryption.md), [0025](../decisions/adr/0025-environment-and-secret-management.md), [0031](../decisions/adr/0031-dependency-and-supply-chain-governance.md).

## 1. Absolute prohibition
Never commit: `.env`, credentials, access/refresh tokens, private keys, database dumps, production hostname
credentials, telemetry credentials, model-provider API keys. Enforced by:
- `scripts/docs/secret-scan.sh` (CI gate) + GitHub secret-scanning push protection.
- Deny-lists in `.claude/settings.json` and `.codex/rules/` (read `.env`/keys blocked).
- The PreToolUse guard hook (`scripts/hooks/guard-dangerous-commands.sh`) and Codex hooks.

## 2. Storage & handling
- Secrets live only in environment variables / a secret manager, referenced by name.
- OAuth access tokens **encrypted at rest**; refresh tokens **never plaintext**; OAuth **state** validated;
  tokens **rotatable**; tenants can disconnect and delete credentials (ADR 0022).
- Encryption keys are managed outside the repo (secret manager / KMS at implementation time).

## 3. Rotation & incident
Compromise response: rotate the affected secret, invalidate sessions/tokens, re-provision via secret manager
(never restore secrets from repo/backup), record a security event, and review the audit trail.

## 4. Codex/MCP/tooling
`.mcp.json` contains **no secrets** (empty server set today); `.codex/config.toml` contains no API keys or
personal paths. Tooling credentials, if ever needed, are env-referenced and reviewed (ADR 0031).

## 5. Assertion
No real secret, token, key, or credential is created, stored, or committed in Step 3. `secret-scan.log`
evidence records a clean scan.
