#!/usr/bin/env bash
# validate-workflow-security.sh — static security review of GitHub Actions workflows.
#
# Enforces CICD-CTRL-1 workflow-security baseline (rule 28; CI-PRINCIPLE-15..18):
#   * third-party/official actions pinned to an immutable 40-hex commit SHA
#   * a TOP-LEVEL `permissions:` block, never `write-all`
#   * no `pull_request_target` (block form or `on: [ ... ]` array form)
#   * no `curl | sh` / `wget | bash` remote-script execution
#   * no untrusted input interpolated into a `run:` shell (FAIL, not warn)
#   * every job declares its own `timeout-minutes` (per-job, not a whole-file count)
#   * no `[skip ci]`/`[no ci]`/`***NO_CI***` skip-directive enablement
# Static, read-only. Requires: grep, awk, sed. Rule: .claude/rules/28. ADR 0045.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
WFDIR=".github/workflows"
fail=0

shopt -s nullglob
files=("$WFDIR"/*.yml "$WFDIR"/*.yaml)
if [ "${#files[@]}" -eq 0 ]; then echo "FAIL: no workflow files under $WFDIR"; exit 1; fi

# Untrusted GitHub Actions context expressions that must not reach a shell unquoted.
UNTRUSTED='github\.event\.(pull_request|issue)\.(title|body)|github\.event\.comment\.body|github\.event\.(head_commit|commits\.[0-9]+)\.message|github\.head_ref|github\.event\.pull_request\.head\.(ref|label|repo)|inputs\.[A-Za-z_]'

for wf in "${files[@]}"; do
  base="$(basename "$wf")"

  # 1. Pinned actions: every `uses:` must be a local ./ reusable or pinned @<40hex>.
  while IFS= read -r line; do
    ref="$(printf '%s' "$line" | sed -E 's/.*uses:[[:space:]]*//; s/[[:space:]]*(#.*)?$//')"
    if [[ "$ref" == ./* ]]; then
      : # local reusable workflow/action
    elif [[ "$ref" =~ ^[^[:space:]]+@[0-9a-f]{40}$ ]]; then
      : # pinned to an immutable commit SHA
    else
      echo "FAIL($base): action not pinned to 40-hex SHA: '$ref'"; fail=1
    fi
  done < <(grep -nE '^[[:space:]]*-?[[:space:]]*uses:' "$wf" || true)

  # 2. A TOP-LEVEL `permissions:` block (column 0) is required; never write-all.
  if ! grep -Eq '^permissions:' "$wf"; then
    echo "FAIL($base): missing TOP-LEVEL 'permissions:' (least privilege required, AFR-121)"; fail=1
  fi
  if grep -Eq 'permissions:[[:space:]]*write-all' "$wf"; then
    echo "FAIL($base): 'write-all' permission is prohibited"; fail=1
  fi

  # 3. No pull_request_target — block form OR `on: [ ..., pull_request_target ]` array form.
  if grep -Eq '^[[:space:]]*pull_request_target:' "$wf" \
     || grep -Eq '^on:[[:space:]]*\[[^]]*pull_request_target' "$wf"; then
    echo "FAIL($base): pull_request_target is prohibited (untrusted privileged execution)"; fail=1
  fi

  # 4. No remote-script piped to a shell (sh/bash/zsh/python variants).
  if grep -Eq '(curl|wget)[^|]*\|[^|]*\b(sudo[[:space:]]+)?(ba|z)?sh([[:space:]]|$)' "$wf" \
     || grep -Eq '(curl|wget)[^|]*\|[^|]*python[0-9.]*([[:space:]]|$)' "$wf"; then
    echo "FAIL($base): remote script piped into an interpreter is prohibited"; fail=1
  fi

  # 5. Untrusted input interpolation is a FAIL — UNLESS it is an `env:`/`with:` key
  #    assignment (the safe remediation: assign to an env var, then use "$VAR" quoted).
  while IFS= read -r line; do
    content="${line#*:}"   # strip the leading `NN:` line-number prefix from grep -n
    # allow `NAME: ${{ ... }}` (env/with assignment) — a plain key:value, not a run line
    if printf '%s' "$content" | grep -Eq '^[[:space:]]*[A-Za-z_][A-Za-z0-9_-]*:[[:space:]]*\$\{\{'; then
      continue
    fi
    echo "FAIL($base): untrusted input interpolated into a shell (script injection): ${content#"${content%%[![:space:]]*}"}"; fail=1
  done < <(grep -nE "\\\$\\{\\{[[:space:]]*($UNTRUSTED)" "$wf" || true)

  # 6. Every job declares its own job-level `timeout-minutes` (2-space job header,
  #    4-space job property). Step-level timeouts (deeper indent) do not count.
  awk '
    /^jobs:/ { injobs=1; next }
    injobs && /^[A-Za-z]/ { if (job!="" && !t) { print job; bad=1 } injobs=0 }
    injobs && /^  [A-Za-z0-9_.-]+:[[:space:]]*$/ { if (job!="" && !t) { print job; bad=1 } job=$0; t=0 }
    injobs && /^    timeout-minutes:/ { t=1 }
    END { if (job!="" && !t) { print job; bad=1 } exit bad }
  ' "$wf" | while IFS= read -r j; do echo "FAIL($base): job without job-level timeout-minutes:${j}"; done
  if ! awk '
    /^jobs:/ { injobs=1; next }
    injobs && /^[A-Za-z]/ { if (job!="" && !t) exit 1; injobs=0 }
    injobs && /^  [A-Za-z0-9_.-]+:[[:space:]]*$/ { if (job!="" && !t) exit 1; job=$0; t=0 }
    injobs && /^    timeout-minutes:/ { t=1 }
    END { if (job!="" && !t) exit 1; exit 0 }
  ' "$wf"; then fail=1; fi

  # 7. No skip-directive enablement (all GitHub-recognized forms).
  if grep -Eiq '\[skip ci\]|\[ci skip\]|\[no ci\]|\[skip actions\]|\[actions skip\]|\*\*\*NO_CI\*\*\*|skip-checks' "$wf"; then
    echo "FAIL($base): skip-ci directive handling must not weaken mandatory checks"; fail=1
  fi
done

echo "OK: scanned ${#files[@]} workflow file(s)"
if [ "$fail" -eq 0 ]; then echo "PASS: workflow security baseline"; else echo "validate-workflow-security: FAILED"; exit 1; fi
