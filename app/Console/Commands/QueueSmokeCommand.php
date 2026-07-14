<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Runtime\Jobs\RuntimeSmokeJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * `php artisan aish:queue-smoke --token=X`  -> dispatch a smoke job.
 * `php artisan aish:queue-smoke --token=X --check` -> verify the worker
 * processed it (exit 0 = processed, 1 = not processed).
 *
 * Used by scripts/verify-runtime.sh to prove real dispatch + worker processing
 * against the configured (Redis) queue. Foundation only — not a product feature.
 */
final class QueueSmokeCommand extends Command
{
    protected $signature = 'aish:queue-smoke {--token= : Unique smoke token} {--check : Verify the job was processed}';

    protected $description = 'Dispatch (or verify) a runtime queue smoke job (foundation only; no business effect).';

    public function handle(): int
    {
        $token = (string) ($this->option('token') ?: 'default');

        if ($this->option('check')) {
            $processed = Cache::get(RuntimeSmokeJob::MARKER_PREFIX.$token) === 'processed';
            if ($processed) {
                $this->info("queue smoke token '{$token}' was processed.");

                return self::SUCCESS;
            }
            $this->error("queue smoke token '{$token}' was NOT processed.");

            return self::FAILURE;
        }

        RuntimeSmokeJob::dispatch($token);
        $this->info("dispatched queue smoke job with token '{$token}'.");

        return self::SUCCESS;
    }
}
