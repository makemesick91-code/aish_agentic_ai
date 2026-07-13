#!/usr/bin/env bash
# validate-workflow-security.sh — static security review of GitHub Actions workflows.
#
# Enforces CICD-CTRL-1 workflow-security baseline (rule 28; CI-PRINCIPLE-15..18):
#   * third-party/official actions pinned to an immutable 40-hex commit SHA
#   * a top-level `permissions:` block, never `write-all`
#   * no `pull_request_target` executing untrusted PR code
#   * no `curl | sh` / `wget | bash` remote-script execution
#   * no untrusted PR title/body/ref interpolated into a `run:` shell
#   * every job declares `timeout-minutes`
#   * no `[skip ci]` / skip-directive enablement for mandatory workflows
# Static, read-only. Requires: grep, awk. Rule: .claude/rules/28.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
WFDIR=".github/workflows"
fail=0

shopt -s nullglob
files=("$WFDIR"/*.yml "$WFDIR"/*.yaml)
if [ "${#files[@]}" -eq 0 ]; then echo "FAIL: no workflow files under $WFDIR"; exit 1; fi

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

  # 2. permissions present, never write-all.
  if ! grep -Eq '^[[:space:]]*permissions:' "$wf"; then
    echo "FAIL($base): missing top-level 'permissions:' (least privilege required)"; fail=1
  fi
  if grep -Eq 'permissions:[[:space:]]*write-all' "$wf"; then
    echo "FAIL($base): 'write-all' permission is prohibited"; fail=1
  fi

  # 3. No pull_request_target running untrusted code.
  if grep -Eq '^[[:space:]]*pull_request_target:' "$wf"; then
    echo "FAIL($base): pull_request_target is prohibited (untrusted privileged execution)"; fail=1
  fi

  # 4. No remote-script piped to a shell.
  if grep -Eq '(curl|wget)[^|]*\|[[:space:]]*(sudo[[:space:]]+)?(ba)?sh([[:space:]]|$)' "$wf"; then
    echo "FAIL($base): remote script piped into a shell is prohibited"; fail=1
  fi

  # 5. No untrusted PR fields interpolated directly into a run shell (warn — review).
  if grep -Eq 'run:.*\$\{\{[[:space:]]*github\.event\.(pull_request\.(title|body)|issue\.(title|body))' "$wf" \
     || grep -Eq '\$\{\{[[:space:]]*github\.head_ref[[:space:]]*\}\}' "$wf"; then
    echo "WARN($base): review untrusted-input interpolation into shell (script-injection risk)"
  fi

  # 6. Every job has timeout-minutes (count runs-on vs timeout-minutes).
  n_runson="$(grep -cE '^[[:space:]]*runs-on:' "$wf" || true)"
  n_timeout="$(grep -cE '^[[:space:]]*timeout-minutes:' "$wf" || true)"
  if [ "$n_runson" -gt 0 ] && [ "$n_timeout" -lt "$n_runson" ]; then
    echo "FAIL($base): $n_runson job(s) but only $n_timeout timeout-minutes (every job needs one)"; fail=1
  fi

  # 7. No skip-ci directive enablement inside mandatory workflow config.
  if grep -Eiq '\[skip ci\]|\[ci skip\]|skip-checks' "$wf"; then
    echo "FAIL($base): skip-ci directive handling must not weaken mandatory checks"; fail=1
  fi
done

echo "OK: scanned ${#files[@]} workflow file(s)"
if [ "$fail" -eq 0 ]; then echo "PASS: workflow security baseline"; else echo "validate-workflow-security: FAILED"; exit 1; fi
