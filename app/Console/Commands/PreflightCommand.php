<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Runtime\RuntimePreflight;
use Illuminate\Console\Command;

/**
 * `php artisan aish:preflight` — fail-fast validation that mandatory runtime
 * configuration is present. Used by scripts/preflight.sh and CI.
 */
final class PreflightCommand extends Command
{
    protected $signature = 'aish:preflight';

    protected $description = 'Validate mandatory Aish Agentic AI runtime configuration is present.';

    public function handle(RuntimePreflight $preflight): int
    {
        $problems = $preflight->problems();

        if ($problems !== []) {
            foreach ($problems as $problem) {
                $this->error('  - '.$problem);
            }
            $this->error('Preflight FAILED ('.count($problems).' problem(s)).');

            return self::FAILURE;
        }

        $this->info('Preflight OK — mandatory runtime configuration is present.');

        return self::SUCCESS;
    }
}
