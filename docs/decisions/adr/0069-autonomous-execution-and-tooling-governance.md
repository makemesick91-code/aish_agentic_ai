# ADR 0069 — Autonomous Execution and Tooling Governance for the Claude Code Coding Agent

- **Status:** Accepted (2026-07-15, Asia/Makassar) — tooling/process governance only; no application feature, migration, or runtime is created
- **Owner:** Principal Engineer / DevOps / Security Engineer
- **Rule:** `.claude/rules/35`, `.claude/rules/15`, `.claude/rules/13`, `.claude/rules/28` · **Canonical:** Master Source §76, §57, §66.6–§66.9, §69; PRD v1.3.0; rules 35, 04, 09, 13, 14, 15, 28

## Context
Routine Yes/No confirmation dialogs slow sprint execution and cause the coding agent to stop on work it could safely
finish (test-fix loops, git workflow, CI remediation, evidence collection). The owner has delegated the full
branch → merge → tag → release lifecycle and wants confirmation reserved for genuinely irreversible decisions. At the
same time, a bare "approve everything" mode would widen the blast radius of a mistake, a hostile prompt, or a malicious
input. This ADR locks an autonomy model that removes routine friction **without** weakening any security, tenant
isolation, privacy, auditability, or release guarantee, and that keeps a real (not prose-only) enforcement layer.

## Decision
- **User-level autonomy (opt-in):** `~/.claude/settings.json` sets `permissions.defaultMode = bypassPermissions`,
  `skipDangerousModePermissionPrompt = true`, and an empty `permissions.ask`, paired with a destructive-operation
  `permissions.deny` set (force-push/`-f`/`--force-with-lease`, remote-ref deletion, tag `-f`/`-d`/`--delete`,
  `reset --hard`, `git clean` force-delete, `rebase`/`filter-branch`/`filter-repo`, `rm -rf` of root/home,
  `mkfs`/`dd if=`/`shred`, and `.env`/private-key/service-account reads). The mode is effective for **future** sessions.
- **Project-level contributor-safe baseline (unchanged intent):** `.claude/settings.json` keeps release operations
  (`git push`, `git merge`, `git tag`, `gh pr merge`, `gh release`) `ask`-gated, keeps a destructive `deny` set, and
  keeps the PreToolUse guard hook registered. Autonomy is a **user-level opt-in**, never a hidden downgrade forced on
  other contributors.
- **Real enforcement:** `scripts/hooks/guard-dangerous-commands.sh` (a PreToolUse Bash hook, exit 2 = block) enforces the
  highest-risk denials regardless of permission mode — force-push, remote-ref/tag deletion, tag move, history rewrite,
  secret/dump reads, filesystem/device destruction (`mkfs`/`dd if=`/`shred`/`git clean -f`), package publish, cloud
  provisioning/deployment, DNS mutation, and skip-CI directives — validated by `scripts/hooks/test-guard.sh`
  (positive + negative), run inside `scripts/docs/validate.sh`.
- **Autonomous flow permitted, gates preserved:** branch → atomic commit → normal push → PR → CI observe/fix → merge when
  every required gate is green and branch protection allows → verify exact merged SHA → clean-checkout verify → annotated
  immutable GO tag → evidence — all bound to rules 13 and 28 (SHA-bound evidence; no admin-bypass; no tag move).
- **Genuine-blocker-only stopping:** the agent stops with a structured `BLOCKED` report only for enumerated genuine
  blockers (missing credential/access, required MFA/OAuth/human step, provider outage, branch-protection human approval,
  unmitigated irreversible production risk, missing product decision, scope conflicting with security/privacy/isolation,
  unavailable privilege, unfakeable-and-unavailable evidence, external payment/contract).
- **Precedence unchanged:** security, tenant isolation, privacy, compliance, correctness, auditability, and truthful
  completion outrank automation and convenience (Master Source §57).

