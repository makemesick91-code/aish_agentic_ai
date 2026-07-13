#!/usr/bin/env bash
# check-version-consistency.sh — canonical versions and repository identity are consistent.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
IDENT="makemesick91-code/aish_agentic_ai"

# Master Source active version >= 2.1.1
MS="docs/canonical/MASTER_SOURCE.md"
ver="$(grep -m1 -E '^\*\*Versi:\*\*' "$MS" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1 || true)"
if [ -z "$ver" ]; then echo "FAIL: cannot read Master Source version"; fail=1; else
  IFS='.' read -r a b c <<<"$ver"
  if [ "$a" -lt 2 ] || { [ "$a" -eq 2 ] && [ "$b" -lt 1 ]; } || { [ "$a" -eq 2 ] && [ "$b" -eq 1 ] && [ "$c" -lt 1 ]; }; then
    echo "FAIL: Master Source version $ver < 2.1.1"; fail=1
  else echo "OK: Master Source version $ver (>= 2.1.1)"; fi
fi

# PRD normalized version == 1.0.1
PRD="docs/canonical/PRD.md"
pver="$(grep -m1 -E '^\*\*Versi:\*\*' "$PRD" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1 || true)"
if [ "$pver" = "1.0.1" ]; then echo "OK: PRD version $pver"; else echo "FAIL: PRD version '$pver' != 1.0.1"; fail=1; fi

# Repository identity present in key files
for f in CLAUDE.md README.md docs/canonical/MASTER_SOURCE.md docs/canonical/DOCUMENT_AUTHORITY.md; do
  if grep -q "$IDENT" "$f"; then echo "OK: identity in $f"; else echo "FAIL: '$IDENT' missing in $f"; fail=1; fi
done

# CLAUDE.md references active Master Source v2.1.1
if grep -q "v2.1.1" CLAUDE.md; then echo "OK: CLAUDE.md references v2.1.1"; else echo "FAIL: CLAUDE.md missing v2.1.1"; fail=1; fi

# Target GO tag name consistent where referenced
TAG="aish-agentic-ai-docs-foundation-v1.0.0-go"
for f in .claude/rules/13-git-ci-release-and-go-tag.md docs/release/RELEASE_MANIFEST.md docs/release/TAG_VERIFICATION.md; do
  grep -q "$TAG" "$f" || { echo "FAIL: GO tag name missing in $f"; fail=1; }
done

if [ "$fail" -eq 0 ]; then echo "PASS: version + identity consistency"; else echo "check-version-consistency: FAILED"; exit 1; fi
