#!/usr/bin/env bash
# audit-ci-runs.sh — CICD-CTRL-1 baseline CI audit.
#
# Enumerates recent GitHub Actions runs for the canonical repository, derives a
# duplicate-SHA / trigger-topology report, and writes machine-readable + human
# evidence under docs/evidence/cicd-ctrl-1/baseline/. Read-only: it never mutates
# workflows, runs, branch protection, or tags.
#
# Runner-minute billing is not exposed by the public runs API; durations are
# derived from run timestamps and labelled APPROXIMATE FROM RUN DURATION.
#
# Rule: .claude/rules/13, 28 (CI-PRINCIPLE-19 evidence over assertion). Requires: gh, python3.
# Usage: scripts/ci/audit-ci-runs.sh [LIMIT]   (LIMIT default 100)
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

REPO="makemesick91-code/aish_agentic_ai"
LIMIT="${1:-100}"
OUT="docs/evidence/cicd-ctrl-1/baseline"
mkdir -p "$OUT"

if ! command -v gh >/dev/null 2>&1; then
  echo "FAIL: gh CLI not available — cannot audit CI runs" >&2
  exit 1
fi

echo "== CICD-CTRL-1 baseline CI audit: $REPO (limit $LIMIT) =="

# 1. Fetch runs as JSON (raw, authoritative).
gh run list --repo "$REPO" --limit "$LIMIT" \
  --json databaseId,name,workflowName,event,headBranch,headSha,status,conclusion,createdAt,updatedAt,startedAt,displayTitle \
  > "$OUT/ci-runs.json"

# 2. Derive CSV + reports deterministically in python3 (no timestamp-of-now in committed output).
python3 - "$OUT" <<'PY'
import json, os, sys, csv
from datetime import datetime

out = sys.argv[1]
runs = json.load(open(os.path.join(out, "ci-runs.json"), encoding="utf-8"))

def parse(ts):
    if not ts:
        return None
    try:
        return datetime.strptime(ts.replace("Z", "+0000"), "%Y-%m-%dT%H:%M:%S%z")
    except ValueError:
        return None

def dur(r):
    a, b = parse(r.get("startedAt")), parse(r.get("updatedAt"))
    if a and b:
        return max(0, int((b - a).total_seconds()))
    return ""

# CSV
cols = ["databaseId", "workflowName", "event", "headBranch", "headSha", "status",
        "conclusion", "createdAt", "startedAt", "updatedAt", "duration_s"]
with open(os.path.join(out, "ci-runs.csv"), "w", newline="", encoding="utf-8") as fh:
    w = csv.writer(fh)
    w.writerow(cols)
    for r in runs:
        w.writerow([r.get("databaseId"), r.get("workflowName"), r.get("event"),
                    r.get("headBranch"), (r.get("headSha") or "")[:12], r.get("status"),
                    r.get("conclusion"), r.get("createdAt"),
                    r.get("startedAt"), r.get("updatedAt"), dur(r)])

# Duplicate-SHA analysis: same headSha with >1 run (any workflow/event).
by_sha = {}
for r in runs:
    by_sha.setdefault(r.get("headSha"), []).append(r)
dup = {sha: rs for sha, rs in by_sha.items() if sha and len(rs) > 1}

# Same SHA seen under BOTH push and pull_request (the CI-PRINCIPLE-06 anti-pattern).
push_pr_dupes = []
for sha, rs in by_sha.items():
    events = {r.get("event") for r in rs}
    if sha and "push" in events and "pull_request" in events:
        push_pr_dupes.append(sha)

durs = [int(dur(r)) for r in runs if dur(r) != ""]
durs.sort()
def pct(p):
    if not durs:
        return None
    return durs[min(len(durs) - 1, int(p * len(durs)))]
