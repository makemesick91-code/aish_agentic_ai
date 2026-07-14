<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use App\Support\Health\Checks\CacheHealthCheck;
use App\Support\Health\Checks\ConfigurationHealthCheck;
use App\Support\Health\Checks\DatabaseHealthCheck;
use Tests\Support\AlwaysFailingHealthCheck;
use Tests\TestCase;

final class ReadinessTest extends TestCase
{
    public function test_ready_endpoint_returns_200_when_all_checks_pass(): void
    {
        config(['health.readiness' => [
            DatabaseHealthCheck::class,
            CacheHealthCheck::class,
            ConfigurationHealthCheck::class,
        ]]);

        $response = $this->getJson('/ready');

        $response->assertOk();
        $response->assertJson(['status' => 'ready']);
        $response->assertJsonStructure(['status', 'checks' => [['name', 'ok', 'status']]]);
    }

    public function test_ready_endpoint_returns_503_when_a_check_fails(): void
    {
        config(['health.readiness' => [
            DatabaseHealthCheck::class,
            AlwaysFailingHealthCheck::class,
        ]]);

        $response = $this->getJson('/ready');

        $response->assertStatus(503);
        $response->assertJson(['status' => 'unavailable']);
    }

    public function test_ready_endpoint_never_leaks_sensitive_detail(): void
    {
        config(['health.readiness' => [AlwaysFailingHealthCheck::class]]);

        $body = $this->getJson('/ready')->getContent();

        // No stack traces, connection strings, credentials, or SQL in the body.
        $this->assertStringNotContainsString('password', strtolower((string) $body));
        $this->assertStringNotContainsString('pgsql:', (string) $body);
        $this->assertStringNotContainsString('#0 ', (string) $body);
    }
}
