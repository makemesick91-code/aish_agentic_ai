# ADR 0072 — Customer Merge/Split Reversibility, Snapshots, and Human Approval

- **Status:** Accepted (2026-07-18, Asia/Makassar) — Step 10 Customer 360 Foundation implementation decision
- **Owner:** Principal Architect / Customer Profile & Identity Resolution
- **Rule:** `.claude/rules/36`, `.claude/rules/34`, `.claude/rules/07` · **Canonical:** Master Source §77, §75, §36, §37; PRD v1.3.0; rules 36, 34, 03, 04, 07, 18, 30

## Context
ADR 0064 requires merge and split to be human-approved, reversible, and immutably audited, with "a merge preserving both
source snapshots so a split can restore". It does not say what reversible *means* mechanically. The naive implementation
— delete the merged customer and repoint its rows — is irreversible in practice: once the losing customer row is gone,
its identity provenance, consent history, and original attribution cannot be reconstructed, so an incorrect merge
becomes permanent data loss. Merges are also the highest-blast-radius operation in Customer 360: a wrong merge exposes
one real person's history to another, so it must be impossible to perform silently, in bulk, or across a branch boundary
the actor cannot reach.

## Decision
- **A merge never deletes.** The non-surviving customer transitions to status `merged` and retains its row, its
  identities, and its consent history. It is excluded from default listings by the `merged` status, and reads of it
  redirect to the survivor via `merged_into_customer_id`. Nothing is destroyed, so reversal is always possible.
- **Links move; ownership history stays.** On merge, `customer_identities` and the additive `feedback_items.customer_id`
  references are repointed to the survivor. Each moved identity records `merged_from_customer_id` so a split knows
  exactly which rows to return — reversal is a precise inverse, never a guess.
- **The merge event carries a full before-snapshot.** `customer_merge_events` is append-only and stores a sanitized
  JSON snapshot of both customers' pre-merge state and the exact set of moved identity/feedback ids. A split reads the
  snapshot of the merge it reverses; it does not attempt to re-derive the split from current state, which may have moved
  on.
- **A split is a new forward event, not an erasure.** Reversing merge `M` appends a `split` row referencing `M` and
  restores the recorded links. The original `merge` row is never updated or deleted, so the audit trail reads as a
  truthful sequence of what happened, including the mistake.
- **Merge and split require human approval and the `customer.merge` permission**, and are refused when the actor cannot
  reach **both** customers' branch scopes. Probabilistic suggestions are only ever proposals — approving a suggestion is
  a distinct authorized action, and a suggestion never applies itself.
- **A merge is refused if either customer is already `merged`**, preventing chains that cannot be cleanly reversed;
  callers must reverse first, then re-merge.
- **Merge/split are single-pair operations.** There is no bulk merge endpoint in Step 10 — bulk identity mutation has no
  safe review story.
- **Concurrency is guarded** by locking both customer rows in a deterministic id order inside the transaction, so two
  simultaneous merges cannot interleave into a partially-merged state or deadlock.

## Alternatives
- **Hard-delete the merged customer and repoint rows** — rejected: irreversible; an incorrect merge becomes permanent
  data loss and destroys consent provenance.
- **Soft-delete via `deleted_at`** — rejected: `merged` is a real domain state with a survivor pointer, not a deletion;
  overloading soft-delete hides it from tenant-owned scopes and loses the survivor link.
- **Re-derive a split from current state instead of the snapshot** — rejected: post-merge activity would be
  misattributed on reversal; only the recorded moved-id set is exact.
- **Mutate/delete the original merge row on reversal** — rejected: breaks append-only audit and erases evidence that the
  incorrect merge occurred.
- **Auto-apply high-confidence probabilistic matches** — rejected by ADR 0064; identity poisoning risk with no human
  checkpoint.
- **Bulk merge tooling** — rejected for Step 10: no reviewable approval story at scale.

## Consequences
Every merge is fully reversible with exact restoration, and the ledger truthfully records both the merge and its
reversal. The cost is that merged customer rows are retained indefinitely (bounded, small, and required for
auditability) and that every customer read path must be `merged`-status aware — enforced by the default scope and
covered by tests.

## Impacts
- **Security:** `customer.merge` permission plus dual-branch reachability; no bulk path; deterministic row locking
  prevents interleaved partial merges.
- **Privacy:** snapshots are sanitized — they record ids, provenance, counts, and status, never raw email/phone, free
  text, or MED data; erasure of a merged customer still purges PII while retaining the non-PII ledger.
- **Tenant isolation:** merge is refused unless both customers share the current tenant; cross-tenant merge is
  structurally impossible (both rows are tenant-scoped and locked under the same fail-closed context).
- **Database:** `customers.status` gains `merged`; `customers.merged_into_customer_id` is a nullable self-FK;
  `customer_identities.merged_from_customer_id` is nullable; `customer_merge_events` is append-only (`created_at` only,
  update/delete blocked at the model layer) with a JSON snapshot column.
- **Operational:** an incorrect merge is recoverable by an authorized operator without a database restore; merge/split
  anomaly rates are observable per the Step 10 observability contract.
- **Cost:** negligible — retained merged rows are few and small; snapshots are bounded JSON.

## Verification / fitness function
`tests/Feature/Customer360/CustomerMergeSplitTest.php` covers merge repointing, snapshot completeness, exact split
restoration, no-delete invariant, merge-of-already-merged refusal, append-only ledger enforcement, permission and
dual-branch checks, and concurrent-merge safety. `tests/Feature/Security/Sf10CrossTenantMatrixTest.php` covers
cross-tenant and cross-branch merge refusal and merge IDOR. `php artisan aish:verify-step-10` re-proves reversibility on
real PostgreSQL 17. AFR-257, AFR-258, AFR-259, AFR-260, AFR-261.

## Related
Requirement: Master Source §77, §75, §36, §37; PRD v1.3.0;
`docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md` §4, §8, §12. Rules: 36, 34, 03, 04, 07, 18, 30. ADRs:
0064, 0068, 0070, 0071; 0011, 0012, 0029.

## Evidence
`app/Customers/CustomerMergeService.php`, `app/Models/CustomerMergeEvent.php`,
`tests/Feature/Customer360/CustomerMergeSplitTest.php`, `tests/Feature/Security/Sf10CrossTenantMatrixTest.php`;
`docs/security/STEP_10_THREAT_MODEL.md`; `docs/evidence/step-10/`.

## Non-claims
Implements no automatic merging, no bulk merge, no AI-assisted matching, and no cross-tenant identity resolution.
Suggestion scoring is deterministic and rule-based, not machine-learned. Claims no deployment, pilot, or production
readiness.

## Rollback
No-delete merges, exact snapshot-based reversibility, append-only merge/split ledger, mandatory human approval with
`customer.merge` plus dual-branch reachability, and the absence of a bulk merge path are permanent for Step 10+;
weakening any of them requires a new ADR and an owner-approved Master Source update that preserves reversibility and
auditability.
