---
id: "35"
title: Autonomous Execution and Tooling Governance
domain: tooling-governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source §76; §57, §66.6–§66.9, §69"
  - "PRD v1.3.0"
  - "ADR 0069; ADRs 0042–0046; AFR-239..249; rules 04, 09, 13, 14, 15, 28"
supersede: "Permanent for the autonomous coding-agent workflow. Non-root execution, the destructive-operation deny set + real PreToolUse enforcement hook, no-force-push / no-history-rewrite / no-tag-move, no-secret-in-repo, no-gate-weakening, no-fabricated-evidence, no-bypass-of-external-authorization, evidence-based completion, and genuine-blocker-only stopping cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees."
---

# Rule 35 — Autonomous Execution and Tooling Governance

## Purpose
Let the Claude Code coding agent complete routine engineering work autonomously — without per-action Yes/No
confirmation — while keeping every security, tenant-isolation, privacy, auditability, release, and truthful-completion
guarantee intact. Autonomy speeds execution; it **MUST NOT** widen the blast radius of a mistake, a hostile prompt, or
a compromised input.

## Scope
Claude Code / coding-agent permission configuration and behavior: user-level settings (`~/.claude/settings.json`),
project-level settings (`.claude/settings.json`, `.claude/settings.local.json`), the PreToolUse safety hook
(`scripts/hooks/guard-dangerous-commands.sh`), and the autonomous branch → commit → push → PR → CI → merge → GO-tag →
evidence workflow. This is tooling/process governance only; it creates **no** application feature, migration, or runtime.

## Rules

### Autonomous execution
- Routine operations — Bash, Git, GitHub CLI, Composer, PHP, Artisan, npm/Node, safe development migrations, test, lint,
  static analysis, build, documentation generation, CI inspection, PR creation, and evidence collection — **MAY** run
  without per-action confirmation. Non-interactive flags **SHOULD** be used where available (`--no-interaction`,
  `npm ci`, `gh pr create --fill`).
- Recoverable failures (failing test/lint/static-analysis/build, fixable dependency/migration/formatting issues,
  deterministically resolvable merge conflicts, CI failures from repository code/config, documentation/governance gate
  failures, generated-artifact drift) **MUST** be diagnosed and fixed autonomously; they are **NOT** blockers and
  **MUST NOT** be reported as `BLOCKED`.
- The agent **MUST NOT** ask the user to run a command it can run itself, or request routine Yes/No confirmation for
  in-scope work.

### Permission configuration
- User-level autonomy is a deliberate opt-in: `permissions.defaultMode = bypassPermissions`,
  `skipDangerousModePermissionPrompt = true`, and an empty `permissions.ask` are permitted **only** together with a
  destructive-operation `permissions.deny` set (AFR-239). This mode becomes effective for **future** sessions, not the
  session that writes it — status **MUST** be reported truthfully.
- Project-level settings **MUST** remain a **contributor-safe baseline** and **MUST NOT** silently force unrestricted
  mode on other contributors: release operations (`git push`, `git merge`, `git tag`, `gh pr merge`, `gh release`) stay
  `ask`-gated, destructive operations stay `deny`-listed, and the PreToolUse guard hook stays registered (AFR-240).
- Project settings **MUST NOT** contain secrets, tokens, credentials, personal absolute paths, or a hidden
  security-control downgrade (rules 04, 15, 24).

### Real enforcement (defense in depth)
- Prohibitions **MUST** be enforced by a real PreToolUse hook (`scripts/hooks/guard-dangerous-commands.sh`), not by prose
  alone. The hook **MUST** block, regardless of permission mode: force-push (incl. `--force-with-lease`), remote-ref
  deletion, tag `-f`/`-d`/move, `reset --hard`/`filter-branch`/`filter-repo`, reads of `.env`/private-key/dump material,
  filesystem/device destruction (`mkfs`, `dd if=`, `shred`, `git clean` force-delete), package publish, cloud
  provisioning/deployment, DNS mutation, and skip-CI directives (AFR-241; rules 13, 28). The hook **MUST** carry
  positive and negative tests (`scripts/hooks/test-guard.sh`) run by the doc-gate suite (rule 15).

