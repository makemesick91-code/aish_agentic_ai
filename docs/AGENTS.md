# AGENTS.md — docs/

Area rules for documentation. See root [AGENTS.md](../AGENTS.md) and `.claude/rules/12`.

- Canonical sources live in `docs/canonical/`; **do not** duplicate all canonical content in derived docs — link
  and use coverage matrices (`.claude/rules/12`, AFR-065).
- Preserve historical originals byte-for-byte in `docs/canonical/source/`; mark superseded decisions
  `SUPERSEDED`, never delete.
- Material decisions MUST update the living Master Source (semver + changelog) and the version matrix.
- Every doc uses only the truthful status vocabulary; never claim application implemented/deployed (AFR-068).
- **Application implementation: NOT STARTED.**
