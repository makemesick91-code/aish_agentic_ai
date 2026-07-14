<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthorizationServiceProvider;
use App\Providers\FeedbackServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\PlatformAuthorizationServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    TenancyServiceProvider::class,
    AuthorizationServiceProvider::class,
    PlatformAuthorizationServiceProvider::class,
    FortifyServiceProvider::class,
    FeedbackServiceProvider::class,
];
