# Privacy and PII — Aish Agentic AI

Canonical: Master Source §15.3, §37, §43 (healthcare rules), §42. Rule: `.claude/rules/04`, `06`, `07`. PRD §15.2.

## Principles
Privacy by design: minimize personal and sensitive data; classify sensitive data; configurable retention;
audit admin access, exports, and deletions (Master Source §37). Audit history is immutable (`.claude/rules/07`).

## Public-output prohibitions (Master Source §43 healthcare rules)
Public replies MUST NOT reveal: diagnosis, medical procedures, visit history, health conditions, medical
record numbers, doctor–patient identity, treatment schedules, medications, or test results. Personal,
financial, and sensitive-transaction data are likewise prohibited in public output (`.claude/rules/06`).

Safe pattern: *"Terima kasih atas masukan Anda. Tim kami ingin mempelajari pengalaman tersebut lebih lanjut
melalui kanal resmi dan privat kami."*

## AI data handling
AI input MAY pass through redaction; AI output storage is controlled; prompt/model versions and tool calls
are logged (`.claude/rules/05`, `07`). Knowledge retrieval sends only minimum, tenant/branch-filtered
context (Master Source §42). Customer content is untrusted (`PROMPT_INJECTION_DEFENSE.md`).

## Data subject controls
Tenants can disconnect Google and delete credentials; data export and deletion are supported, audited, and
tested before a product release (Master Source §54 data gate; `../operations/BACKUP_RESTORE_BASELINE.md`).

**Status:** privacy baseline documented. Runtime redaction/retention apply at implementation (NOT STARTED).
