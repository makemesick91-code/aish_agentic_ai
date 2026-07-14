#!/usr/bin/env bash
# check-links.sh — verify internal markdown links resolve to existing files. External (http/mailto) and
# pure-anchor links are skipped. Uses python3 for robust markdown link parsing.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

python3 - "$ROOT" <<'PY'
import os, re, sys
root = sys.argv[1]
link_re = re.compile(r'(?<!\!)\[[^\]]*\]\(([^)]+)\)')
# Skip VCS, generated, and third-party dependency/build trees. Dependency dirs
# (vendor/, node_modules/) are gitignored and only exist after a local install;
# their bundled READMEs are not repository-owned docs and must not gate the repo.
SKIP_DIRS = ('.git', 'node_modules', 'vendor', '.graphify', 'graphify-out')
SKIP_SUBPATHS = ('/scripts/graphify/out', '/storage/framework', '/public/build')
md_files = []
for dirpath, dirnames, filenames in os.walk(root):
    dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS]
    if any(s in dirpath for s in SKIP_SUBPATHS):
        continue
    for fn in filenames:
        if fn.endswith('.md'):
            md_files.append(os.path.join(dirpath, fn))

broken = 0
checked = 0
for md in md_files:
    base = os.path.dirname(md)
    with open(md, encoding='utf-8') as fh:
        text = fh.read()
    for m in link_re.finditer(text):
        target = m.group(1).strip()
        # Skip external, anchors, mail, and template/example paths.
        if target.startswith(('http://', 'https://', 'mailto:', '#')):
            continue
        # Strip anchor fragment and surrounding angle brackets/quotes.
        path = target.split('#', 1)[0].strip().strip('<>').split(' ')[0]
        if not path:
            continue
        # Glob-like or wildcard references are documentation, not links.
        if any(ch in path for ch in ['*', '{', '}']):
            continue
        resolved = os.path.normpath(os.path.join(base, path))
        checked += 1
        if not os.path.exists(resolved):
            print(f"BROKEN: {os.path.relpath(md, root)} -> {target}")
            broken += 1

if broken:
    print(f"check-links: FAILED ({broken} broken of {checked} internal links)")
    sys.exit(1)
print(f"PASS: {checked} internal links resolve across {len(md_files)} markdown files")
PY
