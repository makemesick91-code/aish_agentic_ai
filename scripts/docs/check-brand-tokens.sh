#!/usr/bin/env bash
# check-brand-tokens.sh — validate the Step 4 brand planning-token JSON: parses, carries a truthful
# planning label, and defines the required token groups. Rule: .claude/rules/22; AFR-084, AFR-085.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
F="docs/brand/tokens/brand-tokens.v1.json"

if [ ! -s "$F" ]; then echo "FAIL: missing/empty $F"; exit 1; fi

if command -v python3 >/dev/null 2>&1; then
  python3 - "$F" <<'PY' || fail=1
import json, sys
f = sys.argv[1]
try:
    d = json.load(open(f, encoding="utf-8"))
except Exception as e:
    print(f"FAIL: {f} invalid JSON: {e}"); sys.exit(1)

ok = True
# Truthful planning label must be present.
status = json.dumps(d).upper()
if "PLANNING TOKENS — NOT IMPLEMENTED IN UI" not in json.dumps(d) and "NOT IMPLEMENTED" not in status:
    print("FAIL: token JSON missing truthful planning label"); ok = False

required_top = ["version", "status", "color", "typography"]
for k in required_top:
    if k not in d:
        print(f"FAIL: token JSON missing top-level key '{k}'"); ok = False

color = d.get("color", {})
required_colors = ["primary","secondary","accent","success","warning","danger","information",
                   "neutral","background","surface","border","text","focusRing"]
for c in required_colors:
    if c not in color:
        print(f"FAIL: color token '{c}' missing"); ok = False

if ok:
    print(f"OK: {f} parses, labelled planning, {len(required_colors)} color tokens + typography present")
sys.exit(0 if ok else 1)
PY
else
  echo "NOTE: python3 absent — JSON structural check skipped (WATCH)"
fi

# Accessibility intent must be documented alongside tokens.
if LC_ALL=C grep -Eiq -- "WCAG|contrast" "$F" docs/brand/ACCESSIBILITY_BASELINE.md 2>/dev/null; then
  echo "OK: accessibility/contrast intent documented"
else
  echo "FAIL: no WCAG/contrast intent documented for brand tokens"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS: brand planning tokens valid"; else echo "check-brand-tokens: FAILED"; exit 1; fi
