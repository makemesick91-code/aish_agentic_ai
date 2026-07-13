#!/usr/bin/env bash
# check-rule-frontmatter.sh — every .claude/rules/*.md MUST have valid frontmatter with required keys.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
RULES_DIR=".claude/rules"
REQUIRED=(id title domain scope authority canonical_refs supersede)
fail=0
count=0

mapfile -t files < <(find "$RULES_DIR" -maxdepth 1 -name '*.md' | sort)
if [ "${#files[@]}" -lt 16 ]; then
  echo "FAIL: expected >=16 rule files, found ${#files[@]}"; fail=1
fi

for f in "${files[@]}"; do
  count=$((count+1))
  # Frontmatter must start at line 1 with '---' and have a closing '---'.
  first="$(sed -n '1p' "$f")"
  if [ "$first" != "---" ]; then echo "FAIL($f): missing opening '---'"; fail=1; continue; fi
  # Extract frontmatter block (between first and second '---').
  fm="$(awk 'NR==1{next} /^---[[:space:]]*$/{exit} {print}' "$f")"
  for key in "${REQUIRED[@]}"; do
    if ! printf '%s\n' "$fm" | grep -Eq "^${key}:"; then
      echo "FAIL($f): missing frontmatter key '${key}'"; fail=1
    fi
  done
done

if [ "$fail" -eq 0 ]; then
  echo "PASS: rule frontmatter valid for $count rule file(s)"
else
  echo "check-rule-frontmatter: FAILED"; exit 1
fi
