<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use Tests\TestCase;

final class LivenessTest extends TestCase
{
    public function test_live_endpoint_returns_200_and_alive_status(): void
    {
        $response = $this->getJson('/live');

        $response->assertOk();
        $response->assertJson(['status' => 'alive']);
        $response->assertJsonStructure(['status', 'service']);
    }

    public function test_live_endpoint_does_not_leak_a_session_cookie(): void
    {
        // Infra probes must not create sessions or set cookies (rule 11).
        $response = $this->get('/live');

        $response->assertOk();
        $this->assertEmpty($response->headers->getCookies(), 'health probe must not set cookies');
    }
}
