<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * `php artisan aish:heartbeat` — the foundation scheduler task. It records that
 * the scheduler ran and has NO business effect (rule 02 — no fake scheduled
 * business tasks). Scheduled in routes/console.php with overlap protection.
 */
final class HeartbeatCommand extends Command
{
    protected $signature = 'aish:heartbeat';

    protected $description = 'Runtime scheduler heartbeat (foundation only; no business effect).';

    public const MARKER = 'aish:heartbeat:last';

    public function handle(): int
    {
        $timestamp = now()->toIso8601String();
        Cache::put(self::MARKER, $timestamp, 3600);
        Log::info('aish.heartbeat', ['ts' => $timestamp]);
        $this->info('heartbeat ok @ '.$timestamp);

        return self::SUCCESS;
    }
}
