<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views render in tests without a compiled frontend: @vite becomes a no-op so the
        // Vite manifest is not required. In CI the test step runs before `npm run build`,
        // so a rendered page must not depend on public/build/manifest.json.
        $this->withoutVite();
    }
}
