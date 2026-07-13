# AGENTS.md — docs/security/

Area rules for security & privacy. See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/03,04,18`.

- Secrets/`.env`/tokens/keys/dumps MUST NOT be committed; OAuth tokens encrypted, refresh never plaintext (AFR-023,024).
- Tenant isolation on every surface; consult the [Tenant Isolation Control Matrix](TENANT_ISOLATION_CONTROL_MATRIX.md).
- Healthcare `MED` data (diagnosis, notes, MRN, prescription, odontogram, imagery, treatment history, insurance,
  bank/PAN) MUST NOT be stored, sent to AI, or placed in public output (AFR-046,048).
- Customer content is untrusted; prompt-injection defense + tool allowlisting mandatory (AFR-049,050).
- Every threat has preventive + detective + recovery + test (see [STEP_3_THREAT_MODEL](STEP_3_THREAT_MODEL.md)).
- **Application implementation: NOT STARTED.** No real secret or PII belongs in the repository.
