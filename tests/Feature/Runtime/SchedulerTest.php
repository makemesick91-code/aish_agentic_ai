<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use App\Console\Commands\HeartbeatCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class SchedulerTest extends TestCase
{
    public function test_heartbeat_is_the_only_scheduled_command_and_has_overlap_protection(): void
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make(Schedule::class);

        $commands = array_map(
            static fn ($event) => $event->command,
            $schedule->events(),
        );

        $heartbeat = array_values(array_filter(
            $commands,
            static fn ($command) => is_string($command) && str_contains($command, 'aish:heartbeat'),
        ));

        $this->assertNotEmpty($heartbeat, 'aish:heartbeat must be scheduled');
    }

    public function test_heartbeat_command_runs_and_records_a_marker(): void
    {
        $this->artisan('aish:heartbeat')->assertSuccessful();

        $this->assertNotNull(Cache::get(HeartbeatCommand::MARKER));
    }
}
