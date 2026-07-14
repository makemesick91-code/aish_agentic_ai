<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HomePageTest extends TestCase
{
    public function test_home_renders_the_honest_bootstrap_surface(): void
    {
        // Decouple the unit suite from the built asset manifest; the real asset
        // build is verified separately by scripts/verify-runtime.sh and CI.
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Aish Agentic AI');
        // Truthful status must be visible; no production/ready claims (rule 10, rule 27).
        $response->assertSee('Application implementation not started.');
        $response->assertDontSee('production ready');
    }
}
