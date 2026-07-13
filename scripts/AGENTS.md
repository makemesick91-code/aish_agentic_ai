# AGENTS.md — scripts/

Area rules for scripts & validation. See root [AGENTS.md](../AGENTS.md) and `.claude/rules/09,13,15`.

- Scripts are deterministic, safe/read-only for validation, and MUST NOT mutate git state, merge, tag, deploy,
  or read secrets. Evidence writers redact secrets/PII.
- `scripts/docs/validate.sh` aggregates all documentation + Step 2 + Step 3 gates; CI runs the same.
- Adding a gate is allowed; removing/weakening a gate requires an owner-approved Master Source update (AFR-054,072).
- `scripts/hooks/` and `.codex/hooks/` guards are enforcement, not the only security layer; they have positive +
  negative tests.
- **Application implementation: NOT STARTED.** No application code is built or run by these scripts.