median = durs[len(durs) // 2] if durs else None
total_dur = sum(durs)

events = {}
concl = {}
for r in runs:
    events[r.get("event")] = events.get(r.get("event"), 0) + 1
    concl[r.get("conclusion") or "in_progress"] = concl.get(r.get("conclusion") or "in_progress", 0) + 1

with open(os.path.join(out, "duplicate-sha-report.md"), "w", encoding="utf-8") as fh:
    fh.write("# CICD-CTRL-1 — Baseline Duplicate-SHA Report\n\n")
    fh.write("Derived from `ci-runs.json` (read-only GitHub Actions run history). "
             "Runner-minute billing is not exposed by the runs API; see `runtime-summary.md` "
             "(APPROXIMATE FROM RUN DURATION).\n\n")
    fh.write(f"- Total runs analysed: **{len(runs)}**\n")
    fh.write(f"- Distinct head SHAs: **{len(by_sha)}**\n")
    fh.write(f"- SHAs with more than one run: **{len(dup)}**\n")
    fh.write(f"- SHAs run under BOTH `push` and `pull_request` (CI-PRINCIPLE-06 anti-pattern): "
             f"**{len(push_pr_dupes)}**\n\n")
    if dup:
        fh.write("## Multi-run SHAs\n\n| head SHA | runs | workflows | events | conclusions |\n")
        fh.write("|----------|------|-----------|--------|-------------|\n")
        for sha, rs in sorted(dup.items(), key=lambda kv: -len(kv[1])):
            wf = ",".join(sorted({r.get("workflowName") or "" for r in rs}))
            ev = ",".join(sorted({r.get("event") or "" for r in rs}))
            cc = ",".join(sorted({r.get("conclusion") or "?" for r in rs}))
            fh.write(f"| `{(sha or '')[:12]}` | {len(rs)} | {wf} | {ev} | {cc} |\n")
    else:
        fh.write("No head SHA has more than one run in the analysed window.\n")

with open(os.path.join(out, "workflow-trigger-map.md"), "w", encoding="utf-8") as fh:
    fh.write("# CICD-CTRL-1 — Baseline Workflow / Trigger Map\n\n")
    fh.write("Observed events across the analysed run window (authoritative source: `ci-runs.json`).\n\n")
    fh.write("| event | run count |\n|-------|-----------|\n")
    for e, n in sorted(events.items(), key=lambda kv: -kv[1]):
        fh.write(f"| `{e}` | {n} |\n")
    fh.write("\n| conclusion | run count |\n|------------|-----------|\n")
    for c, n in sorted(concl.items(), key=lambda kv: -kv[1]):
        fh.write(f"| `{c}` | {n} |\n")

with open(os.path.join(out, "runtime-summary.md"), "w", encoding="utf-8") as fh:
    fh.write("# CICD-CTRL-1 — Baseline Runtime Summary\n\n")
    fh.write("**Duration metrics are APPROXIMATE FROM RUN DURATION** (updatedAt − startedAt). "
             "GitHub does not expose billed runner minutes on the public runs API.\n\n")
    fh.write(f"- Runs analysed: **{len(runs)}**\n")
    fh.write(f"- Runs with a measurable duration: **{len(durs)}**\n")
    fh.write(f"- Median duration: **{median if median is not None else 'n/a'} s**\n")
    fh.write(f"- p90 duration: **{pct(0.9) if durs else 'n/a'} s**\n")
    fh.write(f"- Max duration: **{max(durs) if durs else 'n/a'} s**\n")
    fh.write(f"- Sum of measured durations (approx runner-seconds): **{total_dur} s "
             f"(~{round(total_dur/60)} min)**\n\n")
    fh.write("Per-event counts:\n\n")
    for e, n in sorted(events.items(), key=lambda kv: -kv[1]):
        fh.write(f"- `{e}`: {n}\n")

print(f"OK: audited {len(runs)} runs; {len(dup)} multi-run SHAs; "
      f"{len(push_pr_dupes)} push+PR duplicate SHAs")
PY

echo "== Baseline audit evidence written to $OUT =="
ls -1 "$OUT"
