# Google OAuth and Token Security — Aish Agentic AI

Canonical: Master Source §38 (setup/connection), §22.10, §43. Rule: `.claude/rules/04`, `06`. PRD §10.10, §15.1.

## Setup (Master Source §38)
Create Google Cloud project · configure OAuth consent screen · create OAuth client · enable required APIs ·
apply for access if required · define redirect URI · provide privacy policy + ToS · **separate development
and production credentials** · re-verify current policy before production.

## Connection workflow (Master Source §38)
```
Connect Google → OAuth redirect → tenant grants → callback → token ENCRYPTED →
business account fetched → locations fetched → mapped to branch → initial sync → connection health recorded
```

## Token security (mandatory — `.claude/rules/04`)
- Access tokens MUST be encrypted; refresh tokens MUST NOT be stored in plaintext.
- OAuth `state` MUST be validated; tokens MUST support rotation and reauthorization.
- Credentials MUST NOT be committed; they live in a secure store referenced by env vars (`../../../SECURITY.md`).
- Tenants can disconnect Google and delete credentials (`.claude/rules/07`).

## Health & failure states (Master Source §53)
Connection states: connected, expiring, reauthorization required, permission missing, syncing, sync failed,
disconnected. Alert on OAuth refresh failure and Google sync failure (`.claude/rules/11`).

**Status:** OAuth/token baseline documented. Real credentials/flows at implementation (NOT STARTED).
