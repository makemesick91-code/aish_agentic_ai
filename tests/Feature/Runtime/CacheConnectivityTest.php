<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CacheConnectivityTest extends TestCase
{
    public function test_cache_round_trips_a_value(): void
    {
        // In CI's backend job this runs against Redis via CACHE_STORE=redis.
        Cache::put('aish:test:cache', 'value', 5);

        $this->assertSame('value', Cache::get('aish:test:cache'));
    }
}