## Alternatives
- **Keep per-action confirmation for everything** — rejected: it stalls autonomous sprints and forces stops on
  recoverable failures the agent should fix itself.
- **Bare `bypassPermissions` with no deny set and no hook** — rejected: removes the guardrails that keep force-push,
  history rewrite, secret reads, and device destruction impossible; unacceptable blast radius.
- **Push the bypass mode into project settings for all contributors** — rejected: silently downgrades every
  contributor's safety posture; autonomy must be an explicit per-user opt-in and the shared baseline must stay safe.
- **Rely on prose rules only** — rejected: prose is not enforcement; the real risk requires a hook that blocks at
  execution time (AFR-241).

## Consequences
Routine engineering runs without friction; test-fix, git, CI-remediation, and evidence loops proceed autonomously.
Destructive and outward-facing operations remain blocked by deny rules **and** a real hook that is mode-independent.
Contributors without the opt-in keep the `ask`-gated baseline. Release integrity, tenant isolation, and truthful
completion are unchanged.

## Impacts
- **Security:** deny rules + a real PreToolUse hook block force-push, history rewrite, secret/dump reads, and
  filesystem/device destruction regardless of permission mode; no external-authorization bypass.
- **Privacy:** `.env`/key/service-account/dump reads stay blocked; no PII/secret enters logs or the repository.
- **Tenant isolation:** unchanged — no tenant surface is touched; all Step 5–9 isolation gates re-run unchanged.
- **Database:** no schema/migration/runtime change; production database/volume deletion stays prohibited.
- **Operational:** faster autonomous sprints; non-root execution required; user settings are backed up before change and
  never committed; rollback is a settings restore + a doc revert.
- **Cost:** negligible; no new paid dependency, provider, or infrastructure.

## Verification / fitness function
`scripts/hooks/test-guard.sh` asserts the guard hook blocks every destructive class (positive cases) and allows safe
commands (negative cases); `scripts/docs/validate.sh` runs it as `hook-guard-tests` alongside secret-scan,
version-consistency, rule-frontmatter, ADR-structure, foundation-coverage, and the AGENTS chain. JSON validity of
`~/.claude/settings.json` and `.claude/settings.json` is checked with a parser. Fitness checks AEG-01..AEG-11 map to
AFR-239, AFR-240, AFR-241, AFR-242, AFR-243, AFR-244, AFR-245, AFR-246, AFR-247, AFR-248, AFR-249.

## Related
Requirement: Master Source §76, §57, §66.6–§66.9, §69; PRD v1.3.0. Rules: 35, 04, 09, 13, 14, 15, 28. ADRs: 0042, 0043,
0044, 0045, 0046.

## Evidence
`.claude/settings.json`, `.claude/settings.local.json`, `scripts/hooks/guard-dangerous-commands.sh`,
`scripts/hooks/test-guard.sh`; `.claude/rules/35-autonomous-execution-and-tooling-governance.md`;
`docs/governance/foundation-coverage-matrix.md`; `docs/status/CURRENT_STATE.md`; `CHANGELOG.md`.

## Non-claims
Creates no application feature, migration, table, or runtime; does not alter Step 5–9 foundations; does not deploy,
provision, or own any domain; does not claim pilot or production readiness; the user-level mode is effective only for
future sessions, not the session that writes it.

## Rollback
Restore the timestamped `~/.claude/settings.json.bak-*` backup (or set `permissions.defaultMode = default` and remove
`skipDangerousModePermissionPrompt`) to revert user-level autonomy; the destructive `deny` set and guard hook MAY be
kept. Revert the governance commit/PR to restore the prior CLAUDE.md, rule set, and Master Source via git history — no
history is deleted. Non-root execution, the destructive deny set + real enforcement hook, no-force-push /
no-history-rewrite / no-tag-move, no-secret-in-repo, no-gate-weakening, no-fabricated-evidence,
no-external-authorization-bypass, and evidence-based completion are permanent; changing any requires a new ADR +
owner-approved Master Source update.
