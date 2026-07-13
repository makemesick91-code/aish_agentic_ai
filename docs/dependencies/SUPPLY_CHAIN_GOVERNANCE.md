# Supply-Chain Governance — Aish Agentic AI (Step 4)

**Title:** Step 4 Software Supply-Chain Governance
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Controls are planned; none is executing yet.
**Rule refs:** `.claude/rules/25` (dependencies & supply chain), `.claude/rules/04` (secrets), `.claude/rules/15` (tool safety), `.claude/rules/09` (gates).
**Canonical:** Master Source v2.4.0 §68 (dependency governance), §34, §43 (security controls); PRD v1.3.0.
**AFR refs:** AFR-095, AFR-096, AFR-097, AFR-098.
**Point-in-time research date:** 2026-07-13. **Evidence:** [`../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`](../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md).

## Non-claims

- Nothing is installed, provisioned, or scanned yet; no lock file, no SBOM artifact exists.
- The controls below are the **planned** supply-chain governance for when implementation begins.
- Version/registry facts are point-in-time research (2026-07-13) and re-verified at implementation.

## Purpose

Guarantee that every third-party artifact entering the Aish Agentic AI codebase comes from a verified source, is named correctly, is reviewed and scanned, is recorded in an SBOM, and is patched on a defined cadence. This complements the [Dependency Approval Matrix](DEPENDENCY_APPROVAL_MATRIX.md) and the [Upgrade & Security-Patch Policy](UPGRADE_AND_SECURITY_PATCH_POLICY.md).

## Control catalog

| Control | Rule | Description | Gate |
|---------|------|-------------|------|
| SC-1 Official registry only | MUST | PHP packages from Packagist via the **official registry** only; JS from the official npm registry; OS packages from official distro repos. No arbitrary VCS/URL sources without a governed exception. | Composer/npm config review |
| SC-2 Package-name verification | MUST | Each package name is verified against its official source before first use; vendor/namespace confirmed (e.g. `laravel/`, `spatie/`, `league/`). | Approval matrix step 2 |
| SC-3 Typosquat prevention | MUST | Guard against **typosquat** and dependency-confusion: confirm exact vendor/package spelling, reject look-alike names, prefer scoped/vendor-qualified names, and never install a suggested "did you mean" package without verification. | Name-verification checklist |
| SC-4 Lock-file review | MUST | `composer.lock` / `package-lock.json` are committed and every change is reviewed; unexpected transitive additions or source changes block the PR. | Lock-file diff review |
| SC-5 Dependency review | MUST | New/updated dependencies get a human review: purpose, license, maintainer, popularity/health, release recency, open advisories. | PR review |
| SC-6 Vulnerability scan | MUST | `composer audit` and an npm audit run in CI and locally; known-vulnerable versions block merge until remediated or risk-accepted with evidence. | CI security gate |
| SC-7 SBOM | MUST | A **SBOM** (CycloneDX or SPDX) is generated from the lock files at build/release and stored as evidence; it lists every direct and transitive component with version and license. | Release evidence |
| SC-8 Update cadence | MUST | Dependencies follow the cadence in the upgrade policy; security updates are expedited. | Scheduled review |
| SC-9 Emergency patch process | MUST | Critical advisories trigger the expedited patch path in the upgrade policy (out-of-cadence, documented, tested, evidenced). | Incident/upgrade policy |
| SC-10 Abandoned-package handling | MUST | Packages with no maintenance, unpatched advisories, or archived status are flagged and scheduled for replacement/removal. | Maintenance review |
| SC-11 License compliance | MUST | Only OSI/permissive licenses approved for implementation; restrictive/source-available licenses (e.g. Redis RSALv2/SSPL) flagged with a replacement option (Valkey). | Approval matrix |
| SC-12 No secrets in manifests | MUST | No credentials/tokens in `composer.json`, `package.json`, lock files, or private-registry config committed to the repo (`.claude/rules/04`). | Secret scan |
| SC-13 Pinned installers | MUST | Installer scripts and CI toolchains are pinned to versions/digests; no `curl | bash` of unpinned installers. | CI review |
| SC-14 Integrity verification | SHOULD | Rely on Composer/npm integrity hashes in lock files; verify checksums for out-of-registry artifacts. | Lock-file review |

