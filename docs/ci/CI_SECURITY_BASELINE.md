# CI Security Baseline — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69; §43. Rule: `.claude/rules/28`, `04`, `25`. ADR 0045. AFR-119..122.
Enforced by `scripts/ci/validate-workflow-security.sh`.

## Controls
| Control | Requirement | Enforced by |
|---------|-------------|-------------|
| Pinned actions | every `uses:` is a local `./` reusable or `@<40-hex SHA>` | validator §1 (CI-SEC-03) |
| Least privilege | top-level `permissions:` present; default `contents: read`; no `write-all` | validator §2 (CI-SEC-04) |
| No untrusted privileged exec | no `pull_request_target` | validator §3 (CI-SEC-05) |
| No remote-script exec | no `curl\|sh` / `wget\|bash` | validator §4 |
| No script injection | untrusted PR title/body/ref/`inputs.*`/comment/commit-message not interpolated into a `run:` shell (FAIL; `env:` assignment is the safe remediation) | validator §5 |
| Bounded jobs | every job declares its own job-level `timeout-minutes` (per-job scan) | validator §6 |
| No skip bypass | no `[skip ci]`/`skip-checks` enablement in mandatory workflows | validator §7 |
| Secret hygiene | no committed secret patterns; no tracked `.env` | `scripts/docs/secret-scan.sh` |

## Pinned actions in use
| Action | Version | Pinned SHA |
|--------|---------|-----------|
| `actions/checkout` | v5.0.0 | `08c6903cd8c0fde910a37f88322edcfb5dd907a8` |

`setup-python` was dropped in favour of the runner's preinstalled `python3`, reducing the action surface to one.
Advancing a pin is a recorded decision; the new SHA is verified against the upstream tag (e.g.
`gh api repos/actions/checkout/git/refs/tags/<tag> --jq .object.sha`) before use.

## Repository-level notes
- `GITHUB_TOKEN` writes are granted only to a job that needs them (none currently need write).
- No secrets are printed; no environment dump; caches (future runtime) key on lock hashes, never secrets.
- Repository Actions "SHA pinning required" setting may be enabled later; this validator enforces pinning at the
  workflow level regardless.

## Never optimized away
Secret scan, workflow-security, tenant-isolation (when the application exists), and release-integrity gates MUST NOT
be removed for speed (AFR-119). A `continue-on-error` on a mandatory security job is prohibited.
