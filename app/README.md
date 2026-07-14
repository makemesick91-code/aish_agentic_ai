# app/ — Laravel 12 application (Step 5 runtime foundation)

Step 5 bootstrapped the runtime here. Present today:

- `app/Http/` — health probe controllers and the `SecurityHeaders` middleware.
- `app/Console/Commands/` — `aish:preflight`, `aish:heartbeat`, `aish:queue-smoke` (foundation only).
- `app/Support/` — framework/runtime glue: health checks, readiness probe, runtime preflight, smoke job.
- `app/Modules/` — planned home of the 17 business modules. **Module implementation: NOT STARTED.**
- `app/Shared/` — planned minimal domain Shared Kernel. **NOT STARTED.**

Layout: [REPOSITORY_LAYOUT](../docs/architecture/REPOSITORY_LAYOUT.md);
boundaries: [MODULE_BOUNDARIES](../docs/architecture/MODULE_BOUNDARIES.md).

**Business/module implementation: NOT STARTED.** This is a runtime foundation, not a built product.
