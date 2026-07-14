<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console scheduler
|--------------------------------------------------------------------------
|
| Foundation scheduler wiring for Step 5. Only the runtime heartbeat is
| scheduled — there are NO business/agent scheduled tasks yet (rule 02, rule 05).
| Overlap protection and single-server locking are declared now so future tasks
| inherit a safe default. Production cron: see docs/operations/runtime-verification.md.
|
*/

Schedule::command('aish:heartbeat')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// SPRINT-SF-05: idempotent subscription reconciliation (trial/period/grace expiry).
// Foundation-only; makes no payment assumption and is safe to rerun (rule 31 §9.8; ADR 0055).
Schedule::command('aish:subscription-reconcile')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
