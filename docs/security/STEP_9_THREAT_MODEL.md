# Step 9 — Agentic Experience OS Security & Privacy Threat Model

**Status:** SECURITY ARCHITECTURE BASELINE — design (controls for un-built domains are NOT STARTED)
**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Related:** rule 34; rules 03, 04, 05, 06, 07, 18, 30, 31, 32, 33; `docs/security/THREAT_MODEL_BASELINE.md`
(Step 3, preserved); ADRs 0063–0068
**Canonical repo:** makemesick91-code/aish_agentic_ai

---

## 1. Scope and method

This threat model covers the Experience OS expansion (Customer 360, Recovery, Reputation, Conversations, AI). For each
mandatory vector it states: **Prevention** (control), **Detection** (signal), **Evidence** (where proof lives), and
**Recovery** (response). Controls marked *(implemented)* exist today; controls marked *(design)* are binding
requirements on the step that builds the capability. This model refines the Step 3
`docs/security/THREAT_MODEL_BASELINE.md`; the permanent invariants there remain in force.

**Decision-priority invariant (Master Source §57):** security, tenant isolation, and privacy outrank convenience,
automation, and features.

---

## 2. Threat register

| # | Threat | Prevention | Detection | Evidence | Recovery |
|---|--------|-----------|-----------|----------|----------|
| T-01 | Cross-tenant data leakage | Fail-closed immutable tenant context; row-level `tenant_id`; tenant-namespaced cache; tenant-context job envelope; per-tenant storage prefix *(implemented)* | Cross-tenant anomaly alert (design); isolation test matrix | `tests/Feature/Tenancy/*`, `tests/Feature/Security/Sf0*CrossTenantMatrixTest.php` | Block release (NO-GO); revoke, audit, patch |
| T-02 | Cross-branch unauthorized access | Branch scope on branch-relevant records; branch-restricted role sees only its branch *(implemented)*; new domains carry `branch_id` where source is branch-scoped *(design)* | Branch-scope tests; access anomaly | `app/Tenancy/`, feedback branch scope `app/Feedback/Support/FeedbackBranchScope.php` | Deny, audit, correct scope |
| T-03 | IDOR / broken access control | ULID public keys (not sequential ids); policy + service-layer checks (defense in depth); requester-scoped export download *(implemented)* | Authorization tests; 403 monitoring | `app/Policies/`, export re-auth in `app/Feedback/Export/` | Deny, audit, fix policy |
| T-04 | Customer identity poisoning | Deterministic links only on normalized verified identifiers; probabilistic matches are suggestions, never auto-applied; provenance recorded *(design)* | Identity-link confidence distribution; suggestion-acceptance audit | `docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md` | Reject link; human review |
| T-05 | Incorrect identity merge | Human-approved merge/split only; **no silent destructive merge**; reversible; immutable merge/split audit *(design)* | Merge/split audit anomaly alert | `customer_merge_events` ledger (design); AuditLog | Split/rollback; audit |
| T-06 | PII leakage | PII minimization + classification; redaction; no PII in logs/audit/notifications; sanitized audit metadata *(implemented for current domains)* | Secret/PII scan; log inspection | `scripts/docs/secret-scan.sh`; audit sanitization tests | Purge, rotate, notify |
| T-07 | Healthcare (MED) disclosure | MED data never sent to AI or public output; never in identity records; private-channel routing for sensitive cases *(rule 18)* | Guardrail failure alert (design); review of AI/public payloads | rule 18; AI guardrail contract | Block publish; incident |
| T-08 | Prompt injection | Customer content is untrusted and cannot steer tool calls; input guardrails; tool allowlist; structured output *(design; rule 05)* | Guardrail result log; injection eval dataset | AI tool-permission architecture; `docs/ai/AI_EVALUATION_BASELINE.md` | Reject action; kill switch |
| T-09 | AI tool abuse | Tool-level permission; least privilege; tenant/branch scope; forbidden-action allowlist; cost ceiling *(design)* | Tool-call trace; cost anomaly | Trace + `UsageRecord` | Kill switch; revoke tool |
| T-10 | Unauthorized public reply | Every Google reply requires recorded human approval; no auto-publish without §16.4 preconditions *(rules 06/18)* | Approval audit; publish-without-approval alert | rule 06; Human Approval Matrix | Block publish; audit |
| T-11 | Webhook forgery | Per-tenant signature verification; reject unsigned/mismatched *(design)* | Signature-failure rate | Channel adapter contract | Reject; rotate secret |
| T-12 | Replay attacks | Timestamp + nonce window; idempotency keys; dedupe *(design)* | Duplicate-detection metric | Adapter/ledger idempotency | Drop duplicate; audit |
| T-13 | Queue tenant-context loss | Validated tenant envelope on every job; context cleared between jobs; retry never switches/drops tenant *(implemented)* | Queue context tests; worker log audit | rule 30; `tests/Feature/Tenancy/*` | Fail closed; requeue safely |
| T-14 | Cache scope leakage | Tenant-namespaced cache keys; no broad flush as behavior *(implemented)* | Cache-key tests | rule 30 | Invalidate; fix key |
| T-15 | File / attachment abuse | Private tenant-prefixed disk; content-based MIME allowlist; no public disk; no path traversal *(implemented)* | Upload rejection metrics | `app/Feedback/FeedbackAttachmentService.php` | Reject; quarantine |
| T-16 | Audit tampering | Append-only audit + timeline; no `updated_at`; update/delete blocked at model layer *(implemented)* | Immutability tests | `app/Models/AuditLog.php`, `FeedbackEvent.php` | Restore from backup; incident |
| T-17 | Secret leakage | Secrets never committed; encryption at rest; per-environment secrets; secret scan gate *(implemented)* | `scripts/docs/secret-scan.sh`; push protection | `docs/evidence/validation/secret-scan.log` | Rotate; revoke; audit |
| T-18 | Cost abuse | Per-tenant/agent/run cost ceilings; usage metering; rate limits *(design)* | AI cost alert; usage spikes | `UsageRecord`; cost ceiling | Kill switch; throttle |
| T-19 | Duplicate external action | Idempotency keys on external side effects; transactional outbox; no success before provider verification *(design; ADR 0016/0017)* | Duplicate-action metric | Outbox/idempotency tables | Compensate; reconcile |
| T-20 | Stale approval | Approvals bound to a specific content version + expiry; re-check before execution *(design)* | Approval-age check | Approval records | Re-request approval |
| T-21 | Provider moderation mismatch | Truthful `moderation`/`unknown` states; reconcile with provider; never claim published before confirmation *(design)* | State reconciliation report | Adapter/reputation state | Reconcile; re-notify |
| T-22 | Data retention / deletion conflict | Erasure = tombstone + PII purge, keep minimized counts; legal hold overrides purge; retention configurable *(design)* | Deletion audit; retention report | Retention policy; `data_deletion_requests` (planned) | Reconcile; document exception |

---

## 3. Standing invariants preserved

- Zero cross-tenant exposure is a hard release gate; a breach is NO-GO until fixed and retested (rule 19).
- 100% human-approved public replies; no PII/medical leakage in public output; no external success before provider
  verification; no duplicate external action from retry (rule 18/19).
- Manual workflow works without AI; kill switch exists (rule 05/17).
- Security suspension precedence over commercial state (rule 31).

## 4. Out of scope for Step 9

No new security control is implemented in Step 9; this is the threat contract each Wave-1+ step verifies against with
its own security-review vectors and evidence.
