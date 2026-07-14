<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Tests\TestCase;

final class PreflightCommandTest extends TestCase
{
    public function test_preflight_passes_with_valid_configuration(): void
    {
        $this->artisan('aish:preflight')->assertSuccessful();
    }

    public function test_preflight_fails_when_app_key_is_missing(): void
    {
        config(['app.key' => '']);

        $this->artisan('aish:preflight')->assertFailed();
    }

    public function test_preflight_fails_when_production_has_debug_enabled(): void
    {
        config(['app.env' => 'production', 'app.debug' => true]);

        $this->artisan('aish:preflight')->assertFailed();
    }
}
