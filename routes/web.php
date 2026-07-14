<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| Step 5 ships a single honest bootstrap surface at "/". No product features,
| dashboards, tenant data, or fabricated metrics are exposed here (rule 10,
| rule 27). Infra probes (/live, /ready) are registered in bootstrap/app.php
| outside the web middleware group.
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');
