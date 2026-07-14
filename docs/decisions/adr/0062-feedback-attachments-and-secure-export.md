# ADR 0062 — Feedback Attachments and Secure Export Architecture

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 8 Feedback Operations Foundation; feedback capability IN PROGRESS toward GO
- **Owner:** Principal Architect / Application Security Engineer
- **Rule:** `.claude/rules/33`, `.claude/rules/04`, `.claude/rules/07` · **Canonical:** Master Source §47, §43; PRD v1.3.0 §10.7, §10.8, §14 (FR-FBK-*); rules 33, 04, 07, 30

## Context
Feedback operations require attaching evidence to an item and exporting a tenant's feedback for offline analysis.
Both are classic breach surfaces. Attachments can become a public-URL leak, a stored-XSS/malware vector via a spoofed
content type, or a path-traversal write outside the tenant prefix. Export can leak another tenant's or another
branch's data to whoever holds a link, can smuggle a CSV formula-injection payload into a spreadsheet, and can become
an unmetered/un-entitled bulk drain. The failure modes to prevent: public attachment disks, extension-only MIME
trust, cross-tenant/cross-branch export download, an unauthenticated or non-requester-scoped download, a formula
payload in an exported cell, and export that bypasses entitlement/usage.

## Decision
- **Private, tenant-prefixed attachments.** Feedback attachments are stored on a **private** disk under a
  `tenants/{tenant_id}/feedback/{item_id}/...` prefix; there is **no** public disk and no public listing. Filenames
  are system-generated; user-supplied names are sanitized and never used as a path segment (path traversal
  prevented). MIME is validated by **content inspection** against an allowlist (not by extension or the client-sent
  `Content-Type`); a disallowed type is refused. Removal is a **remove-state** (soft) transition recorded on the
  timeline — an attachment record is not silently hard-deleted.
- **Queued secure export.** Export is an asynchronous `App\Jobs\Feedback\GenerateFeedbackExportJob` that writes a CSV
  to a **private, expiring** location; it is **entitlement-gated** (via the single authoritative resolver, ADR 0055 /
  rule 31) and **metered** as tenant-scoped idempotent usage. The export captures the requester, tenant, branch scope,
  and applied filters.
- **Requester-scoped download.** The download route re-authorizes the **requesting user** (ownership of the export
  record) and re-checks tenant/branch/content scope at download time; another user — even another member of the same
  tenant — cannot fetch someone else's export, and a branch-restricted requester's export contains only their branch's
  data. Links are private and expiring, never public.
- **CSV formula-injection guard.** Every exported cell whose value begins with `=`, `+`, `-`, `@`, tab, or CR is
  neutralized (prefixed/quoted) so a spreadsheet cannot execute it; delimiters/quotes are properly escaped.
- **Content minimization.** Exports and attachment metadata carry minimized fields; no secret, token, or unrelated
  tenant data; free-text content is included only for users with the content-view permission and only within their
  scope.
- **Placement.** Attachment/export code is platform-core in `App\Feedback\*` + `App\Jobs\Feedback\*` (not
  `app/Modules/`).

## Alternatives
- **Public attachment URLs / public disk** — rejected: a guessable or shared URL leaks tenant data; private disk with
  an authorized, scoped download is mandatory.
- **Extension/`Content-Type`-based MIME check** — rejected: trivially spoofable; content-based sniffing against an
  allowlist is required.
- **Synchronous inline export** — rejected: unbounded work on the request path; a queued job with metering and a
  scoped download is safer and observable.
- **Raw CSV writing** — rejected: enables formula injection; every cell is neutralized.
- **A single shareable export link** — rejected: turns a link into cross-tenant access; the download must
  re-authorize the requester.

## Consequences
Attachments stay private, type-validated, and traversal-proof; exports never leave the requester's tenant/branch
scope, never execute in a spreadsheet, and never bypass entitlement/usage; and every attachment/export change is on
the audit timeline.

## Impacts
- **Security:** private disk only, content-based MIME allowlist, path-traversal prevention, requester-scoped
  re-authorized download, CSV-injection neutralization, entitlement gate on export.
- **Privacy:** free-text/attachment content is confined to content-view-authorized users within scope; export
  fields minimized; no secret/token in any artifact.
- **Tenant isolation:** attachment paths and export scope are fail-closed tenant/branch-scoped; no cross-tenant read
  or download.
- **Database:** adds `feedback_attachments` (private path, validated MIME, remove-state) and `feedback_exports`
  (requester, scope, filters, expiry, usage link) tables.
- **Operational:** queued export with bounded retry and expiry; truthful export states (`queued/processing/ready/
  failed/expired`); metered idempotently (a retry does not double-count).
- **Cost:** negligible; local private storage and a queued CSV writer, no external service.

## Verification / fitness function
`tests/Feature/Feedback/FeedbackAttachmentTest.php`, `FeedbackExportTest.php`,
`tests/Feature/Security/Sf08CrossTenantMatrixTest.php`, `tests/Feature/Audit/Sf08AuditTest.php`,
`tests/Architecture/Sf08BoundariesTest.php`, `tests/Feature/Sf08MigrationIntegrityTest.php` assert private
tenant-prefixed attachments, content-based MIME validation, path-traversal refusal, requester-scoped export download,
CSV formula-injection neutralization, and entitlement/metering on export. AFR-197, AFR-198, AFR-202..AFR-206.

## Related
Requirement: Master Source §47, §43; PRD v1.3.0 §10.7, §10.8, §14. Rules: 33, 04, 07, 30, 31. ADRs: 0004, 0012, 0015,
0055, 0060, 0061.

## Evidence
`app/Feedback/FeedbackAttachmentService.php`, `app/Jobs/Feedback/GenerateFeedbackExportJob.php`,
`app/Feedback/Export/*`, `app/Http/Controllers/Tenancy/Feedback/*`, `app/Models/{FeedbackAttachment,FeedbackExport}.php`,
`database/migrations/2026_07_15_*`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-8/`.

## Non-claims
Does not implement virus scanning, an external object store/CDN, image processing, or a production email/download
provider (all NOT STARTED); does not expose a public attachment or export URL; does not claim deployment/pilot/
production readiness.

## Rollback
Private tenant-prefixed attachments, content-based MIME validation, path-traversal prevention, requester-scoped
re-authorized export download, CSV formula-injection neutralization, and entitlement-gated metered export are
permanent; loosening any requires an owner-approved Master Source update.
