---
name: security-privacy-review
description: Review secrets handling, OAuth/token encryption, PII minimization, healthcare data boundary, prompt-injection defense, and public-reply safety against canonical sources. Read-only; never exposes secrets.
---

# Skill: security-privacy-review

**Trigger:** Changes to security, secrets, credentials, AI input/output, or public replies.
**Non-trigger:** Non-sensitive doc edits.
**Inputs:** `docs/security/*`, threat model, data classification.

## Workflow
1. Confirm no secret/`.env`/token/key/dump in the diff; `secret-scan.sh` clean.
2. Confirm OAuth tokens encrypted, refresh never plaintext, rotation (AFR-024,025).
3. Confirm `MED` data excluded from storage/AI/public output; PII minimized (AFR-046,048).
4. Confirm prompt-injection defense + tool allowlisting; public reply human-approved, no gating (AFR-049,050,027,028).

## Safety boundaries
Read-only. MUST NOT print secret contents. MUST NOT weaken a control.

## Required output
Findings (BLOCKER/HIGH/MEDIUM/LOW) with file + rule reference; or "no security/privacy gap".

## Evidence
`docs/security/STEP_3_THREAT_MODEL.md`, `docs/security/DATA_CLASSIFICATION_AND_HANDLING.md`, `secret-scan.log`.

## Failure behavior
Any secret exposure or medical/PII leakage path is a BLOCKER → NO-GO until fixed.
