<?php

declare(strict_types=1);

namespace Tests\Feature\Runtime;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DatabaseConnectivityTest extends TestCase
{
    public function test_default_connection_executes_a_query(): void
    {
        // Proves real DB connectivity, not just an open socket (Step 5 acceptance).
        // In CI's backend job this runs against PostgreSQL via DB_CONNECTION=pgsql.
        $result = DB::connection()->select('select 1 as one');

        $this->assertSame(1, (int) $result[0]->one);
    }
}
