<?php

use Illuminate\Support\Facades\Route;

// Backend ini murni REST API; antarmuka ada di aplikasi Vue (folder frontend/).
Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'message' => 'Letterbox API. Dokumentasi endpoint ada di README. Mulai dari POST /api/login.',
    ]);
});
