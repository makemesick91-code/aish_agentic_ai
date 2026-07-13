#!/usr/bin/env bash
# secret-scan.sh — high-signal secret scan over tracked files. Patterns use bracket classes so this
# scanner does not match itself. Generic keyword=value patterns are intentionally avoided to prevent
# false positives on documentation. Also fails if any .env file is tracked.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0

# High-signal secret token / key formats (bracket classes => self-safe).
PATTERNS=(
  'gh[p]_[A-Za-z0-9]{36}'
  'github_pa[t]_[A-Za-z0-9_]{22,}'
  'xox[baprs]-[A-Za-z0-9-]{10,}'
  'AKIA[0-9A-Z]{16}'
  'AIza[0-9A-Za-z_-]{35}'
  '-----BEGIN [A-Z ]*PRIVATE KEY-----'
  'eyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}'
)

# Files to scan: tracked files if in a git repo, else the working tree. Exclude .git and generated output.
if git rev-parse --git-dir >/dev/null 2>&1; then
  mapfile -t FILES < <(git ls-files | grep -vE '^(scripts/graphify/out/)' )
else
  mapfile -t FILES < <(find . -type f -not -path './.git/*' -not -path './scripts/graphify/out/*')
fi

SCANNER="scripts/docs/secret-scan.sh"
for pat in "${PATTERNS[@]}"; do
  for f in "${FILES[@]}"; do
    [ "$f" = "$SCANNER" ] && continue          # never scan the scanner itself
    if LC_ALL=C grep -EInq "$pat" "$f" 2>/dev/null; then
      echo "FAIL: potential secret matching /$pat/ in $f"; fail=1
    fi
  done
done

# No .env* file may be tracked (an .env.example placeholder is allowed).
if git rev-parse --git-dir >/dev/null 2>&1; then
  if git ls-files | grep -E '(^|/)\.env($|\.)' | grep -vE '\.env\.example$' | grep -q .; then
    echo "FAIL: a .env file is tracked"; fail=1
  fi
fi

if [ "$fail" -eq 0 ]; then echo "PASS: secret scan clean (${#FILES[@]} files, ${#PATTERNS[@]} patterns)"; else echo "secret-scan: FAILED"; exit 1; fi
