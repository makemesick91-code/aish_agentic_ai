<?php

declare(strict_types=1);

namespace App\Support\Runtime\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Foundation-only smoke job: proves the queue can dispatch AND a worker can
 * process a job end-to-end (Step 5 acceptance). It writes a controlled cache
 * marker and has NO business side effect. It is NOT a product feature and MUST
 * NOT be extended into agent/business work here (rule 05, rule 02).
 */
final class RuntimeSmokeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const MARKER_PREFIX = 'aish:queue-smoke:';

    public function __construct(public readonly string $token) {}

    public function handle(): void
    {
        Cache::put(self::MARKER_PREFIX.$this->token, 'processed', 600);
    }
}
