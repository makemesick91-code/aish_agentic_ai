<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Public survey abuse controls (rule 32; Step 7 §18): a coarse per-IP view limit, and a
        // per-token PLUS per-IP submit limit so a single link cannot be flooded and one IP
        // cannot brute-force many links.
        RateLimiter::for('public-survey-view', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->ip()));

        RateLimiter::for('public-survey-submit', function (Request $request): array {
            $token = (string) ($request->route('token')
                ?? $request->route('invitation')
                ?? $request->route('campaign')
                ?? 'anon');

            return [
                Limit::perMinute(10)->by('survey-submit-token:'.$token),
                Limit::perMinute(30)->by('survey-submit-ip:'.$request->ip()),
            ];
        });
    }
}