## Official-source registry policy

- **PHP:** the **official registry** Packagist is the only default source; the exact vendor/package name from the [Dependency Approval Matrix](DEPENDENCY_APPROVAL_MATRIX.md) is used. Private/mirror registries require a governed exception recorded in the decision log.
- **JavaScript (build tooling):** the official npm registry only; scoped names verified.
- **OS/runtime:** official Ubuntu LTS repositories and official upstream repos (php.net, postgresql.org, redis.io/valkey.io, nginx.org, nodejs.org).
- Any deviation from an official source is a governed exception with owner approval and evidence.

## Typosquat and dependency-confusion defense

1. Verify the exact vendor and package name against the official source page before first install (SC-2).
2. Reject visually similar names, hyphen/underscore swaps, and homoglyph look-alikes (**typosquat** defense).
3. Prefer vendor-qualified names and avoid unscoped generic names that collide with internal package names (dependency confusion).
4. Do not act on registry "suggestions" or auto-corrections without independent verification.
5. Internal/private package names are never published to public registries and are namespaced to avoid confusion.

## Review and scanning pipeline (planned)

| Stage | Action | Evidence |
|-------|--------|----------|
| Propose | Approval-matrix entry (purpose, source, license, status) | Matrix row |
| Verify | Name/source verification, typosquat check | Verification note |
| Add | Pin direct dependency; commit lock file | Lock-file diff |
| Review | Dependency + lock-file review in PR | PR review record |
| Scan | `composer audit` / npm audit in CI | CI log |
| SBOM | Generate CycloneDX/SPDX **SBOM** | SBOM artifact |
| Release | Vulnerability + license + SBOM gate | Release evidence |

## SBOM policy

- An **SBOM** is generated from the authoritative lock files, not from source guesses, so it reflects exactly what would be installed.
- It records component, version, license, and source for direct and transitive dependencies.
- It is regenerated on every dependency change and attached to release evidence; it is a prerequisite for a production release gate.
- The SBOM MUST NOT contain secrets or private credentials.

## Abandoned-package handling

- A package is "at risk" when it has no release in a long window, unresolved critical advisories, an archived repository, or a sole unresponsive maintainer.
- At-risk packages are flagged in the approval matrix, a replacement option is identified, and a migration is scheduled per the upgrade policy.
- If no safe version exists, the package is pinned to the last-known-good version behind a documented risk decision until replaced.

## Roles and RACI

| Activity | Responsible | Accountable | Consulted |
|----------|-------------|-------------|-----------|
| Name/source verification | Requesting engineer | Supply-Chain Architect | — |
| Lock-file review | Reviewer | Supply-Chain Architect | Security reviewer |
| Vulnerability triage | Security reviewer | Supply-Chain Architect | Product owner (if risk-accept) |
| SBOM generation | Release governance | Supply-Chain Architect | — |
| Abandoned-package decision | Supply-Chain Architect | Product owner | Security reviewer |

## Exception handling

- Any deviation (non-**official registry** source, unpinned installer, risk-accepted advisory, restrictive license) requires an owner-approved exception recorded in the decision log with a scope, expiry, and mitigation.
- Exceptions are time-boxed and re-reviewed; an expired exception blocks the release gate until renewed or resolved.
- No exception may weaken tenant isolation, secret handling, or the no-secrets-in-manifests control.

## Metrics (planned)

| Metric | Purpose |
|--------|---------|
| Open advisories by severity | Track outstanding vulnerability exposure |
| Mean time to patch (critical) | Measure emergency-patch responsiveness |
| Abandoned packages count | Track at-risk dependencies |
| Exceptions open / expired | Track governance debt |

## CI enforcement (planned)

- Dependency governance gates run in CI on PR and on `main`: lock-file presence and review, `composer audit`/npm audit, license check, SBOM generation, secret scan of manifests.
- Gates MUST NOT be skipped, weakened, or faked (`.claude/rules/09`).

## Status

Supply-chain governance documented: **official registry** policy, package-name and **typosquat** verification, lock-file and dependency review, vulnerability scanning, **SBOM** generation, update cadence, emergency patching, and abandoned-package handling. No lock file exists; nothing is installed or scanned yet. **PLANNING BASELINE — NOT IMPLEMENTED.**
