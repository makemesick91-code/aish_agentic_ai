# app/Shared/ — domain Shared Kernel (NOT STARTED)

Planned minimal Shared Kernel (tenant/branch context, event envelope, id/correlation, result/error, base policy).
It MUST stay tiny and depend on **no** module (fitness function FF-MOD-04, enforced by
`tests/Architecture/FoundationBoundariesTest`). No Shared Kernel code exists yet.

Note: framework/runtime glue added in Step 5 (health, preflight, runtime smoke) lives under `app/Support/`, not
here — the Shared Kernel is reserved for domain primitives.

**Shared Kernel implementation: NOT STARTED.**
