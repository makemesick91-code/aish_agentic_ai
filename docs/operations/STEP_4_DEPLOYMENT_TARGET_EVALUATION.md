# Step 4 Deployment Target Evaluation — Aish Agentic AI

**Title:** Step 4 Deployment Target Evaluation
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Provider NOT selected (WATCH). Nothing is provisioned.
**Rule refs:** `.claude/rules/11` (observability/backup/operations), `.claude/rules/03` (tenant/branch isolation), `.claude/rules/04` (security/secrets), `.claude/rules/13` (release).
**Canonical:** Master Source v2.4.0 §51 (observability/ops), §54 (operational gate), §34 (stack); PRD v1.3.0.
**AFR refs:** AFR-095..098 (dependency/ops governance context).

## Non-claims

- Nothing is provisioned, deployed, or configured; **no provider is selected** (status WATCH).
- Provider/latency/cost facts are point-in-time research (2026-07-13) and re-verified at selection.
- This document evaluates provider **classes** only; it does not name a chosen vendor or region.

## Purpose

Define the recommended deployment target class and the hard isolation requirements for the pilot, and compare provider classes on the criteria that matter, so a provider can later be selected through a governed decision. This extends the Step 3 `ENVIRONMENT_AND_DEPLOYMENT_BASELINE.md` operations work.

## Recommended target class

- **Recommended class:** a **dedicated** Ubuntu LTS VM or an equivalently **isolated** compute instance dedicated to Aish Agentic AI.
- The pilot runs on compute that Aish Agentic AI controls end to end (OS, PHP-FPM, queue workers, Nginx, secrets), not shared application hosting.
- A managed-database and object-storage option MAY be layered on top, provided isolation and data-residency requirements below are met.

## Hard isolation requirement (pilot)

The pilot **MUST NOT**, by default, share any of the following with **DaengtisiaMS** or Aish POS:

| Shared resource | Requirement |
|-----------------|-------------|
| Database instance/schema | **separate** database (own instance or clearly isolated DB + credentials) |
| Redis namespace | **separate** Redis instance or a dedicated, non-overlapping key prefix |
| Application directory | **separate** deployment directory |
| PHP-FPM pool | **separate** PHP-FPM pool |
| Queue worker | **separate** worker process/supervisor |
| Object/file storage | **separate** bucket/prefix |
| Secrets | **separate** secret store / env files, never shared credentials |
| Deployment user | **separate** Unix user |

- These separations enforce tenant/branch isolation guarantees (`.claude/rules/03`) at the infrastructure boundary and prevent cross-application data leakage with DaengtisiaMS.

## Co-hosting exception (temporary only)

If Aish Agentic AI is temporarily co-hosted with **DaengtisiaMS** or Aish POS, it requires an **explicit risk decision** (owner-approved, recorded in the decision log) **and full separation** across every one of:

- **separate** database, **separate** Redis prefix, **separate** directory, **separate** Unix user, **separate** PHP-FPM pool, **separate** Nginx server block, **separate** secrets, **separate** backup, **separate** monitoring, **separate** rollback, **separate** resource limits, and **separate** port/domain isolation.

Co-hosting without full separation is prohibited; a partial-separation co-host is a NO-GO for the pilot.

## Provider-class comparison criteria

Providers are compared as **classes** (self-managed VM, managed VM + managed DB, container platform), not by named vendor, on:

| Criterion | What is evaluated |
|-----------|-------------------|
| Region / Indonesia latency | Proximity to Indonesian users (Asia/Makassar); measured latency at selection |
| Uptime / SLA | Published availability commitment |
| Cost | Predictable monthly cost for the pilot footprint |
| Backup | Native/managed backup capability |
| Snapshot | Instance/volume snapshot support |
| Firewall | Network firewall / security groups |
| Private networking | Private links between app, DB, and cache |
| Managed DB | Availability of managed PostgreSQL option |
| Object storage | S3-compatible storage availability |
| Monitoring | Built-in metrics/log integration |
| Support | Support responsiveness/tier |
| Data residency | Ability to keep data in an acceptable jurisdiction |
| Scalability | Vertical/horizontal scaling path |
| Exit strategy | Ease of migrating out (backups, standard formats) |
| Vendor lock-in | Degree of proprietary coupling |

## Provider-class evaluation (illustrative, not a selection)

| Class | Isolation fit | Managed DB | Data residency control | Lock-in | Notes |
|-------|---------------|-----------|------------------------|---------|-------|
| Self-managed dedicated Ubuntu LTS VM | High (full control of pools/users/dirs) | Self-managed | High (region choice) | Low | Recommended class; most control, most ops responsibility |
| Managed VM + managed PostgreSQL | High | Yes | Medium–High | Medium | Offloads DB backup/patching; verify residency |
| Container/orchestration platform | High if namespaced | Add-on | Medium | Medium–High | More moving parts; evaluate only if scaling need is proven |
| Shared application hosting | Low | Shared | Low | High | REJECTED for pilot (cannot guarantee isolation) |

- The table is an illustrative comparison of classes; it is **not** a provider selection.

## Environment topology (planned)

The authoritative six-environment model (`local, test, CI, staging, pilot, production`) is fixed by ADR 0034 and
`docs/environments/ENVIRONMENT_MATRIX.md` (canonical); **pilot and production are distinct environments** with
separate DB/Redis/queue/storage/secrets. The table below is only a **deployment-class** summary of where each
environment runs:

| Environment | Deployment class | Isolation |
|-------------|------------------|-----------|
| local | Developer machine | Fully local; synthetic data only; no production data |
| test | Local/CI ephemeral | Synthetic fixtures; disposable |
| CI | CI runner (ephemeral) | Synthetic fixtures; per-run isolation |
| staging | Dedicated/isolated instance | Separate DB/Redis/storage/secrets; synthetic or anonymized data; restore/rollback drills |
| pilot | Dedicated Ubuntu LTS VM / isolated compute | Dedicated; separate from DaengtisiaMS/Aish POS; minimum pilot data |
| production | Dedicated Ubuntu LTS VM / isolated compute | Dedicated; production controls; separate from all other products |

- Staging mirrors the pilot isolation model so drills are representative; **pilot and production remain separate**.
- No environment shares secrets or credentials with another; each has its own secret store. See
  `docs/environments/ENVIRONMENT_MATRIX.md` (authoritative) for the full per-environment matrix.

## Data residency and exit

- Data residency is a selection criterion; the pilot targets a jurisdiction acceptable for Indonesian customer data.
- An exit strategy is required before selection: standard PostgreSQL dumps, S3-compatible object exports, and portable config keep migration off the provider feasible.
- Vendor lock-in is minimized by preferring standard formats and self-managed or portable managed services.

## Selection gate (future, governed)

Before any provisioning:
1. Re-verify region/latency/cost/residency for candidate classes.
2. Confirm the hard isolation requirement can be met (separate DB, Redis, dir, user, pool, storage, secrets).
3. Record the choice as an owner-approved decision (decision log) with an exit strategy and lock-in assessment.
4. Only then provision; provisioning is out of scope for this planning document.

## Status

Deployment target evaluation documented: recommended a **dedicated**, **isolated** Ubuntu LTS VM class; fixed the hard **separate**-everything isolation requirement versus **DaengtisiaMS**/Aish POS; compared provider classes on latency/cost/backup/residency/lock-in. **Provider NOT selected (WATCH).** Nothing is provisioned or deployed. **PLANNING BASELINE — NOT IMPLEMENTED.**