### Permanent prohibitions (unchanged)
- The following remain permanently prohibited and **MUST** be enforced by deny rules + the guard hook, never merely
  described: `git push --force`/`-f`/`--force-with-lease`, history rewrite, tag move/deletion, destructive
  `reset --hard`/`clean -f`, production database/volume deletion, committing any secret/token/credential/backup,
  weakening or disabling a test/security scanner/branch-protection/release gate, and fabricating status, CI, deployment,
  merge, or evidence (AFR-243; rules 04, 09, 13, 28).
- Automation **MUST NOT** bypass external authorization boundaries — MFA, OAuth consent, CAPTCHA, branch protection, or
  credentials it does not hold — and **MUST NOT** use admin-bypass of branch protection (AFR-246; rule 13).
- Claude Code **MUST** run as a non-root user; unrestricted autonomous execution **MUST NOT** run as root on a
  production host (AFR-247).
- User-level settings, their backups, secrets, and tokens **MUST NOT** be committed; only project-level
  configuration, documentation, tests, and governance are versioned (AFR-248; rule 04).

### Autonomous Git / release flow
- The agent **MAY** autonomously create a feature branch, commit atomically, push normally, open/update a PR, watch and
  fix CI, merge when every required gate is green and branch protection allows, verify the exact merged SHA,
  clean-checkout-verify, create and push an **annotated immutable** GO tag when all relevant gates pass, and gather
  branch/PR/CI/merge/tag evidence (AFR-245; rule 13).
- A GO tag **MUST** be created only after all relevant gates pass with SHA-bound evidence, and **MUST NOT** be moved
  once created (rules 13, 28). CI-efficiency and completion claims **MUST** be backed by actual run evidence (rule 28).

### Genuine-blocker-only stopping
- The agent **MUST** stop with a structured `BLOCKED` report only for a genuine blocker: a missing required
  credential/secret/access; a required MFA/OAuth/CAPTCHA/hardware/human step; an inaccessible repository/host/provider or
  provider outage; branch protection requiring a human approval the agent cannot give; an unmitigated irreversible
  production risk; a missing material product decision not in the Master Source; a scope conflicting with
  security/privacy/tenant-isolation/compliance/permanent decisions; an unavailable safe privilege; required evidence that
  is unavailable and **MUST NOT** be fabricated; or a required external payment/purchase/contractual approval (AFR-244).
- The `BLOCKED` report **MUST** state the last successful step, the failing command, the actual error, the verified root
  cause, fixes attempted, impact, the exact human action/access needed, the follow-up command, and current
  branch/worktree state.

### Precedence
- Security, tenant isolation, privacy, policy compliance, correctness, auditability, and truthful completion **MUST**
  outrank convenience and automation (AFR-246; Master Source §57).

## Required checks
- `scripts/hooks/test-guard.sh` (positive + negative guard-hook cases); `scripts/docs/secret-scan.sh`;
  `scripts/docs/validate.sh` (full documentation-as-code suite, incl. `hook-guard-tests`); JSON validity of
  `~/.claude/settings.json` and `.claude/settings.json`; the `backend-runtime-ci` gate where application code is touched
  (rules 28, 29).

## Evidence
- `.claude/settings.json`, `.claude/settings.local.json`, `scripts/hooks/guard-dangerous-commands.sh`,
  `scripts/hooks/test-guard.sh`; `docs/decisions/adr/0069-autonomous-execution-and-tooling-governance.md`;
  `docs/governance/foundation-coverage-matrix.md`; `docs/status/CURRENT_STATE.md`.

## Related canonical sections
- Master Source §76; §57 (decision priority), §66.6–§66.9 (Limit Saver / MCP / skills / subagents), §69 (safe CI);
  PRD v1.3.0; ADR 0069; ADRs 0042–0046; AFR-239..249; rules 04, 09, 13, 14, 15, 28.

## Supersession
Permanent for the autonomous coding-agent workflow. Non-root execution, the destructive-operation deny set + real
enforcement hook, no-force-push / no-history-rewrite / no-tag-move, no-secret-in-repo, no-gate-weakening,
no-fabricated-evidence, no-bypass-of-external-authorization, evidence-based completion, and genuine-blocker-only stopping
are permanent; superseded only by a higher-version Master Source update that preserves these guarantees.
