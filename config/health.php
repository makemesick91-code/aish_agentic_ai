<?php

declare(strict_types=1);

use App\Support\Health\Checks\CacheHealthCheck;
use App\Support\Health\Checks\ConfigurationHealthCheck;
use App\Support\Health\Checks\DatabaseHealthCheck;

return [

    /*
    |--------------------------------------------------------------------------
    | Readiness checks
    |--------------------------------------------------------------------------
    |
    | Ordered list of App\Support\Health\HealthCheck implementations evaluated by
    | GET /ready. Every check must pass for the endpoint to report HTTP 200.
    | Tests override this list to exercise the failure (HTTP 503) path.
    |
    */

    'readiness' => [
        DatabaseHealthCheck::class,
        CacheHealthCheck::class,
        ConfigurationHealthCheck::class,
    ],

];
