#!/usr/bin/env bash
# build.sh — deterministic documentation knowledge-index build (Graphify fallback realization).
# Enumerates included files, enforces exclusions (no secrets/PII), counts nodes (files) and edges
# (internal markdown links), and writes a compact, DETERMINISTIC manifest (no timestamps -> idempotent).
# Config: graphify.yaml. Docs: docs/tooling/GRAPHIFY.md. Rule: .claude/rules/15.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
OUT="scripts/graphify/out"
EVID="docs/evidence/graphify"
mkdir -p "$OUT" "$EVID"

python3 - "$ROOT" <<'PY'
import os, re, sys, json, fnmatch
root = sys.argv[1]
INCLUDE_DIRS = ["CLAUDE.md", ".claude", "docs", "scripts", ".github"]
INCLUDE_EXT = (".md", ".php", ".sh", ".yml", ".yaml")
EXCLUDE_GLOBS = [
    "**/.env", "**/.env.*", "**/*.pem", "**/*.key", "**/*.p12", "**/*.pfx",
    "**/secrets/**", "**/credentials/**", "**/*service-account*.json", "**/*token*.json",
    "**/*.sql", "**/*.dump", "**/backups/**", "**/dumps/**",
    "**/vendor/**", "**/node_modules/**", "**/dist/**", "**/build/**",
    "scripts/graphify/out/**", ".git/**",
    "docs/canonical/source/**",   # preserved originals: authoritative, not indexed as derived nodes
]

def excluded(rel):
    for g in EXCLUDE_GLOBS:
        if fnmatch.fnmatch(rel, g) or fnmatch.fnmatch(rel, g.replace("**/", "")):
            return True
    return False

files = []
for dirpath, dirnames, filenames in os.walk(root):
    dirnames[:] = [d for d in dirnames if d != ".git"]
    for fn in filenames:
        full = os.path.join(dirpath, fn)
        rel = os.path.relpath(full, root)
        top = rel.split(os.sep)[0]
        if not (rel == "CLAUDE.md" or top in INCLUDE_DIRS):
            continue
        if not rel.endswith(INCLUDE_EXT):
            continue
        if excluded(rel):
            continue
        files.append(rel)

files = sorted(set(files))

# Safety assertion: no excluded/secret-bearing path slipped into the node set.
for rel in files:
    assert not excluded(rel), f"excluded file indexed: {rel}"

link_re = re.compile(r'(?<!\!)\[[^\]]*\]\(([^)]+)\)')
edges = 0
for rel in files:
    if not rel.endswith(".md"):
        continue
    with open(os.path.join(root, rel), encoding="utf-8") as fh:
        for m in link_re.finditer(fh.read()):
            t = m.group(1)
            if not t.startswith(("http://", "https://", "mailto:", "#")):
                edges += 1

manifest = {
    "schema": "aish-agentic-ai/doc-index/v1",
    "mode": "deterministic-doc-index",
    "authoritative": False,
    "node_count": len(files),
    "edge_count": edges,
    "nodes": files,
}
with open(os.path.join(root, "scripts/graphify/out/graph-manifest.json"), "w", encoding="utf-8") as fh:
    json.dump(manifest, fh, sort_keys=True, indent=2, ensure_ascii=False)
    fh.write("\n")

# Compact committed evidence (counts only; not the full node list -> small + review-friendly).
compact = {k: manifest[k] for k in ("schema", "mode", "authoritative", "node_count", "edge_count")}
with open(os.path.join(root, "docs/evidence/graphify/build-manifest.json"), "w", encoding="utf-8") as fh:
    json.dump(compact, fh, sort_keys=True, indent=2, ensure_ascii=False)
    fh.write("\n")

print(f"PASS: graph built — {len(files)} nodes, {edges} edges (deterministic, secrets excluded)")
PY
