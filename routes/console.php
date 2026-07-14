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
