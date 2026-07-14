<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use App\Support\Runtime\Jobs\RuntimeSmokeJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class QueueSmokeJobTest extends TestCase
{
    public function test_smoke_job_is_dispatched_to_the_queue(): void
    {
        Queue::fake();

        RuntimeSmokeJob::dispatch('abc');

        Queue::assertPushed(RuntimeSmokeJob::class, fn (RuntimeSmokeJob $job) => $job->token === 'abc');
    }

    public function test_smoke_job_handle_writes_the_processed_marker(): void
    {
        // With QUEUE_CONNECTION=sync (test env) dispatch runs the handler inline,
        // proving dispatch + processing end-to-end without external services.
        RuntimeSmokeJob::dispatch('xyz');

        $this->assertSame('processed', Cache::get(RuntimeSmokeJob::MARKER_PREFIX.'xyz'));
    }
}
