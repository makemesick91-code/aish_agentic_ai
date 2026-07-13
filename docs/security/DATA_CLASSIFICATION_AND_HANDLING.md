# Data Classification and Handling — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §36, §37, §43 · PRD v1.2.0 §14 · **Rules:** `.claude/rules/04`, `07`, `18`, `20` ·
**ADR:** [0029](../decisions/adr/0029-data-classification-retention-export-deletion.md).

## Classes and handling
| Class | Meaning | AI input | Public output | Logging | Retention |
|-------|---------|----------|---------------|---------|-----------|
| CFG | configuration | allowed (no secrets) | n/a | allowed | tenant lifetime |
| PII | personal data | only if minimized + redacted | never disclose | redact | configurable; deletable |
| FIN | financial (billing) | no | never | redact | statutory |
| PUB | public content (reviews/replies) | yes (post-redaction) | yes, human-approved | allowed | tracked |
| CRED | credentials | never | never | never | encrypted; rotate |
| AUD | audit | never | never | itself immutable | configurable, non-deletable |
| AIX | AI trace | derived, redacted | never | redacted | configurable |
| KB | tenant knowledge | tenant-scoped | via approved reply | allowed | tenant lifetime |
| mixed | derived rows spanning >1 class (e.g. outbox, read-models, exports) | only after per-field redaction | never raw | redact to strictest constituent class | strictest constituent class |
| **MED** | **medical/clinical** | **PROHIBITED** | **PROHIBITED** | **PROHIBITED** | **not stored** |

`mixed`-class rows (outbox/dead_letters, report_read_models, exports) are handled at the **strictest constituent
class**: any PII is minimized/redacted, no `MED` is ever present, and retention follows the strictest field.

## Prohibited medical data (`MED`, `.claude/rules/18`)
Diagnosis, clinical notes, medical record number, prescription/medication, odontogram, clinical photos/scans,
treatment-plan narrative, treatment history, insurance details, payment-card/bank data, unredacted internal
incident notes. These are **never** stored, sent to an AI provider, or placed in public output. Any exception
requires privacy/security review, lawful basis, minimization, and a Master Source update. See
[PILOT_DATA_BOUNDARY](PILOT_DATA_BOUNDARY.md).

## Minimization & redaction
PII is minimized at capture; a redaction layer (ADR 0024) strips secrets/PII/`MED` before logging or AI input.
Free-text feedback and reviews are **untrusted input** (ADR 0019).

## Export & deletion (ADR 0029)
Export and deletion are first-class, tenant-scoped, audited Audit-module capabilities. Audit history is
append-only. Tenants can disconnect Google and delete credentials.

## Assertion
No real customer PII or medical data is stored in the repository; classification is a **planned** control.
