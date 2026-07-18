#!/usr/bin/env bash
# check-step10-coverage.sh — Step 10 (Customer 360 Foundation) artifact, code, and governance coverage.
# Rule 36. Verifies the Step 10 artifacts, ADR/AFR integrity, version consistency, permanent-invariant
# coverage, and truthful (no-stale) status. Static/read-only; no repository mutation.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0

need_file() { if [ -s "$1" ]; then echo "OK: $1"; else echo "FAIL: missing/empty $1"; fail=1; fi; }
need_grep() { if grep -q "$2" "$1" 2>/dev/null; then echo "OK: $1 contains '$2'"; else echo "FAIL: $1 missing '$2'"; fail=1; fi; }
no_grep()  { if grep -q "$2" "$1" 2>/dev/null; then echo "FAIL: $1 must not contain '$2'"; fail=1; else echo "OK: $1 free of '$2'"; fi; }

echo "== Step 10 required governance artifacts =="
need_file .claude/rules/36-customer-360-identity-and-consent-foundation.md
need_file docs/security/STEP_10_THREAT_MODEL.md
need_file docs/quality/STEP_10_GO_WATCH_NO_GO.md
need_file docs/release/STEP_10_RELEASE_MANIFEST.md
need_file docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md

echo "== Step 10 required implementation artifacts =="
need_file app/Customers/CustomerIdentityResolver.php
need_file app/Customers/CustomerMergeService.php
need_file app/Customers/CustomerConsentService.php
need_file app/Customers/CustomerInteractionsReadModel.php
need_file app/Customers/CustomerEntitlements.php
need_file app/Customers/CustomerFeedbackLinker.php
need_file app/Customers/Identity/IdentityNormalizer.php
need_file app/Customers/Identity/IdentityHasher.php
need_file app/Customers/Support/CustomerBranchScope.php
need_file app/Models/Customer.php
need_file app/Models/CustomerIdentity.php
need_file app/Models/CustomerMergeEvent.php
need_file app/Models/CustomerConsent.php
need_file app/Policies/CustomerPolicy.php
need_file app/Http/Middleware/EnsureCustomer360Enabled.php
need_file app/Console/Commands/CustomerReconcileCommand.php
need_file app/Console/Commands/VerifyStep10Command.php
need_file scripts/runtime/verify-step-10.sh

echo "== Step 10 required test artifacts =="
need_file tests/Unit/Customers/IdentityNormalizerTest.php
need_file tests/Feature/Customer360/CustomerIdentityResolverTest.php
need_file tests/Feature/Customer360/CustomerMergeSplitTest.php
need_file tests/Feature/Customer360/CustomerConsentTest.php
need_file tests/Feature/Customer360/CustomerInteractionsReadModelTest.php
need_file tests/Feature/Customer360/CustomerHttpTest.php
need_file tests/Feature/Security/Sf10CrossTenantMatrixTest.php
need_file tests/Feature/Audit/Sf10AuditTest.php
need_file tests/Feature/Sf10MigrationIntegrityTest.php
need_file tests/Feature/Console/Sf10CommandsTest.php
need_file tests/Architecture/Sf10BoundariesTest.php

echo "== Step 10 additive migrations (no Step 8 alteration) =="
for t in create_customers_table create_customer_identities_table create_customer_merge_events_table \
         create_customer_consents_table add_customer_id_to_feedback_items; do
  m=( database/migrations/*_"${t}".php )
  if [ -e "${m[0]}" ]; then echo "OK: migration ${t} present"; else echo "FAIL: migration ${t} missing"; fail=1; fi
done
# The additive link migration must not perform a backfill (contract §5; ADR 0068).
LINK=( database/migrations/*_add_customer_id_to_feedback_items.php )
if [ -e "${LINK[0]}" ]; then no_grep "${LINK[0]}" "DB::table"; fi

echo "== Step 10 ADRs 0070..0072 =="
for n in 0070 0071 0072; do
  m=( docs/decisions/adr/${n}-*.md )
  if [ -e "${m[0]}" ]; then echo "OK: ADR ${n} present"; else echo "FAIL: ADR ${n} missing"; fail=1; fi
done

echo "== AFR-250..262 in catalog =="
AFR=docs/architecture/APPLICATION_FOUNDATION_RULES.md
for a in AFR-250 AFR-253 AFR-254 AFR-257 AFR-258 AFR-261 AFR-262; do need_grep "$AFR" "$a"; done
need_grep "$AFR" "13 Step 10"
need_grep "$AFR" "262 AFRs total"

echo "== Master Source v2.13.0 =="
MS=docs/canonical/MASTER_SOURCE.md
need_grep "$MS" '^\*\*Versi:\*\* 2\.13\.0'
need_grep "$MS" '^# 77\. STEP 10 — CUSTOMER 360 FOUNDATION'
need_grep "$MS" '^## Version 2\.13\.0'

echo "== PRD pinned at 1.3.0 (unchanged by Step 10) =="
need_grep docs/canonical/PRD.md '1\.3\.0'

echo "== Rule 36 permanent invariants =="
R36=.claude/rules/36-customer-360-identity-and-consent-foundation.md
need_grep "$R36" 'Merge \*\*MUST NOT\*\* delete'
need_grep "$R36" 'keyed hash bound to the tenant'
need_grep "$R36" 'ross-tenant identity linking is \*\*PROHIBITED\*\*'
need_grep "$R36" 'append-only'
need_grep "$R36" 'human approval'
need_grep "$R36" 'nonymous responses \*\*MUST NOT\*\* silently create'
need_grep "$R36" 'review gating remains \*\*prohibited\*\*'

echo "== Governance wiring =="
need_grep CLAUDE.md '36-customer-360-identity-and-consent-foundation.md'
need_grep CLAUDE.md 'v2.13.0'
need_grep AGENTS.md 'v2.13.0'
need_grep docs/governance/foundation-coverage-matrix.md '36-customer-360-identity-and-consent-foundation.md'
need_grep docs/decisions/DECISION_LOG.md 'D-036'
need_grep docs/decisions/VERSION_MATRIX.md '2\.13\.0'
need_grep .github/workflows/pr-ci.yml 'aish:verify-step-10'

echo "== Truthful status — Step 11 and later capabilities remain NOT STARTED =="
need_grep CLAUDE.md 'Step 11 — Customer Recovery OS'
need_grep "$MS" 'NOT STARTED'
# Step 10 must not be described as the "next" unstarted step anywhere authoritative.
no_grep CLAUDE.md 'Step 10 — Customer 360 Foundation (contract locked, \*\*NOT STARTED\*\*)'

if [ "$fail" -ne 0 ]; then echo "check-step10-coverage: FAILED"; exit 1; fi
echo "check-step10-coverage: PASS"
