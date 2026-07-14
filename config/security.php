<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of proxy IPs/CIDRs the application trusts for forwarded
    | headers. Empty => trust NONE. A wildcard ("*") must never be a silent default
    | and requires a controlled ingress (rule 04).
    |
    */

    'trusted_proxies' => env('TRUSTED_PROXIES', ''),

];
