<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present_on_health_responses(): void
    {
        $response = $this->get('/live');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_content_security_policy_is_applied_outside_local(): void
    {
        $this->app['env'] = 'production';
        config(['app.env' => 'production']);

        $response = $this->get('/live');

        $response->assertHeader('Content-Security-Policy');
    }
}
