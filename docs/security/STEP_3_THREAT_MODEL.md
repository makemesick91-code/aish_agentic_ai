# Step 3 Threat Model — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §43, §44, §50 · PRD v1.2.0 §15, §23 · **Rules:** `.claude/rules/04`, `05`, `18`, `20` ·
**ADR:** [0011](../decisions/adr/0011-shared-database-shared-schema-multi-tenancy.md), [0016](../decisions/adr/0016-domain-events-outbox-idempotency-retry-dead-letter.md), [0017](../decisions/adr/0017-public-api-and-webhook-contracts.md), [0019](../decisions/adr/0019-ai-provider-abstraction.md), [0022](../decisions/adr/0022-oauth-credential-encryption.md).

Each threat has asset · actor · entry point · attack path · preventive control · detective control · recovery ·
test · evidence. Controls are **planned**; no application code runs in Step 3.

| # | Threat | Asset | Preventive control | Detective | Recovery | Test / FF |
|---|--------|-------|--------------------|-----------|----------|-----------|
| T1 | Cross-tenant access | tenant data | context scope + tenant-leading constraints (ADR 0011/0012) | isolation-anomaly alert | revoke, patch, retest | FF-TEN-01 |
| T2 | Branch-scope bypass | branch data | branch policy + query re-scope (ADR 0013) | audit review | fix policy | FF-TEN-11 |
| T3 | IDOR | any record | opaque ids + scope check + uniform 404/403 (ADR 0017) | access logs | patch | FF-TEN-11 |
| T4 | Privilege escalation | permissions | least privilege + approval on high-risk (ADR 0013/0028) | audit | revoke role | FF-SEC-03 |
| T5 | Credential leakage | secrets | no-commit + secret scan + push protection (ADR 0025) | scan alerts | rotate secret | FF-SEC-01 |
| T6 | OAuth token theft | Google creds | encryption at rest + rotation (ADR 0022) | anomaly logs | rotate/disconnect | FF-SEC-02 |
| T7 | Prompt injection | AI behaviour | untrusted input + tool allowlist + guardrail (ADR 0019) | guardrail events | kill switch | FF-SEC-05 |
| T8 | Tool abuse | AI tools | validated args + allowlist (ADR 0019) | tool-call logs | kill switch | FF-AI-03 |
| T9 | Queue context loss | tenant scope | context in payload + rehydrate (ADR 0012) | job audit | reprocess | FF-TEN-03 |
| T10 | Cache-key collision | tenant data | prefixed keys (ADR 0015) | anomaly | flush/patch | FF-TEN-02 |
| T11 | Storage-path collision | files | scoped paths (ADR 0015) | audit | patch | FF-TEN-04 |
| T12 | Export leakage | exports | scoped + audited + expiring (ADR 0015/0029) | export audit | revoke link | FF-TEN-06 |
| T13 | Search leakage | index | tenant filter (ADR 0015) | query logs | patch | FF-TEN-05 |
| T14 | AI-retrieval leakage | KB/context | tenant filter + minimal context (ADR 0023) | retrieval logs | patch | FF-TEN-08 |
| T15 | Webhook forgery | inbound | signature + replay protection (ADR 0017) | security events | reject/rotate | FF-API-04 |
| T16 | Replay | external effect | idempotency + nonce (ADR 0016) | dup detection | dedupe | FF-REL-02 |
| T17 | Duplicate external action | provider effect | outbox + idempotency (ADR 0016) | reconciliation | idempotent replay | FF-REL-05 |
| T18 | Supply-chain compromise | dependencies | pinned + verified + minimal (ADR 0031) | audit | pin/rollback | FF-SEC-01 |
| T19 | Malicious file upload | storage | validation + scanning + scoped storage | upload logs | quarantine | FF-TEN-04 |
| T20 | Audit tampering | audit log | append-only, non-deletable (ADR 0024) | integrity check | restore backup | FF-TEN-13 |
| T21 | Secret exposure in logs | telemetry | redaction layer (ADR 0024) | scan | purge/patch | FF-TEN-14 |
| T22 | Medical/PII in public reply | patient privacy | human approval + MED exclusion (ADR 0028/0029) | approval audit | takedown | FF-SEC-03 |
| T23 | SSRF (server-side request forgery) | internal services | egress allowlist + validate/deny internal URLs in webhooks/integrations (ADR 0017/0021) | egress logs | block/patch | FF-API-04 |
| T24 | Rate-limit bypass / DoS | availability | per-tenant+credential rate limits + quotas (ADR 0017) | rate metrics | throttle/patch | FF-API-01 |
| T25 | CSRF / XSS / SQLi | web + data | framework CSRF tokens, output encoding, parameterized queries (Master Source §43) | WAF/logs | patch | FF-API-01 |

## Recovery & kill switch
A kill switch halts AI/external-effect classes without data loss (ADR 0016/0028). Incident handling and rollback:
[INCIDENT_AND_ROLLBACK_BASELINE](../operations/INCIDENT_AND_ROLLBACK_BASELINE.md).

## Assertion
All named threats have planned preventive + detective + recovery controls mapped to ADRs and fitness functions.
No exploit, control, or test executes in Step 3.
