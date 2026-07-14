# Repository Application Layout — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · Step 5 realized the runtime foundation dirs · **Business/module
implementation: NOT STARTED.**
**Canonical:** Master Source v2.6.0 §34, §36, §70 · **Rules:** `.claude/rules/08`, `20`, `29` · **ADRs:**
[0010](../decisions/adr/0010-repository-layout-and-module-boundaries.md),
[0047](../decisions/adr/0047-runtime-version-and-support-policy.md).

> Step 5 realized the runtime-foundation directories (`app/Http`, `app/Console`, `app/Support`, `bootstrap`,
> `config`, `routes`, `database`, `resources`, `tests`) with a bootable Laravel 12 runtime — see
> [runtime-bootstrap.md](runtime-bootstrap.md). The **business** layer (`app/Modules/*`, `app/Shared/`) remains a
> reserved scaffold: no module code, models, or business migrations exist yet.

## 1. Top-level layout
```text
app/
├── Modules/
│   ├── PlatformAdmin/   Identity/     Tenancy/      Billing/
│   ├── Customer/        ServiceEvent/ Survey/       Campaign/
│   ├── Feedback/        Recovery/     Reputation/   Knowledge/
│   ├── AI/              Notification/ Integration/  Analytics/
│   └── Audit/
└── Shared/              # minimal Shared Kernel (see §3)

bootstrap/  config/  database/  resources/  routes/  storage/
tests/
├── Architecture/  Unit/  Feature/  Integration/  Security/  Performance/
docs/  scripts/
```

## 2. Per-module internal layout (planned)
Each `app/Modules/<Module>/` owns its slice and exposes only its public interface:
```text
<Module>/
├── Providers/        # module service provider: bindings, routes, events, migrations path
├── Contracts/        # public interfaces other modules may depend on
├── Application/       # commands, queries, application services, DTOs
├── Domain/           # entities, value objects, domain events, policies
├── Infrastructure/   # repositories, query objects, external adapters
├── Http/             # controllers, form requests, API resources (module-local)
├── Jobs/             # queued jobs (carry tenant context)
├── Listeners/        # event listeners
├── Database/         # migrations (own tables only), factories (test-only)
├── Routes/           # web.php / api.php registered by the provider
├── Config/           # module config
├── resources/        # module Blade views/components (initial frontend)
├── Tests/            # module-scoped tests
├── AGENTS.md         # (added when scaffold is created)
└── README.md         # states scaffold marker + module contract link
```

## 3. Shared Kernel (`app/Shared/`)
Deliberately tiny. **MAY** contain: tenant/branch context primitives, base DTO/value-object types, event
envelope, correlation/id helpers, result/error types, base policy. **MUST NOT** become a helper dumping ground
or hold business logic owned by a module. Growth of the Shared Kernel requires an ADR note.

## 4. Registration & boundaries
- Each module is wired by its **service provider** (routes, listeners, migrations path, config).
- A module reads/writes **only its own tables**; cross-module data flows via application service, contract, or
  domain event (ADR 0010, 0016).
- No **undocumented** circular dependency (fitness function `FF-MOD-03`).
- Cross-module read models are explicit and tenant-scoped; reporting never bypasses tenant isolation (ADR 0015).

## 5. Scaffold policy (Step 3)
If any `app/`, `tests/`, or module directory is materialized in Step 3, it contains only `README.md`/`.gitkeep`
and `AGENTS.md` stating the scaffold marker. Commit messages and docs **MUST NOT** describe scaffold as a built
application. See [MODULE_BOUNDARIES](MODULE_BOUNDARIES.md).
