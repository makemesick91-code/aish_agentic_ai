<?php

declare(strict_types=1);

namespace Tests\Unit\Runtime;

use App\Support\Runtime\RuntimePreflight;
use Tests\TestCase;

final class RuntimePreflightTest extends TestCase
{
    public function test_passes_with_valid_configuration(): void
    {
        $this->assertTrue((new RuntimePreflight)->passes());
        $this->assertSame([], (new RuntimePreflight)->problems());
    }

    public function test_reports_missing_app_key(): void
    {
        config(['app.key' => '']);

        $problems = (new RuntimePreflight)->problems();

        $this->assertNotEmpty($problems);
        $this->assertStringContainsString('APP_KEY', implode("\n", $problems));
    }

    public function test_reports_production_debug_enabled(): void
    {
        config(['app.env' => 'production', 'app.debug' => true]);

        $problems = (new RuntimePreflight)->problems();

        $this->assertStringContainsString('APP_DEBUG', implode("\n", $problems));
    }
}
